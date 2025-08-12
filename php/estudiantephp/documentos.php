<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
header('Content-Type: application/json; charset=utf-8');

session_start();
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado']);
    exit;
}

require_once '../conexion.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'getStudentInfo':
            getStudentInfo($conn);
            break;
            
        case 'getStudentDocuments':
            getStudentDocuments($conn);
            break;
            
        case 'uploadDocument':
            uploadDocument($conn);
            break;
            
        case 'viewDocument':
        case 'downloadDocument':
            handleDocumentView($conn);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error en el servidor: ' . $e->getMessage()]);
}

function getStudentInfo($conn) {
    $usuario_id = $_SESSION['usuario_id'];
    
    $query = "SELECT e.estudiante_id, e.matricula, e.nombre, e.apellido_paterno, e.apellido_materno, e.carrera, e.cuatrimestre, 
                     e.telefono, e.horas_completadas, e.horas_requeridas, u.correo,
                     (SELECT solicitud_id FROM solicitudes WHERE estudiante_id = e.estudiante_id ORDER BY solicitud_id DESC LIMIT 1) as solicitud_id
              FROM estudiantes e
              JOIN usuarios u ON e.usuario_id = u.usuario_id
              WHERE e.usuario_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([$usuario_id]);
    
    if ($stmt->rowCount() > 0) {
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, ...$student]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Estudiante no encontrado']);
    }
}

function getStudentDocuments($conn) {
    $solicitud_id = $_GET['solicitud_id'] ?? 0;
    
    if ($solicitud_id == 0) {
        echo json_encode(['error' => 'ID de solicitud no válido']);
        return;
    }

    // Orden específico solicitado (excluyendo Carta de Termino - tipo_documento_id = 4)
    $query = "SELECT * FROM tipos_documentos 
              WHERE tipo_documento_id != 4
              ORDER BY CASE nombre
                WHEN 'Carta de Presentación' THEN 1
                WHEN 'Carta de Aceptación' THEN 2
                WHEN 'Primer Informe' THEN 3
                WHEN 'Segundo Informe' THEN 4
                WHEN 'Comprobante de Pago' THEN 5
                ELSE 6
              END";
    $stmt = $conn->query($query);
    $documentos_requeridos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // También excluimos documentos de tipo 4 (Carta de Termino) en los documentos subidos
    $query = "SELECT d.*, t.nombre as nombre_documento
              FROM documentos_servicio d
              JOIN tipos_documentos t ON d.tipo_documento_id = t.tipo_documento_id
              WHERE d.solicitud_id = ? AND d.tipo_documento_id != 4";
    $stmt = $conn->prepare($query);
    $stmt->execute([$solicitud_id]);
    $documentos_subidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'documentos_requeridos' => $documentos_requeridos,
        'documentos_subidos' => $documentos_subidos
    ]);
}

function uploadDocument($conn) {
    $solicitud_id = $_POST['solicitud_id'] ?? 0;
    $tipo_documento_id = $_POST['tipo_documento_id'] ?? 0;
    $observaciones = $_POST['observaciones'] ?? '';
    
    if (!isset($_FILES['documento'])) {
        echo json_encode(['success' => false, 'message' => 'No se recibió ningún archivo']);
        return;
    }
    
    $file = $_FILES['documento'];
    $file_name = $file['name'];
    $file_tmp = $file['tmp_name'];
    $file_size = $file['size'];
    $file_type = $file['type'];
    
    $allowed_types = ['application/pdf', 'application/msword', 
                     'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                     'image/jpeg', 'image/png'];
    
    if (!in_array($file_type, $allowed_types)) {
        echo json_encode(['success' => false, 'message' => 'Tipo de archivo no permitido']);
        return;
    }
    
    if ($file_size > 5 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'El archivo excede el tamaño máximo permitido (5MB)']);
        return;
    }
    
    $upload_dir = '../../uploads/documentos_servicio/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
    $file_new_name = 'doc_' . $solicitud_id . '_' . uniqid() . '.' . $file_ext;
    $file_destination = $upload_dir . $file_new_name;
    
    if (move_uploaded_file($file_tmp, $file_destination)) {
        // Verificar si ya existe un documento de este tipo
        $query = "SELECT documento_id FROM documentos_servicio 
                  WHERE solicitud_id = ? AND tipo_documento_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$solicitud_id, $tipo_documento_id]);
        
        if ($stmt->rowCount() > 0) {
            $doc = $stmt->fetch(PDO::FETCH_ASSOC);
            $documento_id = $doc['documento_id'];
            
            // Eliminar el archivo anterior si existe
            $query = "SELECT ruta_archivo FROM documentos_servicio WHERE documento_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->execute([$documento_id]);
            $old_file = $stmt->fetchColumn();
            
            if (file_exists($old_file)) {
                unlink($old_file);
            }
            
            // Actualizar el registro existente
            $query = "UPDATE documentos_servicio 
                      SET nombre_archivo = ?, ruta_archivo = ?, 
                          tipo_archivo = ?, fecha_subida = NOW(), 
                          estado = 'pendiente', observaciones = ?,
                          validado_por = NULL, fecha_validacion = NULL
                      WHERE documento_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->execute([$file_name, $file_destination, $file_type, $observaciones, $documento_id]);
        } else {
            // Insertar nuevo registro
            $query = "INSERT INTO documentos_servicio 
                      (solicitud_id, tipo_documento_id, nombre_archivo, ruta_archivo, 
                       tipo_archivo, fecha_subida, estado, observaciones)
                      VALUES (?, ?, ?, ?, ?, NOW(), 'pendiente', ?)";
            $stmt = $conn->prepare($query);
            $stmt->execute([$solicitud_id, $tipo_documento_id, $file_name, $file_destination, $file_type, $observaciones]);
        }
        
        echo json_encode(['success' => true, 'message' => 'Documento subido correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al subir el archivo']);
    }
}

function handleDocumentView($conn) {
    $documento_id = $_GET['documento_id'] ?? 0;
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    
    $query = "SELECT * FROM documentos_servicio WHERE documento_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$documento_id]);
    
    if ($stmt->rowCount() === 0) {
        header('HTTP/1.0 404 Not Found');
        echo 'Documento no encontrado';
        exit;
    }
    
    $documento = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!file_exists($documento['ruta_archivo'])) {
        header('HTTP/1.0 404 Not Found');
        echo 'Archivo no encontrado en el servidor';
        exit;
    }
    
    $file_path = $documento['ruta_archivo'];
    $file_name = $documento['nombre_archivo'];
    $file_type = $documento['tipo_archivo'];
    
    if ($action === 'downloadDocument') {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file_name) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file_path));
        readfile($file_path);
    } else {
        header('Content-Type: ' . $file_type);
        readfile($file_path);
    }
    exit;
}
?>
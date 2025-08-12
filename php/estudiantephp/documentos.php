<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(403);
    die(json_encode(['success' => false, 'message' => 'Acceso denegado. No has iniciado sesión.']));
}

$usuario_id_estudiante = $_SESSION['usuario_id'];

$conn = new mysqli('127.0.0.1', 'root', '', 'servicio_social');
if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(['success' => false, 'message' => 'Error de conexión a la BD.']));
}
$conn->set_charset('utf8');

$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'getStudentInfo':
        getStudentInfo($conn, $usuario_id_estudiante);
        break;
    case 'getStudentDocuments':
        getStudentDocuments($conn);
        break;
    case 'uploadDocument':
        uploadDocument($conn);
        break;
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Acción no válida.']);
}

$conn->close();

function getStudentInfo($conn, $usuario_id) {
    $query = "SELECT e.estudiante_id, e.nombre, e.apellido_paterno, s.solicitud_id FROM estudiantes e LEFT JOIN solicitudes s ON e.estudiante_id = s.estudiante_id AND s.estado IN ('pendiente', 'aprobada') WHERE e.usuario_id = ? ORDER BY s.fecha_solicitud DESC LIMIT 1";
    $stmt = $conn->prepare($query);
    if (!$stmt) { die(json_encode(['success' => false, 'message' => 'Error al preparar la consulta de estudiante.'])); }
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo json_encode(['success' => true] + $result->fetch_assoc());
    } else {
        echo json_encode(['success' => false, 'message' => 'No se encontró información del estudiante o no tienes una solicitud activa.']);
    }
    $stmt->close();
}

function getStudentDocuments($conn) {
    if (!isset($_GET['solicitud_id'])) { die(json_encode(['success' => false, 'message' => 'Falta el ID de la solicitud.'])); }
    $solicitud_id = intval($_GET['solicitud_id']);
    
    $query_requeridos = "SELECT tipo_documento_id, nombre, descripcion FROM tipos_documentos WHERE requerido = 1";
    $result_requeridos = $conn->query($query_requeridos);
    $documentos_requeridos = $result_requeridos->fetch_all(MYSQLI_ASSOC);

    $query_subidos = "SELECT tipo_documento_id, documento_id, nombre_archivo, ruta_archivo, estado, observaciones, fecha_subida, fecha_validacion FROM documentos_servicio WHERE solicitud_id = ?";
    $stmt_subidos = $conn->prepare($query_subidos);
    if (!$stmt_subidos) { die(json_encode(['success' => false, 'message' => 'Error al preparar la consulta de documentos subidos.'])); }
    $stmt_subidos->bind_param("i", $solicitud_id);
    $stmt_subidos->execute();
    $result_subidos = $stmt_subidos->get_result();
    $documentos_subidos = $result_subidos->fetch_all(MYSQLI_ASSOC);
    
    echo json_encode(['success' => true, 'documentos_requeridos' => $documentos_requeridos, 'documentos_subidos' => $documentos_subidos]);
    $stmt_subidos->close();
}

function uploadDocument($conn) {
    if (!isset($_POST['solicitud_id'], $_POST['tipo_documento_id'], $_FILES['documento'])) {
        die(json_encode(['success' => false, 'message' => 'Faltan datos para subir el archivo.']));
    }
    if ($_FILES['documento']['error'] !== UPLOAD_ERR_OK) {
        die(json_encode(['success' => false, 'message' => 'Error al subir el archivo. Código: ' . $_FILES['documento']['error']]));
    }
    $solicitud_id = intval($_POST['solicitud_id']);
    $tipo_documento_id = intval($_POST['tipo_documento_id']);
    $observaciones = $_POST['observaciones'] ?? '';
    $nombre_original = basename($_FILES['documento']['name']);
    $tipo_archivo = $_FILES['documento']['type'];
    $nombre_unico = uniqid('doc_' . $solicitud_id . '_') . '.' . pathinfo($nombre_original, PATHINFO_EXTENSION);
    $directorio_subida = '../../uploads/documentos_servicio/';
    if (!is_dir($directorio_subida)) { mkdir($directorio_subida, 0777, true); }
    $ruta_archivo_servidor = $directorio_subida . $nombre_unico;
    if (!move_uploaded_file($_FILES['documento']['tmp_name'], $ruta_archivo_servidor)) {
        die(json_encode(['success' => false, 'message' => 'No se pudo guardar el archivo en el servidor.']));
    }
    $ruta_archivo_bd = '../../uploads/documentos_servicio/' . $nombre_unico;
    $query_check = "SELECT documento_id FROM documentos_servicio WHERE solicitud_id = ? AND tipo_documento_id = ?";
    $stmt_check = $conn->prepare($query_check);
    $stmt_check->bind_param("ii", $solicitud_id, $tipo_documento_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    if ($result_check->num_rows > 0) {
        $query_update = "UPDATE documentos_servicio SET nombre_archivo = ?, ruta_archivo = ?, tipo_archivo = ?, fecha_subida = NOW(), estado = 'pendiente', observaciones = ? WHERE solicitud_id = ? AND tipo_documento_id = ?";
        $stmt_update = $conn->prepare($query_update);
        $stmt_update->bind_param("sssisi", $nombre_original, $ruta_archivo_bd, $tipo_archivo, $observaciones, $solicitud_id, $tipo_documento_id);
        $success = $stmt_update->execute();
        $stmt_update->close();
    } else {
        $query_insert = "INSERT INTO documentos_servicio (solicitud_id, tipo_documento_id, nombre_archivo, ruta_archivo, tipo_archivo, estado, observaciones) VALUES (?, ?, ?, ?, ?, 'pendiente', ?)";
        $stmt_insert = $conn->prepare($query_insert);
        $stmt_insert->bind_param("iissss", $solicitud_id, $tipo_documento_id, $nombre_original, $ruta_archivo_bd, $tipo_archivo, $observaciones);
        $success = $stmt_insert->execute();
        $stmt_insert->close();
    }
    $stmt_check->close(); 
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Archivo subido correctamente.']);
    } else {
        unlink($ruta_archivo_servidor);
        echo json_encode(['success' => false, 'message' => 'Error al guardar la información en la base de datos.']);
    }
}
?>
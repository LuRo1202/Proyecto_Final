<?php
// C:\xampp\htdocs\Proyecto\Proyecto_Integrador\php\vinculacionphp\actualizar_estado_carta.php

session_start();
header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Error desconocido.'];

// Verificar que el usuario tenga sesión activa
if (!isset($_SESSION['usuario_id'])) {
    $response['message'] = 'Acceso denegado. Sesión no válida.';
    echo json_encode($response);
    exit;
}

// El JS envía 'tipo_documento', pero tu archivo original esperaba 'tipo_carta'. 
// Se usará 'tipo_documento' para ser más claros.
if (isset($_POST['solicitud_id'], $_POST['tipo_documento'], $_POST['nuevo_estado'])) {
    
    $solicitud_id = intval($_POST['solicitud_id']);
    $tipo_documento_str = $_POST['tipo_documento'];
    $nuevo_estado = $_POST['nuevo_estado'];
    $usuario_validador_id = intval($_SESSION['usuario_id']);

    // Mapa extendido para incluir todos los documentos que se pueden validar desde la vista
    $tipo_documento_map = [
        'presentacion'   => 1,
        'aceptacion'     => 6,
        'primer_informe' => 2,
        'segundo_informe'=> 3,
        'pago'           => 5,
        'termino'        => 4 // Se mantiene en caso de que se necesite
    ];

    // Validar datos de entrada
    $estados_permitidos = ['Aprobada', 'Rechazada', 'Pendiente'];
    if (in_array($nuevo_estado, $estados_permitidos) && isset($tipo_documento_map[$tipo_documento_str])) {
        
        $conn = new mysqli('127.0.0.1', 'root', '', 'servicio_social');
        if ($conn->connect_error) {
            $response['message'] = 'Error de conexión a la BD.';
        } else {
            $conn->begin_transaction();
            try {
                $tipo_doc_id = $tipo_documento_map[$tipo_documento_str];
                $estado_documento_db = strtolower($nuevo_estado); // 'aprobada', 'rechazada', 'pendiente'

                // Intenta ACTUALIZAR un registro existente primero
                $stmt_update = $conn->prepare("
                    UPDATE documentos_servicio 
                    SET estado = ?, validado_por = ?, fecha_validacion = NOW()
                    WHERE solicitud_id = ? AND tipo_documento_id = ?
                ");
                $stmt_update->bind_param("siii", $estado_documento_db, $usuario_validador_id, $solicitud_id, $tipo_doc_id);
                $stmt_update->execute();
                
                // Si ninguna fila fue afectada por el UPDATE, el registro no existía. Lo INSERTAMOS.
                // Esto es crucial para marcar como 'Aprobado' o 'Rechazado' un documento que nunca fue subido por el alumno.
                if ($stmt_update->affected_rows === 0) {
                    $stmt_insert = $conn->prepare("
                        INSERT INTO documentos_servicio 
                        (solicitud_id, tipo_documento_id, nombre_archivo, ruta_archivo, tipo_archivo, estado, validado_por, fecha_validacion)
                        VALUES (?, ?, 'estado_actualizado_por_vinculacion', 'no_aplica', 'sistema', ?, ?, NOW())
                    ");
                    $stmt_insert->bind_param("iisi", $solicitud_id, $tipo_doc_id, $estado_documento_db, $usuario_validador_id);
                    $stmt_insert->execute();
                }

                $conn->commit();
                $response['success'] = true;
                $response['message'] = 'Estado actualizado correctamente.';
                
            } catch (Exception $e) {
                $conn->rollback();
                $response['message'] = 'Error en transacción: ' . $e->getMessage();
            }
            $conn->close();
        }
    } else {
        $response['message'] = 'Datos inválidos proporcionados.';
    }
} else {
    $response['message'] = 'Faltan parámetros para realizar la acción.';
}

echo json_encode($response);
?>
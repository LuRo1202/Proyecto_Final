<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['vinculacion_id'])) {
    die(json_encode(['success' => false, 'message' => 'Error: No has iniciado sesión como Vinculación.']));
}

$response = ['success' => false, 'message' => 'Error: Faltan datos para realizar la acción.'];

if (isset($_POST['solicitud_id'], $_POST['nuevo_estado'])) {
    $solicitud_id = intval($_POST['solicitud_id']);
    $nuevo_estado = $_POST['nuevo_estado']; // 'aprobada', 'rechazada', o 'pendiente'
    $vinculacion_id_aprobador = intval($_SESSION['vinculacion_id']);
    
    // Validar que el estado sea uno de los permitidos
    $estados_validos = ['aprobada', 'rechazada', 'pendiente'];
    if (!in_array($nuevo_estado, $estados_validos)) {
        die(json_encode(['success' => false, 'message' => 'El estado proporcionado no es válido.']));
    }

    $conn = new mysqli('127.0.0.1', 'root', '', 'servicio_social');
    if ($conn->connect_error) {
        die(json_encode(['success' => false, 'message' => 'Error de conexión a la Base de Datos.']));
    }

    // Preparar la consulta SQL dinámicamente
    if ($nuevo_estado === 'aprobada') {
        // Si se aprueba, guardamos quién y cuándo
        $query = "UPDATE solicitudes SET estado = ?, fecha_aprobacion = NOW(), aprobado_por = ? WHERE solicitud_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sii", $nuevo_estado, $vinculacion_id_aprobador, $solicitud_id);
    } else {
        // Si se rechaza o se pone pendiente, limpiamos los datos de aprobación
        $query = "UPDATE solicitudes SET estado = ?, fecha_aprobacion = NULL, aprobado_por = NULL WHERE solicitud_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $nuevo_estado, $solicitud_id);
    }
    
    if ($stmt) {
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $response['success'] = true;
                $response['message'] = 'El estado final de la solicitud ha sido actualizado.';
            } else {
                $response['message'] = 'No se aplicaron cambios. El estado ya era el mismo.';
            }
        } else {
            $response['message'] = 'Error al ejecutar la actualización.';
        }
        $stmt->close();
    } else {
         $response['message'] = 'Error al preparar la consulta de aprobación.';
    }
    $conn->close();
}

echo json_encode($response);
?>
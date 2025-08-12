<?php
// C:\xampp\htdocs\8MSC1\Proyecto_Integrador\php\adminphp\obtener_registros_estudiante.php

header('Content-Type: application/json');
session_start();

// Verificar sesión y rol de administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    http_response_code(403); // Forbidden
    echo json_encode(['success' => false, 'message' => 'Acceso denegado. Permisos insuficientes.']);
    exit();
}

require_once __DIR__ . '/../conexion.php'; // Asegúrate de que $conn sea un objeto PDO

$response = ['success' => false, 'data' => []];

$estudiante_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$estudiante_id) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'ID de estudiante no válido.']);
    exit();
}

try {
    // Consulta para obtener registros de horas para un estudiante específico
    // ¡CORRECCIÓN AQUÍ! Se usan apellido_paterno y apellido_materno en lugar de 'apellido'.
    $query = "
        SELECT 
            rh.fecha, 
            DATE_FORMAT(rh.hora_entrada, '%H:%i') as hora_entrada,
            DATE_FORMAT(rh.hora_salida, '%H:%i') as hora_salida,
            rh.horas_acumuladas,
            rh.estado,
            CONCAT_WS(' ', res.nombre, res.apellido_paterno, res.apellido_materno) AS responsable
        FROM 
            registroshoras rh
        JOIN 
            responsables res ON rh.responsable_id = res.responsable_id
        WHERE 
            rh.estudiante_id = :estudiante_id
        ORDER BY 
            rh.fecha DESC, rh.hora_entrada DESC;
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':estudiante_id', $estudiante_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $response['success'] = true;
    $response['data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    $response['message'] = "Error en la base de datos: " . $e->getMessage();
    error_log("Error en obtener_registros_estudiante.php: " . $e->getMessage()); // Registrar el error en el log del servidor
} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    $response['message'] = "Error inesperado: " . $e->getMessage();
    error_log("Error inesperado en obtener_registros_estudiante.php: " . $e->getMessage()); // Registrar el error
}

echo json_encode($response);
?>
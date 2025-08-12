<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../conexion.php';

$response = ['success' => false, 'data' => []];

try {
    $query = "SELECT 
                e.estudiante_id, 
                e.matricula, 
                e.nombre, 
                e.apellido_paterno, 
                e.apellido_materno, 
                e.carrera, 
                e.cuatrimestre, 
                e.horas_completadas, 
                e.horas_requeridas, 
                e.activo,
                u.correo
              FROM estudiantes e
              LEFT JOIN usuarios u ON e.usuario_id = u.usuario_id
              ORDER BY e.apellido_paterno, e.apellido_materno, e.nombre";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    $response['success'] = true;
    $response['data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $response['message'] = "Error en la base de datos: " . $e->getMessage();
    error_log($e->getMessage());
}

// Limpiar buffer de salida por si acaso y enviar JSON
if (ob_get_level() > 0) {
    ob_end_clean();
}
echo json_encode($response);
exit;
?>
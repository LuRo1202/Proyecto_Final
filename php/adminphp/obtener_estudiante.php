<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../conexion.php';

$response = ['success' => false, 'data' => []];

if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $response['message'] = "ID de estudiante inválido o no proporcionado.";
    echo json_encode($response);
    exit;
}

$estudiante_id = $_GET['id'];

try {
    $query = "SELECT 
                e.estudiante_id, 
                e.matricula, 
                e.nombre, 
                e.apellido_paterno, 
                e.apellido_materno, 
                e.carrera, 
                e.cuatrimestre, 
                e.telefono, 
                e.horas_completadas, 
                e.horas_requeridas, 
                e.activo,
                u.correo
              FROM estudiantes e
              JOIN usuarios u ON e.usuario_id = u.usuario_id
              WHERE e.estudiante_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([$estudiante_id]);
    $estudiante = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($estudiante) {
        $response['success'] = true;
        $response['data'] = $estudiante;
    } else {
        $response['message'] = "Estudiante no encontrado";
    }
} catch (PDOException $e) {
    $response['message'] = "Error en la base de datos: " . $e->getMessage();
    error_log($e->getMessage());
}

if (ob_get_level() > 0) {
    ob_end_clean();
}
echo json_encode($response);
?>
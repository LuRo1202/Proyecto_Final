<?php
// C:\xampp\htdocs\8MSC1\Proyecto_Integrador\php\adminphp\obtener_asignaciones.php

header('Content-Type: application/json');
session_start(); // Inicia la sesión para verificación de usuario

// Verificar sesión y rol de administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    http_response_code(403); // Forbidden
    echo json_encode(['data' => [], 'success' => false, 'message' => 'Acceso denegado. Permisos insuficientes.']);
    exit();
}

require_once __DIR__ . '/../conexion.php'; // Asegúrate de que $conn sea un objeto PDO

$response = ['success' => false, 'data' => []];

try {
    $query = "SELECT er.id as asignacion_id, 
                     CONCAT(e.nombre, ' ', COALESCE(e.apellido_paterno, ''), ' ', COALESCE(e.apellido_materno, '')) as estudiante,
                     CONCAT(r.nombre, ' ', COALESCE(r.apellido_paterno, ''), ' ', COALESCE(r.apellido_materno, '')) as responsable,
                     DATE_FORMAT(er.fecha_asignacion, '%Y-%m-%d %H:%i:%s') as fecha_asignacion,
                     e.estudiante_id,
                     r.responsable_id
              FROM estudiantes_responsables er
              JOIN estudiantes e ON er.estudiante_id = e.estudiante_id
              JOIN responsables r ON er.responsable_id = r.responsable_id
              ORDER BY er.fecha_asignacion DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    $response['success'] = true;
    $response['data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    $response['message'] = "Error en la base de datos: " . $e->getMessage();
    error_log("Error en obtener_asignaciones.php: " . $e->getMessage()); // Registrar el error
}

echo json_encode($response);
?>
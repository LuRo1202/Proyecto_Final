<?php
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
    $query = "SELECT r.responsable_id, 
                     CONCAT(r.nombre, ' ', COALESCE(r.apellido_paterno, ''), ' ', COALESCE(r.apellido_materno, '')) as nombre_completo, 
                     r.cargo
              FROM responsables r
              WHERE r.activo = 1
              ORDER BY r.apellido_paterno, r.nombre"; // Ordenar por apellido paterno y nombre
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    $response['success'] = true;
    $response['data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    $response['message'] = "Error en la base de datos: " . $e->getMessage();
    error_log("Error en obtener_responsables_disponibles.php: " . $e->getMessage()); // Registrar el error
}

echo json_encode($response);
?>
<?php
header('Content-Type: application/json');
session_start(); // Inicia la sesión para verificación de usuario

// Verificar sesión y rol de administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    http_response_code(403); // Forbidden
    echo json_encode(['success' => false, 'message' => 'Acceso denegado. Permisos insuficientes.']);
    exit();
}

require_once __DIR__ . '/../conexion.php'; // Asegúrate de que $conn sea un objeto PDO

$response = ['success' => false, 'message' => ''];

$data = json_decode(file_get_contents('php://input'), true);

// Validar parámetros requeridos
if (!isset($data['estudiante_id']) || !is_numeric($data['estudiante_id']) ||
    !isset($data['responsable_id']) || !is_numeric($data['responsable_id'])) {
    http_response_code(400); // Bad Request
    $response['message'] = "Parámetros requeridos faltantes o inválidos.";
    echo json_encode($response);
    exit;
}

$estudiante_id = $data['estudiante_id'];
$responsable_id = $data['responsable_id'];

try {
    // Verificar si el estudiante ya tiene una asignación (solo una asignación por estudiante)
    $queryCheck = "SELECT id FROM estudiantes_responsables WHERE estudiante_id = ?";
    $stmtCheck = $conn->prepare($queryCheck);
    $stmtCheck->execute([$estudiante_id]);
    
    if ($stmtCheck->rowCount() > 0) {
        http_response_code(409); // Conflict
        $response['message'] = "Este estudiante ya tiene una asignación activa.";
        echo json_encode($response);
        exit;
    }

    // Insertar nueva asignación
    $query = "INSERT INTO estudiantes_responsables (estudiante_id, responsable_id) 
              VALUES (?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->execute([$estudiante_id, $responsable_id]);
    
    $response['success'] = true;
    $response['message'] = "Asignación creada correctamente.";
    $response['asignacion_id'] = $conn->lastInsertId(); // Obtener el ID de la nueva asignación
} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    $response['message'] = "Error en la base de datos: " . $e->getMessage();
    error_log("Error en asignar_estudiante.php: " . $e->getMessage()); // Registrar el error
}

echo json_encode($response);
?>
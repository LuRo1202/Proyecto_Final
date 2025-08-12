<?php
// --- CABECERAS CORS ---
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

header('Content-Type: application/json');
session_start(); // Iniciar sesión para acceder a las variables de sesión

// --- Autenticación y Autorización ---
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(403); 
    echo json_encode(['success' => false, 'message' => 'Acceso denegado. Sesión no iniciada.']);
    exit();
}

if ($_SESSION['rol'] !== 'admin') {
    http_response_code(403); 
    echo json_encode(['success' => false, 'message' => 'Acceso denegado. Permisos insuficientes.']);
    exit();
}

// --- Configuración de la base de datos ---
require_once __DIR__ . '/../conexion.php'; 

try {
    $data = []; 

    $query = "SELECT COUNT(*) as total FROM estudiantes WHERE activo = 1";
    $result = $conn->query($query);
    $data['totalEstudiantes'] = (int)($result->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
    
    $query = "SELECT COUNT(*) as total FROM responsables WHERE activo = 1";
    $result = $conn->query($query);
    $data['totalResponsables'] = (int)($result->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
    
    $query = "SELECT SUM(COALESCE(horas_acumuladas, 0)) as total FROM registroshoras WHERE estado = 'pendiente'";
    $result = $conn->query($query);
    $data['horasPendientes'] = (float)($result->fetch(PDO::FETCH_ASSOC)['total'] ?? 0.00);
    
    $query = "SELECT COUNT(*) as total FROM estudiantes_responsables";
    $result = $conn->query($query);
    $data['totalAsignaciones'] = (int)($result->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
    
    $query = "SELECT 
                COALESCE(carrera, 'Sin carrera asignada') as carrera, 
                COUNT(*) as cantidad 
              FROM estudiantes 
              WHERE activo = 1 
              GROUP BY COALESCE(carrera, 'Sin carrera asignada')
              ORDER BY cantidad DESC";
    $result = $conn->query($query);
    $data['estudiantesPorCarrera'] = $result->fetchAll(PDO::FETCH_ASSOC) ?: [];
    foreach ($data['estudiantesPorCarrera'] as &$item) {
        $item['cantidad'] = (int)$item['cantidad'];
    }
    unset($item);

    $query = "SELECT 
                e.estudiante_id, 
                e.nombre, 
                COALESCE(e.apellido_paterno, '') AS apellido_paterno, 
                COALESCE(e.apellido_materno, '') AS apellido_materno,
                e.horas_completadas,
                e.horas_requeridas,
                (e.horas_requeridas - e.horas_completadas) as horas_restantes_calculadas
              FROM estudiantes e
              WHERE e.activo = 1 AND (e.horas_requeridas - e.horas_completadas) > 0
              ORDER BY horas_restantes_calculadas DESC
              LIMIT 5";
    $result = $conn->query($query);
    $data['estudiantesHorasPendientes'] = $result->fetchAll(PDO::FETCH_ASSOC) ?: [];
    foreach ($data['estudiantesHorasPendientes'] as &$student) {
        $student['horas_completadas'] = (float)$student['horas_completadas'];
        $student['horas_requeridas'] = (int)$student['horas_requeridas'];
        $student['horas_restantes_calculadas'] = (float)$student['horas_restantes_calculadas'];
    }
    unset($student); 

    // Aquí ya no necesitamos 'adminInfo' del dashboard,
    // ya que la info de usuario viene de verificar_sesion.php
    
    echo json_encode([
        'success' => true,
        'data' => $data
    ]);
    
} catch (PDOException $e) {
    http_response_code(500); 
    echo json_encode([
        'success' => false,
        'message' => 'Error en el servidor (PDO): ' . $e->getMessage()
    ]);
    error_log("dashboard.php (PDOException): " . $e->getMessage());
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor: ' . $e->getMessage()
    ]);
    error_log("dashboard.php (General Exception): " . $e->getMessage());
}
?>
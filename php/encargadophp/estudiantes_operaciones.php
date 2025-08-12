<?php
header('Content-Type: application/json');
require_once '../conexion.php';

// Configurar cabeceras CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");

session_start();

// Verificar conexión a la base de datos
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos']);
    exit;
}

$accion = $_GET['accion'] ?? ($_POST['accion'] ?? '');

// Validar acción
if (!in_array($accion, ['listarEstudiantes', 'obtenerDetalleEstudiante', 'obtenerRegistrosEstudiante'])) {
    echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    exit;
}

try {
    switch ($accion) {
        case 'listarEstudiantes':
            listarEstudiantes();
            break;
        case 'obtenerDetalleEstudiante':
            obtenerDetalleEstudiante();
            break;
        case 'obtenerRegistrosEstudiante':
            obtenerRegistrosEstudiante();
            break;
    }
} catch (PDOException $e) {
    error_log('Error en estudiantes_operaciones: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error en el servidor']);
}

function listarEstudiantes() {
    global $conn;
    
    // Verificar que el usuario sea encargado
    if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'encargado') {
        echo json_encode(['success' => false, 'message' => 'Acceso no autorizado']);
        exit;
    }
    
    $responsable_id = $_SESSION['usuario_id'];
    
    $query = "SELECT e.estudiante_id, e.matricula, 
                     e.nombre, e.apellido_paterno, e.apellido_materno,
                     e.carrera, e.cuatrimestre, e.telefono,
                     e.horas_requeridas, 
                     COALESCE(SUM(CASE WHEN rh.estado = 'aprobado' THEN rh.horas_acumuladas ELSE 0 END), 0) as horas_completadas
              FROM estudiantes e
              JOIN estudiantes_responsables er ON e.estudiante_id = er.estudiante_id
              LEFT JOIN registroshoras rh ON e.estudiante_id = rh.estudiante_id AND rh.estado = 'aprobado'
              WHERE er.responsable_id = (SELECT responsable_id FROM responsables WHERE usuario_id = :usuario_id)
              AND e.activo = 1
              GROUP BY e.estudiante_id
              ORDER BY e.apellido_paterno, e.apellido_materno, e.nombre";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':usuario_id', $responsable_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $estudiantes,
        'draw' => $_GET['draw'] ?? 1
    ]);
}

function obtenerDetalleEstudiante() {
    global $conn;
    
    $estudiante_id = $_GET['estudiante_id'] ?? null;
    
    if (!$estudiante_id || !filter_var($estudiante_id, FILTER_VALIDATE_INT)) {
        echo json_encode(['success' => false, 'message' => 'ID de estudiante no válido']);
        return;
    }
    
    // Verificar que el estudiante pertenezca al encargado
    if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'encargado') {
        echo json_encode(['success' => false, 'message' => 'Acceso no autorizado']);
        return;
    }
    
    $responsable_id = $_SESSION['usuario_id'];
    
    $query = "SELECT e.*
              FROM estudiantes e
              JOIN estudiantes_responsables er ON e.estudiante_id = er.estudiante_id
              WHERE e.estudiante_id = :estudiante_id
              AND er.responsable_id = (SELECT responsable_id FROM responsables WHERE usuario_id = :usuario_id)
              AND e.activo = 1";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':estudiante_id', $estudiante_id, PDO::PARAM_INT);
    $stmt->bindParam(':usuario_id', $responsable_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $estudiante = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$estudiante) {
        echo json_encode(['success' => false, 'message' => 'Estudiante no encontrado o no autorizado']);
        return;
    }
    
    // Obtener horas completadas
    $queryHoras = "SELECT COALESCE(SUM(horas_acumuladas), 0) as total 
                   FROM registroshoras 
                   WHERE estudiante_id = :estudiante_id
                   AND estado = 'aprobado'";
    
    $stmtHoras = $conn->prepare($queryHoras);
    $stmtHoras->bindParam(':estudiante_id', $estudiante_id, PDO::PARAM_INT);
    $stmtHoras->execute();
    $horas = $stmtHoras->fetch(PDO::FETCH_ASSOC);
    
    $estudiante['horas_completadas'] = (float)$horas['total'];
    
    echo json_encode(['success' => true, 'data' => $estudiante]);
}

function obtenerRegistrosEstudiante() {
    global $conn;
    
    $estudiante_id = $_GET['estudiante_id'] ?? null;
    
    if (!$estudiante_id || !filter_var($estudiante_id, FILTER_VALIDATE_INT)) {
        echo json_encode(['success' => false, 'message' => 'ID de estudiante no válido']);
        return;
    }
    
    // Verificar que el estudiante pertenezca al encargado
    if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'encargado') {
        echo json_encode(['success' => false, 'message' => 'Acceso no autorizado']);
        return;
    }
    
    $responsable_id = $_SESSION['usuario_id'];
    
    $query = "SELECT r.registro_id, DATE_FORMAT(r.fecha, '%d/%m/%Y') as fecha, 
                     TIME(r.hora_entrada) as hora_entrada, 
                     TIME(r.hora_salida) as hora_salida,
                     r.horas_acumuladas, r.estado,
                     DATE_FORMAT(r.fecha_validacion, '%d/%m/%Y %H:%i') as fecha_validacion,
                     r.observaciones
              FROM registroshoras r
              JOIN estudiantes_responsables er ON r.estudiante_id = er.estudiante_id
              WHERE r.estudiante_id = :estudiante_id
              AND er.responsable_id = (SELECT responsable_id FROM responsables WHERE usuario_id = :usuario_id)
              ORDER BY r.fecha DESC, r.hora_entrada DESC
              LIMIT 10";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':estudiante_id', $estudiante_id, PDO::PARAM_INT);
    $stmt->bindParam(':usuario_id', $responsable_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $registros]);
}
?>
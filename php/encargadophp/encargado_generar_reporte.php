<?php
header('Content-Type: application/json');
session_start();

// --- Verificación de seguridad ---
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'encargado' || !isset($_SESSION['responsable_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado.']);
    exit();
}

require_once('../conexion.php');
$responsable_id = $_SESSION['responsable_id'];
$action = $_GET['action'] ?? 'get_list'; // 'get_list' por defecto

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(['success' => false, 'message' => 'Error de conexión.']));
}
$conn->set_charset('utf8mb4');

// --- ACCIÓN: OBTENER LISTA PARA LA TABLA ---
if ($action === 'get_list') {
    $sql = "SELECT 
                e.estudiante_id,
                e.matricula,
                CONCAT_WS(' ', e.nombre, e.apellido_paterno, e.apellido_materno) AS nombre_completo,
                e.horas_completadas,
                e.horas_requeridas,
                CASE WHEN e.horas_completadas >= e.horas_requeridas THEN 'Liberado' ELSE 'En Proceso' END AS estado
            FROM estudiantes e
            JOIN estudiantes_responsables er ON e.estudiante_id = er.estudiante_id
            WHERE er.responsable_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $responsable_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    echo json_encode(['data' => $data]);
}

// --- ACCIÓN: OBTENER REPORTE DETALLADO ---
elseif ($action === 'get_report' && isset($_GET['id'])) {
    $estudiante_id = intval($_GET['id']);
    
    // Verificación de permiso: ¿Este estudiante pertenece a este encargado?
    $perm_stmt = $conn->prepare("SELECT 1 FROM estudiantes_responsables WHERE estudiante_id = ? AND responsable_id = ?");
    $perm_stmt->bind_param("ii", $estudiante_id, $responsable_id);
    $perm_stmt->execute();
    if ($perm_stmt->get_result()->num_rows === 0) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'No tienes permiso para ver este reporte.']);
        exit();
    }
    $perm_stmt->close();

    $data = [];
    // Detalles
    $stmt = $conn->prepare("SELECT e.*, u.correo, CONCAT_WS(' ', e.nombre, e.apellido_paterno, e.apellido_materno) AS nombre_completo FROM estudiantes e LEFT JOIN usuarios u ON e.usuario_id = u.usuario_id WHERE e.estudiante_id = ?");
    $stmt->bind_param("i", $estudiante_id);
    $stmt->execute();
    $data['detalleEstudiante'] = $stmt->get_result()->fetch_assoc();
    
    // Gráficos y registros
    $query_base = "FROM registroshoras WHERE estudiante_id = $estudiante_id AND estado = 'aprobado'";
    $data['horasSemanales'] = $conn->query("SELECT YEARWEEK(fecha, 1) as label, SUM(horas_acumuladas) as horas $query_base GROUP BY label ORDER BY label DESC LIMIT 6")->fetch_all(MYSQLI_ASSOC);
    $data['horasMensuales'] = $conn->query("SELECT DATE_FORMAT(fecha, '%b %Y') as label, SUM(horas_acumuladas) as horas $query_base GROUP BY label ORDER BY MAX(fecha) DESC LIMIT 6")->fetch_all(MYSQLI_ASSOC);
    $data['horasTrimestrales'] = $conn->query("SELECT CONCAT(YEAR(fecha), '-T', QUARTER(fecha)) as label, SUM(horas_acumuladas) as horas $query_base GROUP BY label ORDER BY label DESC LIMIT 4")->fetch_all(MYSQLI_ASSOC);
    $data['registrosRecientes'] = $conn->query("SELECT DATE_FORMAT(fecha, '%d/%m/%Y') as fecha, horas_acumuladas, estado FROM registroshoras WHERE estudiante_id = $estudiante_id ORDER BY fecha DESC, hora_entrada DESC LIMIT 10")->fetch_all(MYSQLI_ASSOC);

    echo json_encode(['success' => true, 'data' => $data]);
}

$conn->close();
?>
<?php
// Para depuración: Muestra todos los errores de PHP. ¡Comenta o elimina estas líneas en producción!
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
require_once __DIR__ . '/../conexion.php';

// Establecer la zona horaria para que los cálculos de fecha/hora sean consistentes
date_default_timezone_set('America/Mexico_City');

function responder($success, $message = '', $data = null) {
    $response = ['success' => $success];
    if ($message) $response['message'] = $message;
    if ($data !== null) $response['data'] = $data;
    echo json_encode($response);
    exit;
}

$accion = $_GET['accion'] ?? '';
$estudiante_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

try {
    switch ($accion) {
        case 'listar_estudiantes':
            $stmt = $conn->query("
                SELECT 
                    e.estudiante_id, 
                    e.matricula, 
                    CONCAT_WS(' ', e.nombre, e.apellido_paterno, e.apellido_materno) as nombre_completo,
                    e.carrera, 
                    e.horas_completadas, 
                    e.horas_requeridas
                FROM estudiantes e
                WHERE e.activo = 1
                ORDER BY e.apellido_paterno, e.apellido_materno, e.nombre
            ");
            $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            responder(true, 'Lista de estudiantes obtenida.', $estudiantes);
            break;

        case 'obtener_reporte_completo':
            if (!$estudiante_id) {
                throw new Exception("ID de estudiante no válido.");
            }

            $reporte = [];

            // 1. Detalles del estudiante
            $stmt = $conn->prepare("
                SELECT 
                    e.*, 
                    CONCAT_WS(' ', e.nombre, e.apellido_paterno, e.apellido_materno) as nombre_completo,
                    CONCAT_WS(' ', r.nombre, r.apellido_paterno) as responsable_nombre
                FROM estudiantes e
                LEFT JOIN estudiantes_responsables er ON e.estudiante_id = er.estudiante_id
                LEFT JOIN responsables r ON er.responsable_id = r.responsable_id
                WHERE e.estudiante_id = ?
            ");
            $stmt->execute([$estudiante_id]);
            $reporte['detalleEstudiante'] = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$reporte['detalleEstudiante']) {
                throw new Exception("Estudiante no encontrado.");
            }

            // 2. Horas semanales (últimas 8)
            $stmt = $conn->prepare("
                SELECT YEARWEEK(fecha, 1) as semana, SUM(horas_acumuladas) as total_horas
                FROM registroshoras
                WHERE estudiante_id = ? AND estado = 'aprobado'
                GROUP BY semana
                ORDER BY semana DESC
                LIMIT 8
            ");
            $stmt->execute([$estudiante_id]);
            $reporte['horasSemanales'] = array_reverse($stmt->fetchAll(PDO::FETCH_ASSOC)); // Revertir para orden cronológico

            // 3. Horas mensuales (últimos 6)
            $stmt = $conn->prepare("
                SELECT DATE_FORMAT(fecha, '%Y-%m') as mes, SUM(horas_acumuladas) as total_horas
                FROM registroshoras
                WHERE estudiante_id = ? AND estado = 'aprobado'
                GROUP BY mes
                ORDER BY mes DESC
                LIMIT 6
            ");
            $stmt->execute([$estudiante_id]);
            $reporte['horasMensuales'] = array_reverse($stmt->fetchAll(PDO::FETCH_ASSOC));

            // 4. Registros recientes (últimos 10 aprobados)
            $stmt = $conn->prepare("
                SELECT rh.fecha, rh.horas_acumuladas, rh.estado, CONCAT_WS(' ', r.nombre, r.apellido_paterno) as responsable_nombre
                FROM registroshoras rh
                LEFT JOIN responsables r ON rh.responsable_id = r.responsable_id
                WHERE rh.estudiante_id = ? AND rh.estado = 'aprobado'
                ORDER BY rh.fecha DESC
                LIMIT 10
            ");
            $stmt->execute([$estudiante_id]);
            $reporte['registrosRecientes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            responder(true, 'Reporte completo obtenido.', $reporte);
            break;

        default:
            throw new Exception("Acción no reconocida.");
    }
} catch (PDOException | Exception $e) {
    responder(false, "Error: " . $e->getMessage());
}
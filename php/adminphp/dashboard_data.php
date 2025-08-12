<?php
header('Content-Type: application/json');
require_once '../conexion.php'; // Asegúrate de que la ruta es correcta

// Función para establecer la conexión a la base de datos
function conectarDB() {
    $host = '127.0.0.1';
    $dbname = 'servicio_social';
    $username = 'root'; // Cambiar según tu configuración
    $password = ''; // Cambiar según tu configuración

    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error de conexión: ' . $e->getMessage()]);
        exit();
    }
}

// Obtener estadísticas para el dashboard
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $conn = conectarDB();
        
        // Obtener total de estudiantes
        $query = "SELECT COUNT(*) as total FROM estudiantes WHERE activo = 1";
        $stmt = $conn->query($query);
        $totalEstudiantes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Obtener total de responsables
        $query = "SELECT COUNT(*) as total FROM responsables WHERE activo = 1";
        $stmt = $conn->query($query);
        $totalResponsables = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Obtener total de horas pendientes
        $query = "SELECT SUM(horas_acumuladas) as total FROM registroshoras WHERE estado = 'pendiente'";
        $stmt = $conn->query($query);
        $horasPendientes = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        
        // Obtener total de asignaciones
        $query = "SELECT COUNT(*) as total FROM estudiantes_responsables";
        $stmt = $conn->query($query);
        $totalAsignaciones = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Obtener estudiantes por carrera
        $query = "SELECT carrera, COUNT(*) as cantidad FROM estudiantes WHERE activo = 1 GROUP BY carrera";
        $stmt = $conn->query($query);
        $estudiantesPorCarrera = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Obtener estudiantes con más horas pendientes
        $query = "SELECT e.estudiante_id, e.nombre, 
                 CONCAT(IFNULL(e.apellido_paterno, ''), ' ', IFNULL(e.apellido_materno, '')) as apellidos, 
                 e.horas_completadas, 
                 (e.horas_requeridas - e.horas_completadas) as horas_pendientes
                 FROM estudiantes e
                 WHERE e.activo = 1
                 ORDER BY horas_pendientes DESC
                 LIMIT 5";
        $stmt = $conn->query($query);
        $estudiantesHorasPendientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Obtener últimos registros de horas
        $query = "SELECT r.registro_id, e.nombre as estudiante, 
                 CONCAT(IFNULL(e.apellido_paterno, ''), ' ', IFNULL(e.apellido_materno, '')) as apellidos,
                 res.nombre as responsable, 
                 r.fecha, r.hora_entrada, r.hora_salida, r.horas_acumuladas, r.estado
                 FROM registroshoras r
                 JOIN estudiantes e ON r.estudiante_id = e.estudiante_id
                 JOIN responsables res ON r.responsable_id = res.responsable_id
                 ORDER BY r.fecha_registro DESC
                 LIMIT 5";
        $stmt = $conn->query($query);
        $ultimosRegistros = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Obtener distribución de estados de registros
        $query = "SELECT estado, COUNT(*) as cantidad 
                 FROM registroshoras 
                 GROUP BY estado";
        $stmt = $conn->query($query);
        $distribucionEstados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Preparar respuesta
        $response = [
            'totalEstudiantes' => (int)$totalEstudiantes,
            'totalResponsables' => (int)$totalResponsables,
            'horasPendientes' => (float)$horasPendientes,
            'totalAsignaciones' => (int)$totalAsignaciones,
            'estudiantesPorCarrera' => $estudiantesPorCarrera,
            'estudiantesHorasPendientes' => $estudiantesHorasPendientes,
            'ultimosRegistros' => $ultimosRegistros,
            'distribucionEstados' => $distribucionEstados
        ];
        
        echo json_encode($response);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error en la consulta: ' . $e->getMessage()]);
    }
}
?>
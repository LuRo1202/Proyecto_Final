<?php
// Habilitar la visualización de errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../conexion.php'; // Tu archivo de conexión PDO

try {
    // Consulta SQL SIN el filtro WHERE para obtener TODAS las solicitudes
    $sql = "SELECT 
                s.solicitud_id,
                CONCAT(e.nombre, ' ', e.apellido_paterno, ' ', e.apellido_materno) AS alumno_nombre,
                e.matricula,
                e.carrera,
                e.horas_completadas,
                er.nombre AS entidad_nombre,
                DATE_FORMAT(s.periodo_inicio, '%b-%Y') AS periodo_inicio_fmt,
                DATE_FORMAT(s.periodo_fin, '%b-%Y') AS periodo_fin_fmt,
                s.estado AS estado_solicitud, -- 'pendiente', 'aprobada', 'rechazada'
                s.estado_carta_presentacion,
                s.estado_carta_aceptacion,
                s.estado_carta_termino
            FROM solicitudes s
            JOIN estudiantes e ON s.estudiante_id = e.estudiante_id
            JOIN entidades_receptoras er ON s.entidad_id = er.entidad_id
            -- La siguiente línea fue eliminada para mostrar todos los alumnos --
            -- WHERE s.estado = 'aprobada' 
            ORDER BY s.solicitud_id DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $datos = [];

    foreach ($resultados as $fila) {
        // El estado del servicio solo aplica a los aprobados
        if ($fila['estado_solicitud'] === 'aprobada') {
            $fila['estado_servicio'] = ($fila['horas_completadas'] >= 480) ? 'Finalizado' : 'En Proceso';
        } else {
            $fila['estado_servicio'] = 'N/A'; // No aplica si no está aprobado
        }
        
        $fila['periodo_servicio'] = ucfirst($fila['periodo_inicio_fmt']) . ' - ' . ucfirst($fila['periodo_fin_fmt']);
        $datos[] = $fila;
    }

    header('Content-Type: application/json');
    echo json_encode($datos);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()]);
}

$conn = null;
?>
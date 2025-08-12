<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

header('Content-Type: application/json; charset=utf-8');
require_once '../conexion.php'; 

session_start();

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso denegado. No ha iniciado sesión.']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$estudiante_id = null;

try {
    $stmt_estudiante = $conn->prepare("SELECT estudiante_id FROM estudiantes WHERE usuario_id = :usuario_id");
    $stmt_estudiante->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
    $stmt_estudiante->execute();
    $estudiante = $stmt_estudiante->fetch(PDO::FETCH_ASSOC);

    if (!$estudiante) {
        http_response_code(404);
        echo json_encode(['error' => 'No se encontró el perfil del estudiante.']);
        exit;
    }
    $estudiante_id = $estudiante['estudiante_id'];

} catch (PDOException $e) {
    http_response_code(500);
    error_log("Error al buscar estudiante: " . $e->getMessage());
    echo json_encode(['error' => 'Error en la base de datos al buscar estudiante.']);
    exit;
}

$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo === 'GET') {
    try {
        $sql = "SELECT
                    s.solicitud_id, s.entidad_id, s.periodo_inicio, s.periodo_fin,
                    s.horario_lv_inicio, s.horario_lv_fin, s.horario_sd_inicio, s.horario_sd_fin,
                    s.actividades, s.fecha_solicitud, s.programa_id,
                    e.nombre, e.apellido_paterno, e.apellido_materno, e.matricula, e.curp, e.sexo, e.edad,
                    u.correo, e.telefono, e.domicilio, e.facebook, e.carrera, e.cuatrimestre,
                    e.porcentaje_creditos, e.promedio,
                    er.nombre as entidad_nombre, er.tipo_entidad, er.unidad_administrativa,
                    er.domicilio as entidad_domicilio, er.municipio as entidad_municipio, er.telefono as entidad_telefono,
                    er.funcionario_responsable, er.cargo_funcionario,
                    p.nombre as programa_nombre, p.programa_id,
                    pr.nombre as periodo_nombre
                FROM solicitudes s
                JOIN estudiantes e ON s.estudiante_id = e.estudiante_id
                JOIN usuarios u ON e.usuario_id = u.usuario_id
                JOIN entidades_receptoras er ON s.entidad_id = er.entidad_id
                JOIN programas p ON s.programa_id = p.programa_id
                JOIN periodos_registro pr ON s.periodo_id = pr.periodo_id
                WHERE s.estudiante_id = :estudiante_id
                ORDER BY s.solicitud_id DESC
                LIMIT 1";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':estudiante_id', $estudiante_id, PDO::PARAM_INT);
        $stmt->execute();
        $solicitud = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($solicitud) {
            echo json_encode($solicitud);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'No se encontró ninguna solicitud para este estudiante.']);
        }

    } catch (PDOException $e) {
        http_response_code(500);
        error_log("Error al obtener solicitud: " . $e->getMessage());
        echo json_encode(['error' => 'Error al cargar los datos de la solicitud.']);
    }

} elseif ($metodo === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data) {
        http_response_code(400);
        echo json_encode(['error' => 'No se recibieron datos.']);
        exit;
    }

    $conn->beginTransaction();
    try {
        // 1. Actualizar tabla 'estudiantes'
        $sql_estudiante = "UPDATE estudiantes SET
            nombre = :nombre, apellido_paterno = :apellido_paterno, apellido_materno = :apellido_materno,
            matricula = :matricula, curp = :curp, sexo = :sexo, telefono = :telefono, edad = :edad,
            domicilio = :domicilio, facebook = :facebook, carrera = :carrera,
            cuatrimestre = :cuatrimestre, porcentaje_creditos = :porcentaje_creditos, promedio = :promedio
            WHERE estudiante_id = :estudiante_id";
        $stmt_est = $conn->prepare($sql_estudiante);
        $stmt_est->execute([
            ':nombre' => $data['nombre'], ':apellido_paterno' => $data['apellido_paterno'], ':apellido_materno' => $data['apellido_materno'],
            ':matricula' => $data['matricula'], ':curp' => $data['curp'], ':sexo' => $data['sexo'], ':telefono' => $data['telefono'], ':edad' => $data['edad'],
            ':domicilio' => $data['domicilio'], ':facebook' => $data['facebook'], ':carrera' => $data['carrera'],
            ':cuatrimestre' => $data['cuatrimestre'], ':porcentaje_creditos' => $data['porcentaje_creditos'], ':promedio' => $data['promedio'],
            ':estudiante_id' => $estudiante_id
        ]);

        // 2. Actualizar tabla 'entidades_receptoras'
        $sql_entidad = "UPDATE entidades_receptoras SET
            nombre = :entidad_nombre, tipo_entidad = :tipo_entidad, unidad_administrativa = :unidad_administrativa,
            domicilio = :entidad_domicilio, municipio = :entidad_municipio, telefono = :entidad_telefono,
            funcionario_responsable = :funcionario_responsable, cargo_funcionario = :cargo_funcionario
            WHERE entidad_id = :entidad_id";
        $stmt_entidad = $conn->prepare($sql_entidad);
        $stmt_entidad->execute([
            ':entidad_nombre' => $data['entidad_nombre'], ':tipo_entidad' => $data['tipo_entidad'], ':unidad_administrativa' => $data['unidad_administrativa'],
            ':entidad_domicilio' => $data['entidad_domicilio'], ':entidad_municipio' => $data['entidad_municipio'], ':entidad_telefono' => $data['entidad_telefono'],
            ':funcionario_responsable' => $data['funcionario_responsable'], ':cargo_funcionario' => $data['cargo_funcionario'],
            ':entidad_id' => $data['entidad_id']
        ]);

        // 3. Actualizar tabla 'solicitudes'
        $sql_solicitud = "UPDATE solicitudes SET
            programa_id = :programa_id, actividades = :actividades,
            periodo_inicio = :periodo_inicio, periodo_fin = :periodo_fin,
            horario_lv_inicio = :horario_lv_inicio, horario_lv_fin = :horario_lv_fin,
            horario_sd_inicio = :horario_sd_inicio, horario_sd_fin = :horario_sd_fin
            WHERE solicitud_id = :solicitud_id";
        $stmt_sol = $conn->prepare($sql_solicitud);
        $stmt_sol->execute([
            ':programa_id' => $data['programa_id'], ':actividades' => $data['actividades'],
            ':periodo_inicio' => $data['periodo_inicio'], ':periodo_fin' => $data['periodo_fin'],
            ':horario_lv_inicio' => !empty($data['horario_lv_inicio']) ? $data['horario_lv_inicio'] : null,
            ':horario_lv_fin' => !empty($data['horario_lv_fin']) ? $data['horario_lv_fin'] : null,
            ':horario_sd_inicio' => !empty($data['horario_sd_inicio']) ? $data['horario_sd_inicio'] : null,
            ':horario_sd_fin' => !empty($data['horario_sd_fin']) ? $data['horario_sd_fin'] : null,
            ':solicitud_id' => $data['solicitud_id']
        ]);

        $conn->commit();
        echo json_encode(['success' => 'Solicitud actualizada correctamente.']);

    } catch (PDOException $e) {
        $conn->rollBack();
        http_response_code(500);
        error_log("Error al actualizar solicitud: " . $e->getMessage());
        echo json_encode(['error' => 'Error al guardar los cambios. ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido.']);
}
?>
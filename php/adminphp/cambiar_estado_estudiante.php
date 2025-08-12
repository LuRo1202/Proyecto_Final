<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../conexion.php';

$data = json_decode(file_get_contents('php://input'), true);
$response = ['success' => false, 'message' => ''];

try {
    // Validar que los datos necesarios fueron enviados
    if (!isset($data['estudiante_id']) || !isset($data['activo'])) {
        throw new Exception("Datos incompletos.");
    }

    $estudiante_id = filter_var($data['estudiante_id'], FILTER_VALIDATE_INT);
    $activo = filter_var($data['activo'], FILTER_VALIDATE_INT);

    if ($estudiante_id === false || ($activo !== 0 && $activo !== 1)) {
        throw new Exception("Datos inválidos.");
    }

    $conn->beginTransaction();

    // 1. Actualizar el estado en la tabla 'estudiantes'
    $stmtEstudiante = $conn->prepare("UPDATE estudiantes SET activo = ? WHERE estudiante_id = ?");
    $stmtEstudiante->execute([$activo, $estudiante_id]);

    // 2. Actualizar también el estado en la tabla 'usuarios' para mantener la consistencia
    $stmtUsuario = $conn->prepare(
        "UPDATE usuarios u
         JOIN estudiantes e ON u.usuario_id = e.usuario_id
         SET u.activo = ?
         WHERE e.estudiante_id = ?"
    );
    $stmtUsuario->execute([$activo, $estudiante_id]);

    // Verificar si se afectó alguna fila para confirmar el éxito
    if ($stmtEstudiante->rowCount() > 0) {
        $conn->commit();
        $response['success'] = true;
        $response['message'] = 'Estado del estudiante actualizado correctamente.';
    } else {
        throw new Exception("No se encontró al estudiante o el estado ya era el mismo.");
    }
} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    $response['message'] = 'Error en la base de datos: ' . $e->getMessage();
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>
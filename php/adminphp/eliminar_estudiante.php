<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../conexion.php'; // Asegúrate que la ruta a tu conexión es correcta

// Usar la conexión de la base de datos que ya tienes en tu proyecto
// Asumo que la variable de conexión se llama $conn y usa PDO. Si usas mysqli, el código cambiará ligeramente.

$data = json_decode(file_get_contents('php://input'), true);
$response = ['success' => false, 'message' => ''];

try {
    // Validar que se recibió el ID del estudiante
    if (empty($data['estudiante_id'])) {
        throw new Exception("ID de estudiante no proporcionado");
    }

    $estudiante_id = $data['estudiante_id'];

    // Iniciar transacción
    $conn->beginTransaction();

    // 1. Obtener el usuario_id del estudiante
    $sqlUsuarioId = "SELECT usuario_id FROM estudiantes WHERE estudiante_id = ?";
    $stmtUsuarioId = $conn->prepare($sqlUsuarioId);
    $stmtUsuarioId->execute([$estudiante_id]);
    $usuario_id = $stmtUsuarioId->fetchColumn();

    if (!$usuario_id) {
        throw new Exception("Estudiante no encontrado");
    }

    // 2. Eliminar registros de horas del estudiante
    $sqlEliminarRegistros = "DELETE FROM registroshoras WHERE estudiante_id = ?";
    $stmtEliminarRegistros = $conn->prepare($sqlEliminarRegistros);
    $stmtEliminarRegistros->execute([$estudiante_id]);

    // 3. Eliminar relaciones con responsables
    $sqlEliminarResponsables = "DELETE FROM estudiantes_responsables WHERE estudiante_id = ?";
    $stmtEliminarResponsables = $conn->prepare($sqlEliminarResponsables);
    $stmtEliminarResponsables->execute([$estudiante_id]);

    // 4. NUEVO PASO: Eliminar solicitudes asociadas al estudiante
    // Esta es la línea que soluciona el error de la llave foránea.
    $sqlEliminarSolicitudes = "DELETE FROM solicitudes WHERE estudiante_id = ?";
    $stmtEliminarSolicitudes = $conn->prepare($sqlEliminarSolicitudes);
    $stmtEliminarSolicitudes->execute([$estudiante_id]);

    // 5. Eliminar el estudiante (ahora sí se podrá)
    $sqlEliminarEstudiante = "DELETE FROM estudiantes WHERE estudiante_id = ?";
    $stmtEliminarEstudiante = $conn->prepare($sqlEliminarEstudiante);
    $stmtEliminarEstudiante->execute([$estudiante_id]);

    // 6. Eliminar el usuario asociado
    $sqlEliminarUsuario = "DELETE FROM usuarios WHERE usuario_id = ?";
    $stmtEliminarUsuario = $conn->prepare($sqlEliminarUsuario);
    $stmtEliminarUsuario->execute([$usuario_id]);

    // Si todo salió bien, confirma la transacción
    $conn->commit();
    $response['success'] = true;
    $response['message'] = 'Estudiante y toda su información asociada han sido eliminados correctamente.';

} catch (PDOException $e) {
    // Si algo falla, revierte todos los cambios
    $conn->rollBack();
    $response['message'] = 'Error en la base de datos: ' . $e->getMessage();
    http_response_code(500); // Enviar un código de error de servidor
} catch (Exception $e) {
    // Si algo falla, revierte todos los cambios
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    $response['message'] = $e->getMessage();
    http_response_code(400); // Enviar un código de error de cliente (p. ej. mal request)
}

echo json_encode($response);
?>
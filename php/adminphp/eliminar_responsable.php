<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../conexion.php';

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['responsable_id'])) {
    echo json_encode(['success' => false, 'message' => 'ID no proporcionado.']);
    exit;
}

try {
    $conn->beginTransaction();

    $stmtUserId = $conn->prepare("SELECT usuario_id FROM responsables WHERE responsable_id = ?");
    $stmtUserId->execute([$data['responsable_id']]);
    $usuario_id = $stmtUserId->fetchColumn();

    if ($usuario_id) {
        // Eliminar de la tabla 'responsables' primero
        $stmtResp = $conn->prepare("DELETE FROM responsables WHERE responsable_id = ?");
        $stmtResp->execute([$data['responsable_id']]);

        // Luego eliminar de la tabla 'usuarios'
        $stmtUser = $conn->prepare("DELETE FROM usuarios WHERE usuario_id = ?");
        $stmtUser->execute([$usuario_id]);
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Responsable eliminado correctamente.']);

} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
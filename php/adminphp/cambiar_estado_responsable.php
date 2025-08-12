<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../conexion.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['responsable_id']) || !isset($data['activo'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
    exit;
}

try {
    $conn->beginTransaction();

    $stmt = $conn->prepare(
        "UPDATE responsables r JOIN usuarios u ON r.usuario_id = u.usuario_id 
         SET r.activo = ?, u.activo = ? 
         WHERE r.responsable_id = ?"
    );
    $stmt->execute([$data['activo'], $data['activo'], $data['responsable_id']]);

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Estado actualizado.']);

} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error al actualizar: ' . $e->getMessage()]);
}
?>
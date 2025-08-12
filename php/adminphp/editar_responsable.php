<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../conexion.php';

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['responsable_id']) || empty($data['nombre']) || empty($data['apellido_paterno']) || empty($data['correo'])) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos obligatorios.']);
    exit;
}

try {
    $conn->beginTransaction();
    
    $stmtUserId = $conn->prepare("SELECT usuario_id FROM responsables WHERE responsable_id = ?");
    $stmtUserId->execute([$data['responsable_id']]);
    $usuario_id = $stmtUserId->fetchColumn();

    if(!$usuario_id) throw new Exception("Responsable no encontrado.");

    $stmtCheck = $conn->prepare("SELECT COUNT(*) FROM usuarios WHERE correo = ? AND usuario_id != ?");
    $stmtCheck->execute([$data['correo'], $usuario_id]);
    if ($stmtCheck->fetchColumn() > 0) {
        throw new Exception("El correo electrónico ya pertenece a otro usuario.");
    }

    $stmtUsuario = $conn->prepare("UPDATE usuarios SET correo = ? WHERE usuario_id = ?");
    $stmtUsuario->execute([$data['correo'], $usuario_id]);

    if (!empty($data['contrasena'])) {
        $hashedPassword = password_hash($data['contrasena'], PASSWORD_DEFAULT);
        $stmtPass = $conn->prepare("UPDATE usuarios SET contrasena = ? WHERE usuario_id = ?");
        $stmtPass->execute([$hashedPassword, $usuario_id]);
    }

    $stmtResponsable = $conn->prepare(
        "UPDATE responsables SET nombre = ?, apellido_paterno = ?, apellido_materno = ?, cargo = ?, departamento = ?, telefono = ?
         WHERE responsable_id = ?"
    );
    $stmtResponsable->execute([
        $data['nombre'], $data['apellido_paterno'], $data['apellido_materno'],
        $data['cargo'], $data['departamento'], $data['telefono'], $data['responsable_id']
    ]);

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Responsable actualizado correctamente.']);

} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
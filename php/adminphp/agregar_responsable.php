<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../conexion.php';

$data = json_decode(file_get_contents('php://input'), true);
$ROL_ENCARGADO = 2;

if (empty($data['nombre']) || empty($data['apellido_paterno']) || empty($data['correo']) || empty($data['contrasena'])) {
    echo json_encode(['success' => false, 'message' => 'Los campos con (*) son obligatorios.']);
    exit;
}

try {
    $conn->beginTransaction();

    $stmtCheck = $conn->prepare("SELECT COUNT(*) FROM usuarios WHERE correo = ?");
    $stmtCheck->execute([$data['correo']]);
    if ($stmtCheck->fetchColumn() > 0) {
        throw new Exception("El correo electrónico ya está registrado.");
    }

    $hashedPassword = password_hash($data['contrasena'], PASSWORD_DEFAULT);
    $stmtUsuario = $conn->prepare("INSERT INTO usuarios (correo, contrasena, rol_id, activo, tipo_usuario) VALUES (?, ?, ?, 1, 'encargado')");
    $stmtUsuario->execute([$data['correo'], $hashedPassword, $ROL_ENCARGADO]);
    $usuario_id = $conn->lastInsertId();

    $stmtResponsable = $conn->prepare(
        "INSERT INTO responsables (usuario_id, nombre, apellido_paterno, apellido_materno, cargo, departamento, telefono, activo) 
         VALUES (?, ?, ?, ?, ?, ?, ?, 1)"
    );
    $stmtResponsable->execute([
        $usuario_id, $data['nombre'], $data['apellido_paterno'], $data['apellido_materno'], 
        $data['cargo'], $data['departamento'], $data['telefono']
    ]);

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Responsable agregado correctamente.']);

} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
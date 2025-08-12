<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../conexion.php';

$data = json_decode(file_get_contents('php://input'), true);
$response = ['success' => false, 'message' => ''];
$ROL_ESTUDIANTE = 3; // ID del rol para 'estudiante'

try {
    // 1. Validar datos requeridos
    if (empty($data['matricula']) || empty($data['nombre']) || empty($data['apellido_paterno']) || empty($data['correo']) || empty($data['contrasena'])) {
        throw new Exception("Matrícula, nombre, apellido paterno, correo y contraseña son requeridos.");
    }

    $conn->beginTransaction();

    // 2. Verificar si el correo o la matrícula ya existen
    $sqlVerificar = "SELECT 
        (SELECT COUNT(*) FROM usuarios WHERE correo = ?) as correo_count,
        (SELECT COUNT(*) FROM estudiantes WHERE matricula = ?) as matricula_count";
    $stmtVerificar = $conn->prepare($sqlVerificar);
    $stmtVerificar->execute([$data['correo'], $data['matricula']]);
    $counts = $stmtVerificar->fetch(PDO::FETCH_ASSOC);

    if ($counts['correo_count'] > 0) {
        throw new Exception("El correo electrónico ya está registrado.");
    }
    if ($counts['matricula_count'] > 0) {
        throw new Exception("La matrícula ya está registrada.");
    }

    // 3. Insertar en la tabla `usuarios`
    $hashedPassword = password_hash($data['contrasena'], PASSWORD_DEFAULT);
    $sqlUsuario = "INSERT INTO usuarios (correo, contrasena, rol_id, activo, tipo_usuario) VALUES (?, ?, ?, ?, 'estudiante')";
    $stmtUsuario = $conn->prepare($sqlUsuario);
    $stmtUsuario->execute([
        $data['correo'],
        $hashedPassword,
        $ROL_ESTUDIANTE,
        $data['activo'] ? 1 : 0
    ]);
    $usuario_id = $conn->lastInsertId();

    // 4. Insertar en la tabla `estudiantes`
    $sqlEstudiante = "INSERT INTO estudiantes (
        usuario_id, matricula, nombre, apellido_paterno, apellido_materno, 
        carrera, cuatrimestre, telefono, activo
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmtEstudiante = $conn->prepare($sqlEstudiante);
    $stmtEstudiante->execute([
        $usuario_id,
        $data['matricula'],
        $data['nombre'],
        $data['apellido_paterno'],
        $data['apellido_materno'] ?? null,
        $data['carrera'] ?? null,
        $data['cuatrimestre'] ?? null,
        $data['telefono'] ?? null,
        $data['activo'] ? 1 : 0
    ]);

    $conn->commit();
    $response['success'] = true;
    $response['message'] = 'Estudiante agregado correctamente';
    
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

if (ob_get_level() > 0) {
    ob_end_clean();
}
echo json_encode($response);
?>
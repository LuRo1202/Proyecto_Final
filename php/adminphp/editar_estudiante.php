<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../conexion.php';

$data = json_decode(file_get_contents('php://input'), true);
$response = ['success' => false, 'message' => ''];

try {
    // Validar datos requeridos
    if (empty($data['estudiante_id']) || empty($data['matricula']) || empty($data['nombre']) || empty($data['apellido_paterno']) || empty($data['correo'])) {
        throw new Exception("ID, Matrícula, Nombre, Apellido Paterno y Correo son requeridos.");
    }

    // Obtener el usuario_id del estudiante
    $sqlUsuarioId = "SELECT usuario_id FROM estudiantes WHERE estudiante_id = ?";
    $stmtUsuarioId = $conn->prepare($sqlUsuarioId);
    $stmtUsuarioId->execute([$data['estudiante_id']]);
    $usuario_id = $stmtUsuarioId->fetchColumn();

    if (!$usuario_id) {
        throw new Exception("Estudiante no encontrado");
    }

    // Verificar si el nuevo correo ya existe en otro usuario
    $sqlVerificarCorreo = "SELECT usuario_id FROM usuarios WHERE correo = ? AND usuario_id != ?";
    $stmtVerificarCorreo = $conn->prepare($sqlVerificarCorreo);
    $stmtVerificarCorreo->execute([$data['correo'], $usuario_id]);
    
    if ($stmtVerificarCorreo->rowCount() > 0) {
        throw new Exception("El correo electrónico ya está registrado en otra cuenta");
    }

    // Verificar si la nueva matrícula ya existe en otro estudiante
    $sqlVerificarMatricula = "SELECT estudiante_id FROM estudiantes WHERE matricula = ? AND estudiante_id != ?";
    $stmtVerificarMatricula = $conn->prepare($sqlVerificarMatricula);
    $stmtVerificarMatricula->execute([$data['matricula'], $data['estudiante_id']]);
    
    if ($stmtVerificarMatricula->rowCount() > 0) {
        throw new Exception("La matrícula ya está registrada por otro estudiante");
    }

    $conn->beginTransaction();

    // Actualizar tabla usuarios
    $sqlUsuario = "UPDATE usuarios SET correo = ?, activo = ? WHERE usuario_id = ?";
    $stmtUsuario = $conn->prepare($sqlUsuario);
    $stmtUsuario->execute([
        $data['correo'],
        $data['activo'] ? 1 : 0,
        $usuario_id
    ]);

    // Si se proporcionó una nueva contraseña, actualizarla
    if (!empty($data['contrasena'])) {
        $hashedPassword = password_hash($data['contrasena'], PASSWORD_DEFAULT);
        $sqlContrasena = "UPDATE usuarios SET contrasena = ? WHERE usuario_id = ?";
        $stmtContrasena = $conn->prepare($sqlContrasena);
        $stmtContrasena->execute([$hashedPassword, $usuario_id]);
    }

    // Actualizar tabla estudiantes
    $sqlEstudiante = "UPDATE estudiantes SET 
                        matricula = ?, 
                        nombre = ?, 
                        apellido_paterno = ?, 
                        apellido_materno = ?, 
                        carrera = ?, 
                        cuatrimestre = ?, 
                        telefono = ?, 
                        activo = ? 
                      WHERE estudiante_id = ?";
    $stmtEstudiante = $conn->prepare($sqlEstudiante);
    $stmtEstudiante->execute([
        $data['matricula'],
        $data['nombre'],
        $data['apellido_paterno'],
        $data['apellido_materno'] ?? null,
        $data['carrera'] ?? null,
        $data['cuatrimestre'] ?? null,
        $data['telefono'] ?? null,
        $data['activo'] ? 1 : 0,
        $data['estudiante_id']
    ]);

    $conn->commit();
    $response['success'] = true;
    $response['message'] = 'Estudiante actualizado correctamente';
    
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
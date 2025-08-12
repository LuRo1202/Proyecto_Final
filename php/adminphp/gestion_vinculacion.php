<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../conexion.php';

$response = ['success' => false, 'message' => '', 'data' => []];

try {
    $pdo = $conn;
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $action = $_GET['action'] ?? '';

    switch ($action) {
        case 'listar':
            $query = "SELECT v.vinculacion_id, v.nombre, v.apellido_paterno, v.apellido_materno, 
                             v.telefono, v.activo, u.correo 
                      FROM vinculacion v 
                      JOIN usuarios u ON v.usuario_id = u.usuario_id 
                      ORDER BY v.nombre";
            $stmt = $pdo->query($query);
            
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response['data'] = $resultados;
            $response['success'] = true;
            break;

        case 'obtener':
            if (empty($_GET['correo'])) {
                throw new Exception('Se requiere el correo del personal');
            }

            $correo = $_GET['correo'];
            $query = "SELECT v.vinculacion_id as id, v.nombre, v.apellido_paterno, v.apellido_materno, 
                             v.telefono, u.correo, v.activo
                      FROM vinculacion v 
                      JOIN usuarios u ON v.usuario_id = u.usuario_id 
                      WHERE u.correo = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$correo]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                $response['success'] = true;
                $response['data'] = $result;
            } else {
                throw new Exception('No se encontró el registro solicitado');
            }
            break;

        case 'crear':
            $data = $_POST;

            $required = ['nombre', 'apellido_paterno', 'correo'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    throw new Exception("El campo $field es requerido");
                }
            }

            if (!filter_var($data['correo'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception('El correo electrónico no es válido');
            }

            $query = "SELECT usuario_id FROM usuarios WHERE correo = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$data['correo']]);

            if ($stmt->fetch()) {
                throw new Exception('El correo electrónico ya está registrado');
            }

            $pdo->beginTransaction();

            $contrasena = !empty($data['contrasena']) ? 
                password_hash($data['contrasena'], PASSWORD_DEFAULT) : 
                password_hash('Vinculacion123', PASSWORD_DEFAULT);
            
            $query = "INSERT INTO usuarios (correo, contrasena, rol_id, tipo_usuario, activo) 
                      VALUES (?, ?, 4, 'vinculacion', 1)";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$data['correo'], $contrasena]);
            $usuario_id = $pdo->lastInsertId();

            $query = "INSERT INTO vinculacion 
                      (usuario_id, nombre, apellido_paterno, apellido_materno, telefono, activo) 
                      VALUES (?, ?, ?, ?, ?, 1)";
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                $usuario_id,
                $data['nombre'],
                $data['apellido_paterno'],
                $data['apellido_materno'] ?? null,
                $data['telefono'] ?? null
            ]);

            $vinculacion_id = $pdo->lastInsertId();

            $pdo->commit();

            $response['success'] = true;
            $response['message'] = 'Personal de vinculación creado exitosamente';
            $response['id'] = $vinculacion_id;
            break;

        case 'actualizar':
            $data = $_POST;

            if (empty($data['id'])) {
                throw new Exception('Se requiere identificar al personal para actualizar');
            }

            $required = ['nombre', 'apellido_paterno', 'correo'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    throw new Exception("El campo $field es requerido");
                }
            }

            $query = "SELECT usuario_id FROM vinculacion WHERE vinculacion_id = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$data['id']]);
            $result = $stmt->fetch();

            if (!$result) {
                throw new Exception('No se encontró el registro a actualizar');
            }
            $usuario_id = $result['usuario_id'];

            $pdo->beginTransaction();

            if (!empty($data['contrasena'])) {
                $contrasena = password_hash($data['contrasena'], PASSWORD_DEFAULT);
                $query = "UPDATE usuarios SET correo = ?, contrasena = ? WHERE usuario_id = ?";
                $stmt = $pdo->prepare($query);
                $stmt->execute([$data['correo'], $contrasena, $usuario_id]);
            } else {
                $query = "UPDATE usuarios SET correo = ? WHERE usuario_id = ?";
                $stmt = $pdo->prepare($query);
                $stmt->execute([$data['correo'], $usuario_id]);
            }

            $query = "UPDATE vinculacion SET 
                      nombre = ?, 
                      apellido_paterno = ?, 
                      apellido_materno = ?, 
                      telefono = ? 
                      WHERE vinculacion_id = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                $data['nombre'],
                $data['apellido_paterno'],
                $data['apellido_materno'] ?? null,
                $data['telefono'] ?? null,
                $data['id']
            ]);

            $pdo->commit();

            $response['success'] = true;
            $response['message'] = 'Registro actualizado exitosamente';
            break;

        case 'cambiar_estado':
            if (empty($_POST['correo'])) {
                throw new Exception('Se requiere el correo del personal');
            }

            $correo = $_POST['correo'];
            
            // Obtener el estado actual
            $query = "SELECT v.activo 
                      FROM vinculacion v 
                      JOIN usuarios u ON v.usuario_id = u.usuario_id 
                      WHERE u.correo = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$correo]);
            $result = $stmt->fetch();

            if (!$result) {
                throw new Exception('No se encontró el registro');
            }

            $nuevo_estado = $result['activo'] ? 0 : 1;

            $pdo->beginTransaction();

            // Actualizar estado en vinculacion
            $query = "UPDATE vinculacion v
                      JOIN usuarios u ON v.usuario_id = u.usuario_id
                      SET v.activo = ?
                      WHERE u.correo = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$nuevo_estado, $correo]);

            // Actualizar estado en usuarios
            $query = "UPDATE usuarios 
                      SET activo = ?
                      WHERE correo = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$nuevo_estado, $correo]);

            $pdo->commit();

            $response['success'] = true;
            $response['message'] = 'Estado actualizado correctamente';
            $response['nuevo_estado'] = $nuevo_estado;
            break;

        case 'eliminar':
            $data = $_POST;

            if (empty($data['correo'])) {
                throw new Exception('Se requiere el correo del personal para eliminar');
            }

            $query = "SELECT v.vinculacion_id, v.usuario_id 
                      FROM vinculacion v 
                      JOIN usuarios u ON v.usuario_id = u.usuario_id 
                      WHERE u.correo = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$data['correo']]);
            $result = $stmt->fetch();

            if (!$result) {
                throw new Exception('No se encontró el registro a eliminar');
            }
            $vinculacion_id = $result['vinculacion_id'];
            $usuario_id = $result['usuario_id'];

            $pdo->beginTransaction();

            $query = "DELETE FROM vinculacion WHERE vinculacion_id = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$vinculacion_id]);

            $query = "DELETE FROM usuarios WHERE usuario_id = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$usuario_id]);

            $pdo->commit();

            $response['success'] = true;
            $response['message'] = 'Registro eliminado exitosamente';
            break;

        default:
            throw new Exception('Acción no válida');
    }
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $response['message'] = 'Error de base de datos: ' . $e->getMessage();
    error_log('PDOException en gestion_vinculacion.php: ' . $e->getMessage());
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $response['message'] = $e->getMessage();
    error_log('Exception en gestion_vinculacion.php: ' . $e->getMessage());
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>
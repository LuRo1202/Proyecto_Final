<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../conexion.php';

$response = ['success' => false, 'message' => ''];

try {
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
                if(!$id) throw new Exception("ID inválido.");

                $query = "SELECT a.*, u.correo FROM administradores a 
                          JOIN usuarios u ON a.usuario_id = u.usuario_id WHERE a.admin_id = ?";
                $stmt = $conn->prepare($query);
                $stmt->execute([$id]);
                $admin = $stmt->fetch(PDO::FETCH_ASSOC);

                $response['success'] = true;
                $response['data'] = $admin;

            } else {
                $query = "SELECT a.admin_id, a.nombre, a.apellido_paterno, a.apellido_materno, 
                                 a.telefono, a.activo, u.correo, u.ultimo_login
                          FROM administradores a
                          JOIN usuarios u ON a.usuario_id = u.usuario_id
                          ORDER BY a.apellido_paterno, a.nombre";
                $stmt = $conn->query($query);
                $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $response['success'] = true;
                $response['data'] = $admins;
            }
            break;
            
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['nombre']) || empty($data['apellido_paterno']) || empty($data['correo']) || empty($data['contrasena'])) {
                throw new Exception("Nombre, Apellido Paterno, Correo y Contraseña son obligatorios.");
            }
            
            $conn->beginTransaction();
            
            $stmtCheck = $conn->prepare("SELECT COUNT(*) FROM usuarios WHERE correo = ?");
            $stmtCheck->execute([$data['correo']]);
            if ($stmtCheck->fetchColumn() > 0) throw new Exception("El correo ya está registrado.");
            
            $queryUsuario = "INSERT INTO usuarios (correo, contrasena, rol_id, tipo_usuario, activo) VALUES (?, ?, 1, 'admin', 1)";
            $hashedPassword = password_hash($data['contrasena'], PASSWORD_DEFAULT);
            $stmtUsuario = $conn->prepare($queryUsuario);
            $stmtUsuario->execute([$data['correo'], $hashedPassword]);
            $usuario_id = $conn->lastInsertId();
            
            $queryAdmin = "INSERT INTO administradores (usuario_id, nombre, apellido_paterno, apellido_materno, telefono, activo) VALUES (?, ?, ?, ?, ?, 1)";
            $stmtAdmin = $conn->prepare($queryAdmin);
            $stmtAdmin->execute([
                $usuario_id, $data['nombre'], $data['apellido_paterno'],
                $data['apellido_materno'] ?? null, $data['telefono'] ?? null
            ]);
            
            $conn->commit();
            $response['success'] = true;
            $response['message'] = "Administrador creado correctamente";
            break;
            
        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['admin_id'])) throw new Exception("ID de administrador no proporcionado");

            $conn->beginTransaction();
            
            if (isset($data['activo']) && count($data) == 2) {
                $queryEstado = "UPDATE administradores a JOIN usuarios u ON a.usuario_id = u.usuario_id 
                                SET a.activo = ?, u.activo = ? 
                                WHERE a.admin_id = ?";
                $stmtEstado = $conn->prepare($queryEstado);
                $stmtEstado->execute([$data['activo'], $data['activo'], $data['admin_id']]);

                $conn->commit();
                $response['success'] = true;
                $response['message'] = "Estado del administrador actualizado";
                break;
            }

            if (empty($data['nombre']) || empty($data['apellido_paterno']) || empty($data['correo'])) {
                throw new Exception("Nombre, Apellido Paterno y Correo son obligatorios.");
            }
            
            $stmtGetUsuario = $conn->prepare("SELECT usuario_id FROM administradores WHERE admin_id = ?");
            $stmtGetUsuario->execute([$data['admin_id']]);
            $usuario_id = $stmtGetUsuario->fetchColumn();
            
            if (!$usuario_id) throw new Exception("No se encontró el administrador");
            
            $stmtUsuario = $conn->prepare("UPDATE usuarios SET correo = ? WHERE usuario_id = ?");
            $stmtUsuario->execute([$data['correo'], $usuario_id]);
            
            if (!empty($data['contrasena'])) {
                $hashedPassword = password_hash($data['contrasena'], PASSWORD_DEFAULT);
                $queryPassword = "UPDATE usuarios SET contrasena = ? WHERE usuario_id = ?";
                $stmtPassword = $conn->prepare($queryPassword);
                $stmtPassword->execute([$hashedPassword, $usuario_id]);
            }
            
            $queryAdmin = "UPDATE administradores SET 
                                nombre = ?, apellido_paterno = ?, apellido_materno = ?, telefono = ?
                           WHERE admin_id = ?";
            $stmtAdmin = $conn->prepare($queryAdmin);
            $stmtAdmin->execute([
                $data['nombre'], $data['apellido_paterno'], $data['apellido_materno'] ?? null,
                $data['telefono'] ?? null, $data['admin_id']
            ]);
            
            $conn->commit();
            $response['success'] = true;
            $response['message'] = "Administrador actualizado correctamente";
            break;
            
        case 'DELETE':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['admin_id'])) throw new Exception("ID de administrador no proporcionado");
            
            $conn->beginTransaction();
            
            $stmtGetUsuario = $conn->prepare("SELECT usuario_id FROM administradores WHERE admin_id = ?");
            $stmtGetUsuario->execute([$data['admin_id']]);
            $usuario_id = $stmtGetUsuario->fetchColumn();
            
            if (!$usuario_id) throw new Exception("No se encontró el administrador");
            
            $stmtDeleteUsuario = $conn->prepare("DELETE FROM usuarios WHERE usuario_id = ?");
            $stmtDeleteUsuario->execute([$usuario_id]);
            
            $conn->commit();
            $response['success'] = true;
            $response['message'] = "Administrador eliminado correctamente";
            break;
            
        default:
            throw new Exception("Método no permitido");
    }
    
} catch (Exception $e) {
    if ($conn->inTransaction()) $conn->rollBack();
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>
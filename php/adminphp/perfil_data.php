<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Considerar restringir esto en producción para mayor seguridad
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

session_start();

// Configuración de la base de datos
$host = 'localhost';
$dbname = 'servicio_social';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Obtener ID del administrador desde la sesión
    if (!isset($_SESSION['usuario_id'])) {
        throw new Exception("No hay sesión activa");
    }

    $usuarioId = $_SESSION['usuario_id'];

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Consulta para obtener datos del administrador, incluyendo apellido_paterno y apellido_materno
        $query = "SELECT a.admin_id, a.nombre, a.apellido_paterno, a.apellido_materno, a.telefono, u.correo 
                  FROM administradores a
                  JOIN usuarios u ON a.usuario_id = u.usuario_id
                  WHERE u.usuario_id = :usuarioId";
        
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':usuarioId', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();
        
        $adminData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$adminData) {
            throw new Exception("Administrador no encontrado");
        }

        echo json_encode([
            'success' => true,
            'data' => $adminData,
            'message' => 'Datos obtenidos correctamente'
        ]);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Procesar actualización de datos
        $data = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Datos JSON inválidos");
        }

        // Iniciar transacción
        $pdo->beginTransaction();

        try {
            // 1. Actualizar datos en tabla administradores
            $queryAdmin = "UPDATE administradores 
                           SET nombre = :nombre, apellido_paterno = :apellido_paterno, apellido_materno = :apellido_materno, telefono = :telefono
                           WHERE usuario_id = :usuarioId";
            
            $stmtAdmin = $pdo->prepare($queryAdmin);
            $stmtAdmin->execute([
                ':nombre' => $data['nombre'],
                ':apellido_paterno' => $data['apellido_paterno'],
                ':apellido_materno' => $data['apellido_materno'],
                ':telefono' => $data['telefono'],
                ':usuarioId' => $usuarioId
            ]);

            // 2. Actualizar correo en tabla usuarios
            $queryUsuario = "UPDATE usuarios 
                             SET correo = :correo
                             WHERE usuario_id = :usuarioId";
            
            $stmtUsuario = $pdo->prepare($queryUsuario);
            $stmtUsuario->execute([
                ':correo' => $data['correo'],
                ':usuarioId' => $usuarioId
            ]);

            // 3. Si se proporcionó nueva contraseña, actualizarla
            if (!empty($data['newPassword'])) {
                if ($data['newPassword'] !== $data['confirmPassword']) {
                    throw new Exception("Las contraseñas no coinciden");
                }

                $hashedPassword = password_hash($data['newPassword'], PASSWORD_DEFAULT);
                
                $queryPassword = "UPDATE usuarios 
                                  SET contrasena = :contrasena
                                  WHERE usuario_id = :usuarioId";
                
                $stmtPassword = $pdo->prepare($queryPassword);
                $stmtPassword->execute([
                    ':contrasena' => $hashedPassword,
                    ':usuarioId' => $usuarioId
                ]);
            }

            $pdo->commit();

            echo json_encode([
                'success' => true,
                'message' => 'Datos actualizados correctamente'
            ]);

        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error en la base de datos',
        'error' => $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
<?php
header('Content-Type: application/json');
require_once '../conexion.php'; 

// --- IMPORTANTE: SEGURIDAD ---
// Descomenta las siguientes líneas para proteger este endpoint.
// Esto asegura que solo administradores autenticados puedan usarlo.
/*
require_once '../verificar_sesion.php'; 
if (!isset($rol) || $rol !== 'admin') {
    http_response_code(403); // Forbidden
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado.']);
    exit();
}
*/

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $stmt = $conn->query("SELECT periodo_id, nombre, fecha_inicio, fecha_fin, estado FROM periodos_registro ORDER BY fecha_inicio DESC");
        $periodos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'periodos' => $periodos]);
        
    } elseif ($method === 'POST') {
        $action = $_POST['action'] ?? '';
        
        $conn->beginTransaction();
        
        switch ($action) {
            case 'crear':
                if (empty($_POST['nombre']) || empty($_POST['fecha_inicio']) || empty($_POST['fecha_fin'])) {
                    throw new Exception('Todos los campos son obligatorios.');
                }
                $stmt = $conn->prepare("INSERT INTO periodos_registro (nombre, fecha_inicio, fecha_fin, estado) VALUES (?, ?, ?, 'inactivo')");
                $stmt->execute([$_POST['nombre'], $_POST['fecha_inicio'], $_POST['fecha_fin']]);
                break;

            case 'activar':
                $periodo_id = filter_var($_POST['periodo_id'], FILTER_VALIDATE_INT);
                if (!$periodo_id) throw new Exception('ID de período no válido.');

                $conn->exec("UPDATE periodos_registro SET estado = 'inactivo'");
                $stmt = $conn->prepare("UPDATE periodos_registro SET estado = 'activo' WHERE periodo_id = ?");
                $stmt->execute([$periodo_id]);
                break;

            case 'desactivar':
                $periodo_id = filter_var($_POST['periodo_id'], FILTER_VALIDATE_INT);
                if (!$periodo_id) throw new Exception('ID de período no válido.');

                $stmt = $conn->prepare("UPDATE periodos_registro SET estado = 'inactivo' WHERE periodo_id = ?");
                $stmt->execute([$periodo_id]);
                break;
            
            default:
                throw new Exception('Acción no válida o no especificada.');
        }

        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Operación realizada con éxito.']);

    } else {
        http_response_code(405); // Method Not Allowed
        throw new Exception('Método no permitido.');
    }
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
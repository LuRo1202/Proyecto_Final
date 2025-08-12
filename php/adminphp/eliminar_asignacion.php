<?php
// --- CABECERAS CORS ---
header("Access-Control-Allow-Origin: *"); // Permite solicitudes desde cualquier origen (ajustar en producción)
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, DELETE"); // Métodos HTTP permitidos
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Cabeceras permitidas en las solicitudes
header("Access-Control-Allow-Credentials: true"); // Necesario si usas sesiones/cookies

// Manejar solicitudes OPTIONS (pre-flight requests)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

header('Content-Type: application/json');
session_start(); // Inicia la sesión para verificación de usuario

// --- Autenticación y Autorización ---
// Si no hay sesión activa o el rol no es admin, denegar acceso
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    http_response_code(403); // Forbidden
    echo json_encode(['success' => false, 'message' => 'Acceso denegado. Permisos insuficientes.']);
    exit();
}

require_once __DIR__ . '/../conexion.php'; // Asegúrate de que $conn sea un objeto PDO

$response = ['success' => false, 'message' => ''];

$data = json_decode(file_get_contents('php://input'), true);

// Validar que el ID de asignación fue proporcionado y es numérico
if (!isset($data['asignacion_id']) || !is_numeric($data['asignacion_id'])) {
    http_response_code(400); // Bad Request
    $response['message'] = "ID de asignación no proporcionado o inválido.";
    error_log("eliminar_asignacion.php (Error 400): ID de asignación inválido o faltante. Datos recibidos: " . print_r($data, true));
    echo json_encode($response);
    exit;
}

$asignacion_id = $data['asignacion_id'];

// Registra en el log el ID de asignación que se va a intentar eliminar
error_log("eliminar_asignacion.php: Intentando eliminar asignación con ID: " . $asignacion_id);

try {
    // Define la consulta SQL para eliminar un registro de la tabla 'estudiantes_responsables'
    // Se usa 'id = ?' porque 'id' es la clave primaria de esa tabla.
    $query = "DELETE FROM estudiantes_responsables WHERE id = ?";
    
    // Prepara la consulta SQL para su ejecución segura (prevención de inyección SQL)
    // '$conn' es la conexión PDO que debe venir de 'conexion.php'
    $stmt = $conn->prepare($query);
    
    // Verifica si la preparación de la consulta falló
    if ($stmt === false) {
        throw new Exception("Falló la preparación de la consulta: " . implode(" ", $conn->errorInfo()));
    }
    
    // Ejecuta la consulta, pasando el ID de asignación como parámetro
    $stmt->execute([$asignacion_id]);
    
    // 'rowCount()' devuelve el número de filas afectadas por la última sentencia SQL
    if ($stmt->rowCount() > 0) {
        // Si se afectó al menos una fila, la eliminación fue exitosa
        $response['success'] = true;
        $response['message'] = "Asignación eliminada correctamente.";
        // Registra el éxito en el log
        error_log("eliminar_asignacion.php: Asignación ID " . $asignacion_id . " eliminada correctamente.");
    } else {
        // Si no se afectaron filas, la asignación no se encontró o ya estaba eliminada
        http_response_code(404); // Not Found
        $response['message'] = "No se encontró la asignación con ID: " . $asignacion_id . " o ya fue eliminada.";
        // Registra la advertencia en el log
        error_log("eliminar_asignacion.php (Advertencia 404): No se encontró asignación con ID " . $asignacion_id . " para eliminar.");
    }
} catch (PDOException $e) {
    // Captura excepciones específicas de PDO (errores de la base de datos)
    http_response_code(500); // Internal Server Error
    $response['message'] = "Error en la base de datos: " . $e->getMessage();
    // Registra el error detallado de la base de datos en el log
    error_log("eliminar_asignacion.php (Error PDO): Para ID " . $asignacion_id . ": " . $e->getMessage());
} catch (Exception $e) {
    // Captura otras excepciones generales (ej. si la preparación falló)
    http_response_code(500); // Internal Server Error
    $response['message'] = "Error interno del servidor: " . $e->getMessage();
    error_log("eliminar_asignacion.php (Error General): Para ID " . $asignacion_id . ": " . $e->getMessage());
}

// Devuelve la respuesta final en formato JSON
echo json_encode($response);
?>
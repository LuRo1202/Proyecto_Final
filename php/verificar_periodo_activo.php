<?php
// Evitar el caché del navegador para esta respuesta específica
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// Indicar que la respuesta es JSON
header('Content-Type: application/json; charset=utf-8');

// --- Configuración de la base de datos ---
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "servicio_social";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    // Enviar un error 500 para que el 'catch' en JS pueda manejarlo si es necesario
    http_response_code(500); 
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos.']);
    exit();
}

$conn->set_charset('utf8mb4');

try {
    // Consulta para ver si existe un período con estado 'activo' y cuya fecha actual esté dentro del rango
    $sql = "SELECT 1 FROM periodos_registro 
            WHERE estado = 'activo' AND CURDATE() BETWEEN fecha_inicio AND fecha_fin 
            LIMIT 1";
    
    $result = $conn->query($sql);
    
    // Si la consulta devuelve al menos una fila, significa que hay un período activo.
    $is_active = ($result && $result->num_rows > 0);

    echo json_encode(['success' => true, 'activo' => $is_active]);

} catch (Exception $e) {
    http_response_code(500);
    error_log("Error en verificar_periodo_activo.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al consultar la base de datos.']);
}

$conn->close();
?>
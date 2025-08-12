<?php
header('Content-Type: application/json; charset=utf-8');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "servicio_social";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    error_log("Error de conexión DB para get_periodos: " . $conn->connect_error);
    http_response_code(500);
    echo json_encode([]); // Devolver un array vacío en caso de error
    exit();
}
$conn->set_charset('utf8mb4');

// CORRECCIÓN: La consulta ahora filtra solo los períodos activos y vigentes
$sql = "SELECT periodo_id, nombre, estado FROM periodos_registro 
        WHERE estado = 'activo' AND CURDATE() BETWEEN fecha_inicio AND fecha_fin 
        ORDER BY fecha_inicio DESC";

$result = $conn->query($sql);

$periodos = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $periodos[] = $row;
    }
}

$conn->close();

echo json_encode($periodos);
?>
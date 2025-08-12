<?php
header('Content-Type: application/json');
$conn = new mysqli('127.0.0.1', 'root', '', 'servicio_social');
if ($conn->connect_error) {
    die("[]");
}
$conn->set_charset('utf8');

$result = $conn->query("SELECT periodo_id, nombre FROM periodos_registro ORDER BY fecha_inicio DESC");
$periodos = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode($periodos);
$conn->close();
?>
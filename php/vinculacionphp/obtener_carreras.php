<?php
header('Content-Type: application/json');
$config = ['host' => '127.0.0.1', 'user' => 'root', 'pass' => '', 'db' => 'servicio_social'];
$conn = new mysqli($config['host'], $config['user'], $config['pass'], $config['db']);
if ($conn->connect_error) { die("[]"); }
$conn->set_charset('utf8');

$result = $conn->query("SELECT DISTINCT carrera FROM estudiantes WHERE carrera IS NOT NULL AND carrera != '' ORDER BY carrera ASC");
$carreras = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode($carreras);
$conn->close();
?>
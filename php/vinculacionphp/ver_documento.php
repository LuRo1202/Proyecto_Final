<?php
session_start();
error_reporting(0); // Desactivar errores en producción para no romper la salida del archivo

// --- Verificación de Sesión ---
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(403);
    die('Acceso denegado. Debes iniciar sesión.');
}

// --- Validar que se recibió un ID ---
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    die('ID de documento no válido.');
}

$documento_id = intval($_GET['id']);

// --- Conexión a la Base de Datos ---
$conn = new mysqli('127.0.0.1', 'root', '', 'servicio_social');
if ($conn->connect_error) {
    http_response_code(500);
    die('Error de conexión a la base de datos.');
}
$conn->set_charset('utf8');

// --- Buscar la información del documento en la BD ---
$query = "SELECT ruta_archivo, tipo_archivo, nombre_archivo FROM documentos_servicio WHERE documento_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $documento_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    die('Documento no encontrado en la base de datos.');
}

$documento = $result->fetch_assoc();
$ruta_relativa_db = $documento['ruta_archivo'];
$tipo_mime = $documento['tipo_archivo'];
$nombre_original = $documento['nombre_archivo'];

$stmt->close();
$conn->close();

// --- LÓGICA DE RUTA CORREGIDA ---
// Se asume que la ruta en la BD es algo como '../../uploads/documentos_servicio/archivo.pdf'
// y que fue creada desde un script dentro de la carpeta /php/
// Este script (ver_documento.php) está en /php/vinculacionphp/, por lo que la raíz del proyecto está 2 niveles arriba.
$project_root = dirname(__DIR__, 2);

// Limpiamos la parte relativa ('../../') de la ruta de la BD para obtener una ruta desde la raíz del proyecto.
$clean_path = str_replace('../../', '', $ruta_relativa_db);

// Construimos la ruta absoluta y la verificamos con realpath() para seguridad.
$ruta_absoluta = $project_root . '/' . $clean_path;
$ruta_real = realpath($ruta_absoluta);


if ($ruta_real === false || !file_exists($ruta_real)) {
    http_response_code(404);
    die('El archivo físico no se encuentra en el servidor. Ruta calculada: ' . htmlspecialchars($ruta_absoluta));
}

// --- Servir el archivo al navegador ---
header('Content-Type: ' . $tipo_mime);
header('Content-Disposition: inline; filename="' . basename($nombre_original) . '"');
header('Content-Length: ' . filesize($ruta_real));
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');
ob_clean();
flush();
readfile($ruta_real);
exit;
?>
<?php
session_start(); // Inicia la sesión PHP

// --- CABECERAS CORS ---
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

// Manejar solicitudes OPTIONS (pre-flight requests)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

header('Content-Type: application/json'); // Asegura que la respuesta sea JSON

// Configurar cabeceras para evitar caché
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Verifica si la sesión tiene los datos esenciales
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['rol']) || !isset($_SESSION['nombre_completo']) || !isset($_SESSION['correo'])) {
    // CAMBIO AQUÍ: 'activa' -> 'success'
    echo json_encode(['success' => false, 'message' => 'Sesión no activa o incompleta.']);
    exit;
}

// Si la sesión está activa y tiene los datos mínimos, devuelve la información
// CAMBIO AQUÍ: 'activa' -> 'success'
echo json_encode([
    'success' => true, 
    'rol' => $_SESSION['rol'],
    'usuario_id' => $_SESSION['usuario_id'],
    'responsable_id' => $_SESSION['responsable_id'] ?? null,
    'admin_id' => $_SESSION['admin_id'] ?? null,
    'estudiante_id' => $_SESSION['estudiante_id'] ?? null,
    'vinculacion_id' => $_SESSION['vinculacion_id'] ?? null,
    'correo' => $_SESSION['correo'], 
    'nombre_completo' => $_SESSION['nombre_completo'] 
]);
?>
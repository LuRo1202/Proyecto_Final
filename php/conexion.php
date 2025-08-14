<?php
// Configuración para XAMPP (ajusta según tu entorno)
define('DB_HOST', 'localhost');
define('DB_NAME', 'servicio_social_');
define('DB_USER', 'root');
define('DB_PASS', '');

// Configuración de errores (solo mostrar en desarrollo)
define('ENVIRONMENT', 'development'); // Cambiar a 'production' en entorno real

if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Conexión PDO con manejo de errores
try {
    $conn = new PDO(
        "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4",
        DB_USER, 
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => false
        ]
    );
    
    // Opcional: Establecer zona horaria si es necesario
    $conn->exec("SET time_zone = '-06:00';"); // Ejemplo para hora central México
    
} catch (PDOException $e) {
    // Log del error
    error_log("[" . date('Y-m-d H:i:s') . "] Error de conexión: " . $e->getMessage() . "\n", 3, __DIR__.'/error.log');
    
    // Mensaje amigable
    if (ENVIRONMENT === 'development') {
        die("Error de conexión: " . $e->getMessage());
    } else {
        die("Error al conectar con la base de datos. Por favor intente más tarde.");
    }
}

// Función para sanitizar entradas (opcional pero recomendada)
function sanitizar($data) {
    if (is_array($data)) {
        return array_map('sanitizar', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}
?>
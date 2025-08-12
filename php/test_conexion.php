<?php
// --- MISMOS DATOS DE TU CONEXIÓN ---
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "servicio_social";

echo "Intentando conectar a la base de datos: '$db_name' ...<br>";

try {
    // Intentamos crear la conexión PDO
    $conn = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    
    // Configurar PDO para que reporte errores
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Si llegamos aquí, la conexión fue exitosa
    echo "<h2 style='color: green;'>¡Conexión Exitosa!</h2>";
    echo "La configuración de tu archivo conexion.php es correcta.";

} catch (PDOException $e) {
    // Si la conexión falla, se captura el error y se muestra
    echo "<h2 style='color: red;'>¡Falló la Conexión!</h2>";
    echo "<strong>Mensaje de error exacto:</strong><br>";
    echo "<pre style='background-color: #eee; border: 1px solid #ccc; padding: 10px;'>" . $e->getMessage() . "</pre>";
    echo "<br><strong>Sugerencia:</strong> Revisa que el servicio de MySQL esté corriendo en XAMPP y que el nombre de la base de datos, usuario y contraseña sean correctos.";
}
?>
<?php
header('Content-Type: application/json');

// --- Configuración de la base de datos ---
$servername = "localhost";
$username = "root";     // ¡CUIDADO! En producción, no uses 'root' sin contraseña.
$password = "";         // Tu contraseña de MySQL/MariaDB
$dbname = "servicio_social"; // El nombre de tu base de datos

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    // Registra el error de conexión en los logs del servidor (no lo expongas al cliente)
    error_log("Error de conexión a la base de datos: " . $conn->connect_error);
    // Envia una respuesta genérica al cliente
    // CAMBIO: Si hay un error de DB, lo tratamos como "existe" para no dar pistas al atacante
    // y para que el usuario no pueda seguir si hay un problema con la validación.
    echo json_encode(['exists' => true, 'message' => 'Error en la verificación del correo. Intenta más tarde.']);
    exit();
}

// Obtener los datos JSON de la solicitud POST
$input = file_get_contents('php://input');
$data = json_decode($input, true); // true para obtener un array asociativo

$response = ['exists' => false];

// Verificar si el correo fue enviado en la solicitud
if (isset($data['correo'])) {
    $correo = $conn->real_escape_string($data['correo']); // Sanitizar el correo

    // Usar sentencia preparada para evitar inyecciones SQL
    $sql = "SELECT COUNT(*) AS count FROM usuarios WHERE correo = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        error_log("Error al preparar la consulta: " . $conn->error);
        // CAMBIO: Mismo tratamiento para errores de preparación
        echo json_encode(['exists' => true, 'message' => 'Error interno del servidor.']);
        $conn->close();
        exit();
    }

    $stmt->bind_param("s", $correo); // 's' indica que el parámetro es un string
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['count'] > 0) {
        $response['exists'] = true; // El correo ya existe
    }

    $stmt->close();
} else {
    // Si no se proporcionó el correo en la solicitud, es un error del cliente o una solicitud mal formada
    // CAMBIO: Mismo tratamiento para solicitudes mal formadas
    $response = ['exists' => true, 'message' => 'Correo no proporcionado en la solicitud.'];
}

$conn->close();

echo json_encode($response);
?>
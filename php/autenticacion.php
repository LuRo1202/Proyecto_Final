<?php
session_start(); // Siempre al inicio para manejar sesiones

// Configuración de la base de datos
$db_host = "localhost";
$db_user = "root";
$db_pass = ""; // Tu contraseña de la base de datos
$db_name = "servicio_social";

// --- CABECERAS CORS (Asegura la comunicación entre frontend y backend) ---
header("Access-Control-Allow-Origin: *"); // Permite solicitudes desde cualquier origen (AJUSTAR EN PRODUCCIÓN)
header("Access-Control-Allow-Methods: GET, POST, OPTIONS"); // Métodos HTTP permitidos
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Cabeceras permitidas en las solicitudes
header("Access-Control-Allow-Credentials: true"); // Necesario si usas sesiones/cookies

// Manejar solicitudes OPTIONS (pre-flight requests)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Establecer el tipo de contenido para la respuesta
header('Content-Type: application/json');

try {
    // Conexión a la base de datos MySQLi
    $conexion = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    if ($conexion->connect_error) {
        // En caso de error de conexión, registrarlo y lanzar una excepción.
        error_log("Error de conexión a la base de datos: " . $conexion->connect_error);
        throw new Exception("Error al conectar con la base de datos. Por favor, inténtalo más tarde.");
    }
    
    // Recibir y sanitizar datos del POST
    $correo = $conexion->real_escape_string($_POST['correo'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';
    
    if (empty($correo) || empty($contrasena)) {
        throw new Exception("Correo y contraseña son requeridos.");
    }
    
    // Consulta MEJORADA para obtener todos los datos necesarios, incluyendo el nombre completo
    // Se unen todas las tablas de usuarios (estudiantes, responsables, administradores, vinculacion)
    // usando LEFT JOIN para obtener el nombre y los IDs específicos del rol.
    $sql = "SELECT 
                u.usuario_id, 
                u.correo, 
                u.contrasena, 
                u.rol_id, 
                u.activo,
                u.tipo_usuario,
                r.nombre_rol,
                e.estudiante_id,
                res.responsable_id,
                a.admin_id,
                v.vinculacion_id,
                COALESCE(e.nombre, res.nombre, a.nombre, v.nombre) AS nombre,
                COALESCE(e.apellido_paterno, res.apellido_paterno, a.apellido_paterno, v.apellido_paterno) AS apellido_paterno,
                COALESCE(e.apellido_materno, res.apellido_materno, a.apellido_materno, v.apellido_materno) AS apellido_materno
            FROM usuarios u
            JOIN roles r ON u.rol_id = r.rol_id
            LEFT JOIN estudiantes e ON u.usuario_id = e.usuario_id
            LEFT JOIN responsables res ON u.usuario_id = res.usuario_id
            LEFT JOIN administradores a ON u.usuario_id = a.usuario_id
            LEFT JOIN vinculacion v ON u.usuario_id = v.usuario_id
            WHERE u.correo = ? AND u.activo = 1";
    
    $stmt = $conexion->prepare($sql);
    if (!$stmt) {
        // Si la preparación falla, registrar el error y lanzar una excepción.
        error_log("Error en la preparación de la consulta SQL: " . $conexion->error);
        throw new Exception("Error interno del servidor. Inténtalo más tarde.");
    }
    
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows !== 1) {
        throw new Exception("Correo o contraseña incorrectos."); // Mensaje genérico por seguridad
    }
    
    $usuario = $resultado->fetch_assoc();
    
    // Verificación de contraseña
    // Prioriza password_verify para contraseñas hasheadas (recomendado)
    // Incluye una verificación temporal para administradores con contraseña en texto plano (DEBE ELIMINARSE EN PRODUCCIÓN)
    $contrasena_valida = false;
    
    if (password_verify($contrasena, $usuario['contrasena'])) {
        $contrasena_valida = true;
    } elseif ($usuario['nombre_rol'] === 'admin' && $contrasena === $usuario['contrasena']) { 
        $contrasena_valida = true;
        // Si un admin inicia sesión con una contraseña sin hash, la hashea y actualiza.
        $nuevo_hash = password_hash($contrasena, PASSWORD_DEFAULT);
        $update_sql = "UPDATE usuarios SET contrasena = ? WHERE usuario_id = ?";
        $update_stmt = $conexion->prepare($update_sql);
        if ($update_stmt) {
            $update_stmt->bind_param("si", $nuevo_hash, $usuario['usuario_id']);
            $update_stmt->execute();
            $update_stmt->close();
        } else {
            error_log("Error al preparar la actualización de contraseña: " . $conexion->error);
        }
    }
    
    if (!$contrasena_valida) {
        throw new Exception("Correo o contraseña incorrectos."); // Mensaje genérico por seguridad
    }
    
    // --- Configurar Sesión con TODOS los datos necesarios ---
    $_SESSION['usuario_id'] = $usuario['usuario_id'];
    $_SESSION['correo'] = $usuario['correo'];
    $_SESSION['rol'] = $usuario['nombre_rol'];
    $_SESSION['tipo_usuario'] = $usuario['tipo_usuario'];
    
    // Construir y guardar el nombre completo en la sesión
    $nombre_completo = trim(
        ($usuario['nombre'] ?? '') . ' ' . 
        ($usuario['apellido_paterno'] ?? '') . ' ' . 
        ($usuario['apellido_materno'] ?? '')
    );
    $_SESSION['nombre_completo'] = $nombre_completo;
    
    // Configurar redirección y datos específicos según el rol
    $redirect = '';
    switch ($usuario['nombre_rol']) {
        case 'admin':
            $_SESSION['admin_id'] = $usuario['admin_id'];
            $redirect = "../admin/admin.html";
            break;
            
        case 'encargado':
            $_SESSION['responsable_id'] = $usuario['responsable_id'];
            $redirect = "../encargado/encargado.html";
            break;
            
        case 'estudiante':
            $_SESSION['estudiante_id'] = $usuario['estudiante_id'];
            $redirect = "../estudiante/estudiante.html";
            break;
            
        case 'vinculacion': // ¡Nuevo rol de Vinculación! 🚀
            $_SESSION['vinculacion_id'] = $usuario['vinculacion_id']; // Guarda el ID específico de vinculación
            $redirect = "../vinculacion/vinculacion.html"; // Redirige a la página de vinculación
            break;
            
        default:
            // Si el rol no está mapeado, se considera un error.
            throw new Exception("Rol de usuario no reconocido. Contacta al administrador.");
    }
    
    // Actualizar la fecha del último login del usuario
    $update_sql = "UPDATE usuarios SET ultimo_login = NOW() WHERE usuario_id = ?";
    $update_stmt = $conexion->prepare($update_sql);
    if ($update_stmt) {
        $update_stmt->bind_param("i", $usuario['usuario_id']);
        $update_stmt->execute();
        $update_stmt->close();
    } else {
        error_log("Error al preparar la actualización de ultimo_login: " . $conexion->error);
    }
    
    // Redireccionar al usuario a su dashboard.
    header("Location: " . $redirect);
    exit();
    
} catch (Exception $e) {
    // Capturar cualquier excepción lanzada durante el proceso
    error_log("Error de autenticación: " . $e->getMessage());
    
    // Redirigir al `index.html` con un mensaje de error codificado para la URL.
    header("Location: ../index.html?error=" . urlencode($e->getMessage()));
    exit();
} finally {
    // Asegurarse de cerrar la conexión a la base de datos si está abierta.
    if (isset($conexion)) {
        $conexion->close();
    }
}
?>
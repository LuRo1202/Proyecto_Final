<?php
session_start();

header('Content-Type: application/json');

// Verificar autenticación
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'estudiante') {
    echo json_encode(['error' => 'Acceso denegado. Por favor, inicia sesión.']);
    exit();
}

// Configuración de la base de datos
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "servicio_social";

// Conexión a la base de datos
try {
    $conexion = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    if ($conexion->connect_error) {
        throw new Exception("Error de conexión a la base de datos: " . $conexion->connect_error);
    }
    
    // Obtener acción solicitada
    $accion = $_GET['accion'] ?? $_POST['accion'] ?? '';
    
    switch ($accion) {
        case 'obtenerDatos':
            obtenerDatosEstudiante($conexion);
            break;
        case 'registrarEntrada':
            registrarEntrada($conexion);
            break;
        case 'registrarSalida':
            registrarSalida($conexion);
            break;
        default:
            echo json_encode(['error' => 'Acción no válida.']);
            break;
    }
    
} catch (Exception $e) {
    error_log("Error en el script PHP: " . $e->getMessage()); // Log the error
    echo json_encode(['error' => 'Ha ocurrido un error en el servidor: ' . $e->getMessage()]);
} finally {
    if (isset($conexion)) {
        $conexion->close();
    }
}

/**
 * Obtiene la información del estudiante, su responsable y sus registros de horas.
 * @param mysqli $conexion Objeto de conexión a la base de datos.
 */
function obtenerDatosEstudiante($conexion) {
    $usuario_id = $_SESSION['usuario_id'];
    
    // Obtener información del estudiante
    // Aseguramos que se seleccionen 'nombre', 'apellido_paterno', 'apellido_materno'
    $sql_estudiante = "SELECT e.estudiante_id, e.matricula, e.nombre, e.apellido_paterno, e.apellido_materno, 
                              e.carrera, e.cuatrimestre, e.horas_requeridas, e.horas_completadas,
                              u.correo 
                       FROM estudiantes e 
                       JOIN usuarios u ON e.usuario_id = u.usuario_id 
                       WHERE e.usuario_id = ?";
    $stmt_estudiante = $conexion->prepare($sql_estudiante);
    if (!$stmt_estudiante) {
        throw new Exception("Error al preparar la consulta de estudiante: " . $conexion->error);
    }
    $stmt_estudiante->bind_param("i", $usuario_id);
    $stmt_estudiante->execute();
    $resultado_estudiante = $stmt_estudiante->get_result();
    $estudiante = $resultado_estudiante->fetch_assoc();

    if (!$estudiante) {
        echo json_encode(['error' => 'No se encontró información para el estudiante.']);
        exit();
    }

    // Obtener responsable asignado
    $responsable = null;
    // Aseguramos que se seleccionen 'nombre', 'apellido_paterno', 'apellido_materno' para responsables
    $sql_responsable = "SELECT r.responsable_id, r.nombre, r.apellido_paterno, r.apellido_materno 
                        FROM estudiantes_responsables er
                        JOIN responsables r ON er.responsable_id = r.responsable_id
                        WHERE er.estudiante_id = ?";
    $stmt_responsable = $conexion->prepare($sql_responsable);
    if (!$stmt_responsable) {
        throw new Exception("Error al preparar la consulta de responsable: " . $conexion->error);
    }
    $stmt_responsable->bind_param("i", $estudiante['estudiante_id']);
    $stmt_responsable->execute();
    $responsable = $stmt_responsable->get_result()->fetch_assoc();

    // Obtener registros de horas
    // La tabla es 'registroshoras' y los campos son 'registro_id', 'fecha', 'hora_entrada', 'hora_salida', 'horas_acumuladas', 'estado'
    $sql_registros = "SELECT registro_id, fecha, hora_entrada, hora_salida, horas_acumuladas, estado 
                      FROM registroshoras 
                      WHERE estudiante_id = ? 
                      ORDER BY fecha DESC, hora_entrada DESC";
    $stmt_registros = $conexion->prepare($sql_registros);
    if (!$stmt_registros) {
        throw new Exception("Error al preparar la consulta de registros: " . $conexion->error);
    }
    $stmt_registros->bind_param("i", $estudiante['estudiante_id']);
    $stmt_registros->execute();
    $registros = $stmt_registros->get_result()->fetch_all(MYSQLI_ASSOC);

    echo json_encode([
        'estudiante' => $estudiante,
        'responsable' => $responsable,
        'registros' => $registros
    ]);
}

/**
 * Registra la hora de entrada del estudiante.
 * @param mysqli $conexion Objeto de conexión a la base de datos.
 */
function registrarEntrada($conexion) {
    $usuario_id = $_SESSION['usuario_id'];
    
    // Recibir la fecha y hora desde el cliente
    $fecha_entrada_cliente = $_POST['fecha_entrada'] ?? null;
    $hora_entrada_cliente = $_POST['hora_entrada'] ?? null;

    if (!$fecha_entrada_cliente || !$hora_entrada_cliente) {
        echo json_encode(['error' => 'Fecha y/o hora de entrada no proporcionadas.']);
        exit();
    }

    // Formatear la fecha y hora para la base de datos (DATE y DATETIME)
    $fecha_db = $fecha_entrada_cliente; // 'YYYY-MM-DD'
    $hora_entrada_db = $fecha_entrada_cliente . ' ' . $hora_entrada_cliente; // 'YYYY-MM-DD HH:MM:SS'
    
    // Obtener ID de estudiante
    $sql_estudiante_id = "SELECT estudiante_id FROM estudiantes WHERE usuario_id = ?";
    $stmt_estudiante_id = $conexion->prepare($sql_estudiante_id);
    $stmt_estudiante_id->bind_param("i", $usuario_id);
    $stmt_estudiante_id->execute();
    $resultado_estudiante_id = $stmt_estudiante_id->get_result();
    $estudiante = $resultado_estudiante_id->fetch_assoc();
    $estudiante_id = $estudiante['estudiante_id']; // Usamos 'estudiante_id' como en tu DB
    
    // Obtener responsable asignado
    $sql_responsable = "SELECT responsable_id FROM estudiantes_responsables 
                        WHERE estudiante_id = ? LIMIT 1"; // Usamos 'estudiante_id', 'responsable_id'
    $stmt_responsable = $conexion->prepare($sql_responsable);
    $stmt_responsable->bind_param("i", $estudiante_id);
    $stmt_responsable->execute();
    $responsable_result = $stmt_responsable->get_result();
    
    if ($responsable_result->num_rows === 0) {
        echo json_encode(['error' => 'No tienes un encargado asignado. Contacta al administrador.']);
        exit();
    }
    
    $responsable = $responsable_result->fetch_assoc();
    $responsable_id = $responsable['responsable_id']; // Usamos 'responsable_id'
    
    // Verificar si ya hay un registro de entrada sin cerrar para hoy
    // La tabla es 'registroshoras' y el campo es 'estado'
    $sql_check_entry = "SELECT * FROM registroshoras 
                        WHERE estudiante_id = ? AND fecha = ? AND hora_salida IS NULL";
    $stmt_check_entry = $conexion->prepare($sql_check_entry);
    $stmt_check_entry->bind_param("is", $estudiante_id, $fecha_db);
    $stmt_check_entry->execute();
    $resultado_check_entry = $stmt_check_entry->get_result();
    
    if ($resultado_check_entry->num_rows > 0) {
        echo json_encode(['error' => 'Ya tienes un registro de entrada pendiente por cerrar hoy.']);
        exit();
    }
    
    // Insertar nuevo registro con la fecha y hora del cliente
    // Columnas: 'estudiante_id', 'responsable_id', 'fecha', 'hora_entrada', 'estado'
    $insert_sql = "INSERT INTO registroshoras 
                   (estudiante_id, responsable_id, fecha, hora_entrada, estado) 
                   VALUES (?, ?, ?, ?, 'pendiente')";
    $insert_stmt = $conexion->prepare($insert_sql);
    $insert_stmt->bind_param("iiss", $estudiante_id, $responsable_id, $fecha_db, $hora_entrada_db); 
    
    if ($insert_stmt->execute()) {
        echo json_encode(['mensaje' => 'Entrada registrada correctamente a las ' . date('H:i:s', strtotime($hora_entrada_db)) . '.']);
    } else {
        throw new Exception('Error al registrar la entrada: ' . $conexion->error);
    }
}

/**
 * Registra la hora de salida del estudiante y calcula las horas acumuladas.
 * @param mysqli $conexion Objeto de conexión a la base de datos.
 */
function registrarSalida($conexion) {
    $usuario_id = $_SESSION['usuario_id'];
    
    // Recibir la hora de salida desde el cliente
    $hora_salida_cliente = $_POST['hora_salida'] ?? null;

    if (!$hora_salida_cliente) {
        echo json_encode(['error' => 'Hora de salida no proporcionada.']);
        exit();
    }
    
    // Obtener ID de estudiante
    $sql_estudiante_id = "SELECT estudiante_id FROM estudiantes WHERE usuario_id = ?";
    $stmt_estudiante_id = $conexion->prepare($sql_estudiante_id);
    $stmt_estudiante_id->bind_param("i", $usuario_id);
    $stmt_estudiante_id->execute();
    $resultado_estudiante_id = $stmt_estudiante_id->get_result();
    $estudiante = $resultado_estudiante_id->fetch_assoc();
    $estudiante_id = $estudiante['estudiante_id']; // Usamos 'estudiante_id'
    
    // Obtener el último registro de entrada pendiente del día actual para cerrar
    // Columnas: 'registro_id', 'fecha', 'hora_entrada' y tabla 'registroshoras'
    $sql_get_pending = "SELECT registro_id, fecha, hora_entrada FROM registroshoras 
                        WHERE estudiante_id = ? AND hora_salida IS NULL 
                        ORDER BY hora_entrada DESC LIMIT 1"; 
    $stmt_get_pending = $conexion->prepare($sql_get_pending);
    if (!$stmt_get_pending) {
        throw new Exception("Error al preparar la consulta para obtener registro pendiente: " . $conexion->error);
    }
    $stmt_get_pending->bind_param("i", $estudiante_id);
    $stmt_get_pending->execute();
    $resultado_get_pending = $stmt_get_pending->get_result();
    
    if ($resultado_get_pending->num_rows === 0) {
        echo json_encode(['error' => 'No hay un registro de entrada pendiente para cerrar.']);
        exit();
    }
    
    $registro_pendiente = $resultado_get_pending->fetch_assoc();
    $registro_id = $registro_pendiente['registro_id']; // Usamos 'registro_id'
    $fecha_registro_entrada = $registro_pendiente['fecha']; // Usamos 'fecha'
    $hora_entrada_completa_db = $registro_pendiente['hora_entrada']; // Usamos 'hora_entrada'

    // Combine the entry date with the client's exit time for calculation
    $hora_salida_completa_db = $fecha_registro_entrada . ' ' . $hora_salida_cliente;

    $timestamp_entrada = strtotime($hora_entrada_completa_db);
    $timestamp_salida = strtotime($hora_salida_completa_db);

    // Validate if exit time is before entry time
    if ($timestamp_salida < $timestamp_entrada) {
        echo json_encode(['error' => 'La hora de salida no puede ser anterior a la hora de entrada del mismo día.']);
        exit();
    }

    $diferencia_segundos = $timestamp_salida - $timestamp_entrada;
    $diferencia_horas_bruta = $diferencia_segundos / 3600; // Total hours elapsed

    // --- Validation and Hour Calculation Logic ---
    $min_horas = 4; // Minimum hours required per entry
    $max_horas_per_entry = 8; // Maximum hours to count for a single entry

    if ($diferencia_horas_bruta < $min_horas) {
        echo json_encode(['error' => 'Deben pasar al menos ' . $min_horas . ' horas desde la entrada. Tiempo registrado: ' . round($diferencia_horas_bruta, 2) . ' horas.']);
        exit();
    }

    // Calculate accumulated hours: Capped at max_horas_per_entry
    $horas_acumuladas = min($diferencia_horas_bruta, $max_horas_per_entry);
    // --- End Validation and Hour Calculation Logic ---
    
    // Update the record with exit time and accumulated hours
    // Columnas: 'hora_salida', 'horas_acumuladas', 'estado' y 'registro_id'
    $update_sql = "UPDATE registroshoras 
                   SET hora_salida = ?, horas_acumuladas = ?, estado = 'aprobado'
                   WHERE registro_id = ?";
    $update_stmt = $conexion->prepare($update_sql);
    if (!$update_stmt) {
        throw new Exception("Error al preparar la consulta de actualización de salida: " . $conexion->error);
    }
    $update_stmt->bind_param("sdi", $hora_salida_completa_db, $horas_acumuladas, $registro_id); 
    
    if ($update_stmt->execute()) {
        // Update total completed hours for the student
        // Columnas: 'horas_completadas' y 'estudiante_id'
        $update_total_hours_sql = "UPDATE estudiantes 
                                   SET horas_completadas = horas_completadas + ? 
                                   WHERE estudiante_id = ?";
        $stmt_total_hours = $conexion->prepare($update_total_hours_sql);
        if (!$stmt_total_hours) {
            throw new Exception("Error al preparar la consulta de actualización de horas totales: " . $conexion->error);
        }
        $stmt_total_hours->bind_param("di", $horas_acumuladas, $estudiante_id);
        $stmt_total_hours->execute();
        
        echo json_encode(['mensaje' => 'Salida registrada. Horas acumuladas para este registro: ' . round($horas_acumuladas, 2) . ' horas.']);
    } else {
        throw new Exception('Error al registrar la salida: ' . $conexion->error);
    }
}
?>
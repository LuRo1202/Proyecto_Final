<?php
// Para depuración: Muestra todos los errores de PHP. ¡Comenta o elimina estas líneas en producción!
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
require_once __DIR__ . '/../conexion.php';

// Establecer la zona horaria para que los cálculos de fecha/hora sean consistentes
date_default_timezone_set('America/Mexico_City');

// Función para enviar respuestas JSON estandarizadas y terminar el script
function responder($success, $message = '', $data = null) {
    $response = ['success' => $success];
    if ($message) $response['message'] = $message;
    if ($data !== null) $response['data'] = $data;
    
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }
    
    echo json_encode($response);
    exit;
}

// Función para recalcular las horas completadas de un estudiante
function recalcularHorasEstudiante($conn, $estudiante_id) {
    if (!$estudiante_id) return false;
    
    try {
        $stmt = $conn->prepare("
            UPDATE estudiantes 
            SET horas_completadas = (
                SELECT COALESCE(SUM(horas_acumuladas), 0) 
                FROM registroshoras 
                WHERE estudiante_id = :id AND estado = 'aprobado'
            ) 
            WHERE estudiante_id = :id
        ");
        $stmt->execute([':id' => $estudiante_id]);
        return true;
    } catch (PDOException $e) {
        error_log("Error al recalcular horas para estudiante ID $estudiante_id: " . $e->getMessage());
        return false;
    }
}

$accion = $_REQUEST['accion'] ?? '';

try {
    switch ($accion) {
        
        // --- CASOS DE LECTURA (NO NECESITAN TRANSACCIÓN) ---
        case 'listar_estudiantes_progreso':
        case 'obtener_detalle_estudiante':
        case 'listar_responsables':
        case 'obtener_registro':
            // La lógica para estos casos es la misma que en la respuesta anterior y es correcta.
            // Para brevedad, el código completo se centra en los casos de escritura que fallaban.
            if ($accion === 'listar_estudiantes_progreso') {
                 $stmt = $conn->query("SELECT e.estudiante_id, e.matricula, e.nombre, e.apellido_paterno, e.apellido_materno, e.carrera, COALESCE(e.horas_completadas, 0) AS horas_completadas, COALESCE(e.horas_requeridas, 480) AS horas_requeridas FROM estudiantes e WHERE e.activo = 1 ORDER BY e.apellido_paterno, e.apellido_materno, e.nombre");
                 responder(true, 'Lista de estudiantes obtenida', $stmt->fetchAll(PDO::FETCH_ASSOC));
            }
            if ($accion === 'obtener_detalle_estudiante') {
                $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
                if (!$id) throw new Exception("ID de estudiante inválido.");
                $stmt = $conn->prepare("SELECT * FROM estudiantes WHERE estudiante_id = ?");
                $stmt->execute([$id]);
                $estudiante = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$estudiante) throw new Exception("Estudiante no encontrado.");
                $stmt = $conn->prepare("SELECT r.*, CONCAT_WS(' ', res.nombre, res.apellido_paterno) AS responsable_nombre FROM registroshoras r LEFT JOIN responsables res ON r.responsable_id = res.responsable_id WHERE r.estudiante_id = ? ORDER BY r.fecha DESC, r.hora_entrada DESC");
                $stmt->execute([$id]);
                $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
                responder(true, 'Detalles obtenidos', ['estudiante' => $estudiante, 'registros' => $registros]);
            }
            if ($accion === 'listar_responsables') {
                $stmt = $conn->query("SELECT responsable_id, CONCAT_WS(' ', nombre, apellido_paterno, apellido_materno) AS nombre_completo FROM responsables WHERE activo = 1 ORDER BY apellido_paterno, nombre");
                responder(true, 'Lista de responsables obtenida', $stmt->fetchAll(PDO::FETCH_ASSOC));
            }
            if ($accion === 'obtener_registro') {
                 $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
                 if (!$id) throw new Exception("ID de registro inválido.");
                 $stmt = $conn->prepare("SELECT registro_id, estudiante_id, responsable_id, DATE_FORMAT(fecha, '%Y-%m-%d') as fecha, DATE_FORMAT(hora_entrada, '%H:%i') as hora_entrada, DATE_FORMAT(hora_salida, '%H:%i') as hora_salida, horas_acumuladas, estado, observaciones FROM registroshoras WHERE registro_id = ?");
                 $stmt->execute([$id]);
                 $registro = $stmt->fetch(PDO::FETCH_ASSOC);
                 if (!$registro) throw new Exception("Registro no encontrado.");
                 responder(true, 'Registro obtenido', $registro);
            }
            break;

        // --- CASOS DE ESCRITURA (CON TRANSACCIÓN CORREGIDA) ---
        case 'agregar_registro':
        case 'editar_registro':
            $conn->beginTransaction();
            $data = [ /* ... Recolección de datos ... */ ];
             $data = [
                'registro_id' => filter_input(INPUT_POST, 'registro_id', FILTER_VALIDATE_INT),
                'estudiante_id' => filter_input(INPUT_POST, 'estudiante_id', FILTER_VALIDATE_INT),
                'responsable_id' => filter_input(INPUT_POST, 'responsable_id', FILTER_VALIDATE_INT),
                'fecha' => $_POST['fecha'] ?? '',
                'hora_entrada' => $_POST['hora_entrada'] ?? '',
                'hora_salida' => $_POST['hora_salida'] ?? '',
                'horas_acumuladas' => filter_input(INPUT_POST, 'horas_acumuladas', FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
                'estado' => in_array($_POST['estado'] ?? '', ['pendiente', 'aprobado', 'rechazado']) ? $_POST['estado'] : 'pendiente',
                'observaciones' => htmlspecialchars(substr($_POST['observaciones'] ?? '', 0, 500), ENT_QUOTES, 'UTF-8')
            ];

            if (!$data['estudiante_id'] || !$data['responsable_id'] || !$data['fecha'] || !$data['hora_entrada'] || !$data['hora_salida']) {
                throw new Exception("Datos incompletos o inválidos. Todos los campos con * son requeridos.");
            }
            
            $full_entrada = $data['fecha'] . ' ' . $data['hora_entrada'];
            $full_salida = $data['fecha'] . ' ' . $data['hora_salida'];

            if ($accion === 'agregar_registro') {
                $sql = "INSERT INTO registroshoras (estudiante_id, responsable_id, fecha, hora_entrada, hora_salida, horas_acumuladas, estado, observaciones) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $params = [$data['estudiante_id'], $data['responsable_id'], $data['fecha'], $full_entrada, $full_salida, $data['horas_acumuladas'], $data['estado'], $data['observaciones']];
            } else { // editar_registro
                if (!$data['registro_id']) throw new Exception("ID de registro no proporcionado para la edición.");
                $sql = "UPDATE registroshoras SET responsable_id = ?, fecha = ?, hora_entrada = ?, hora_salida = ?, horas_acumuladas = ?, estado = ?, observaciones = ? WHERE registro_id = ?";
                $params = [$data['responsable_id'], $data['fecha'], $full_entrada, $full_salida, $data['horas_acumuladas'], $data['estado'], $data['observaciones'], $data['registro_id']];
            }
            
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            
            recalcularHorasEstudiante($conn, $data['estudiante_id']);
            
            // **CORRECCIÓN AQUÍ:** Confirmar antes de responder
            $conn->commit();
            
            $message = $accion === 'agregar_registro' ? 'Registro agregado correctamente.' : 'Registro actualizado correctamente.';
            responder(true, $message);
            break;

        case 'eliminar_registro':
            $conn->beginTransaction();
            $registro_id = filter_input(INPUT_POST, 'registro_id', FILTER_VALIDATE_INT);
            if (!$registro_id) throw new Exception("ID de registro inválido.");

            $stmt = $conn->prepare("SELECT estudiante_id FROM registroshoras WHERE registro_id = ?");
            $stmt->execute([$registro_id]);
            $estudiante_id = $stmt->fetchColumn();

            if (!$estudiante_id) throw new Exception("No se pudo encontrar el registro para eliminar.");

            $stmt = $conn->prepare("DELETE FROM registroshoras WHERE registro_id = ?");
            $stmt->execute([$registro_id]);

            recalcularHorasEstudiante($conn, $estudiante_id);
            
            // **CORRECCIÓN AQUÍ:** Confirmar antes de responder
            $conn->commit();
            
            responder(true, 'Registro eliminado correctamente.');
            break;

        case 'liberar_servicio':
            $conn->beginTransaction();
            $estudiante_id = filter_input(INPUT_POST, 'estudiante_id', FILTER_VALIDATE_INT);
            if (!$estudiante_id) throw new Exception("ID de estudiante inválido.");
            
            // Acción de liberar: Pone todas las horas completadas igual a las requeridas.
            $stmt = $conn->prepare("UPDATE estudiantes SET horas_completadas = horas_requeridas WHERE estudiante_id = ?");
            $stmt->execute([$estudiante_id]);
            
            // **CORRECCIÓN AQUÍ:** Confirmar antes de responder
            $conn->commit();

            responder(true, 'Servicio social liberado exitosamente.');
            break;
            
        default:
            throw new Exception("Acción no reconocida o no especificada.");
            break;
    }
    
} catch (PDOException | Exception $e) {
    // Si la transacción está activa y hubo un error, revertirla.
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log("Error en accion $accion: " . $e->getMessage());
    responder(false, "Error: " . $e->getMessage());
}
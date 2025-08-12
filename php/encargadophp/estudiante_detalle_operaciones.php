<?php
header('Content-Type: application/json');
require_once '../conexion.php';

// Configurar cabeceras CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");

session_start();

// Verificar conexión a la base de datos
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos']);
    exit;
}

$accion = $_GET['accion'] ?? ($_POST['accion'] ?? '');

// Validar acción
$accionesPermitidas = ['obtenerDetalleCompleto', 'listarRegistrosEstudiante', 'validarRegistroEstudiante'];
if (!in_array($accion, $accionesPermitidas)) {
    echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    exit;
}

try {
    switch ($accion) {
        case 'obtenerDetalleCompleto':
            obtenerDetalleCompleto();
            break;
        case 'listarRegistrosEstudiante':
            listarRegistrosEstudiante();
            break;
        case 'validarRegistroEstudiante':
            validarRegistroEstudiante();
            break;
    }
} catch (PDOException $e) {
    error_log('Error en estudiante_detalle_operaciones: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error en el servidor']);
}

function obtenerDetalleCompleto() {
    global $conn;
    
    $estudiante_id = $_GET['estudiante_id'] ?? null;
    
    if (!$estudiante_id || !filter_var($estudiante_id, FILTER_VALIDATE_INT)) {
        echo json_encode(['success' => false, 'message' => 'ID de estudiante no válido']);
        return;
    }
    
    // Verificar que el estudiante pertenezca al encargado
    if (!isset($_SESSION['responsable_id'])) {
        echo json_encode(['success' => false, 'message' => 'Acceso no autorizado']);
        return;
    }
    
    $responsable_id = $_SESSION['responsable_id'];
    
    $query = "SELECT e.*, 
                     CONCAT(r.nombre, ' ', r.apellido_paterno) as responsable_nombre,
                     (SELECT COUNT(*) FROM registroshoras WHERE estudiante_id = e.estudiante_id) as total_registros,
                     (SELECT COUNT(*) FROM registroshoras 
                      WHERE estudiante_id = e.estudiante_id AND estado = 'aprobado') as registros_aprobados
              FROM estudiantes e
              JOIN estudiantes_responsables er ON e.estudiante_id = er.estudiante_id
              LEFT JOIN responsables r ON er.responsable_id = r.responsable_id
              WHERE e.estudiante_id = :estudiante_id
              AND er.responsable_id = :responsable_id
              AND e.activo = 1";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':estudiante_id', $estudiante_id, PDO::PARAM_INT);
    $stmt->bindParam(':responsable_id', $responsable_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $estudiante = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$estudiante) {
        echo json_encode(['success' => false, 'message' => 'Estudiante no encontrado o no autorizado']);
        return;
    }
    
    // Obtener horas completadas (aprobadas)
    $queryHoras = "SELECT COALESCE(SUM(horas_acumuladas), 0) as total 
                   FROM registroshoras 
                   WHERE estudiante_id = :estudiante_id
                   AND estado = 'aprobado'";
    
    $stmtHoras = $conn->prepare($queryHoras);
    $stmtHoras->bindParam(':estudiante_id', $estudiante_id, PDO::PARAM_INT);
    $stmtHoras->execute();
    $horas = $stmtHoras->fetch(PDO::FETCH_ASSOC);
    
    $estudiante['horas_completadas'] = (float)$horas['total'];
    
    echo json_encode(['success' => true, 'data' => $estudiante]);
}

function listarRegistrosEstudiante() {
    global $conn;
    
    $estudiante_id = $_GET['estudiante_id'] ?? null;
    
    if (!$estudiante_id || !filter_var($estudiante_id, FILTER_VALIDATE_INT)) {
        echo json_encode(['success' => false, 'message' => 'ID de estudiante no válido']);
        return;
    }
    
    // Verificar que el estudiante pertenezca al encargado
    if (!isset($_SESSION['responsable_id'])) {
        echo json_encode(['success' => false, 'message' => 'Acceso no autorizado']);
        return;
    }
    
    $responsable_id = $_SESSION['responsable_id'];
    
    $query = "SELECT r.registro_id, r.fecha, 
                     TIME(r.hora_entrada) as hora_entrada, 
                     TIME(r.hora_salida) as hora_salida,
                     r.horas_acumuladas, r.estado,
                     DATE_FORMAT(r.fecha_validacion, '%d/%m/%Y %H:%i') as fecha_validacion,
                     r.observaciones,
                     CONCAT(res.nombre, ' ', res.apellido_paterno) as responsable_validacion
              FROM registroshoras r
              JOIN estudiantes_responsables er ON r.estudiante_id = er.estudiante_id
              LEFT JOIN responsables res ON r.responsable_id = res.responsable_id
              WHERE r.estudiante_id = :estudiante_id
              AND er.responsable_id = :responsable_id
              ORDER BY r.fecha DESC, r.hora_entrada DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':estudiante_id', $estudiante_id, PDO::PARAM_INT);
    $stmt->bindParam(':responsable_id', $responsable_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $registros]);
}

function validarRegistroEstudiante() {
    global $conn;
    
    $registro_id = $_POST['registro_id'] ?? null;
    $estudiante_id = $_POST['estudiante_id'] ?? null;
    $estado = $_POST['estado'] ?? null;
    $observaciones = $_POST['observaciones'] ?? null;
    $responsable_id = $_POST['responsable_id'] ?? null;
    
    if (!$registro_id || !$estado || !$estudiante_id || !$responsable_id) {
        echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
        return;
    }
    
    try {
        $conn->beginTransaction();
        
        // 1. Obtener estado anterior y horas
        $queryAnterior = "SELECT estado, horas_acumuladas 
                          FROM registroshoras 
                          WHERE registro_id = :registro_id
                          AND estudiante_id = :estudiante_id";
        $stmtAnterior = $conn->prepare($queryAnterior);
        $stmtAnterior->bindParam(':registro_id', $registro_id, PDO::PARAM_INT);
        $stmtAnterior->bindParam(':estudiante_id', $estudiante_id, PDO::PARAM_INT);
        $stmtAnterior->execute();
        $registroAnterior = $stmtAnterior->fetch(PDO::FETCH_ASSOC);
        
        if (!$registroAnterior) {
            $conn->rollBack();
            echo json_encode(['success' => false, 'message' => 'Registro no encontrado']);
            return;
        }
        
        // 2. Actualizar registro
        $query = "UPDATE registroshoras 
                  SET estado = :estado, 
                      observaciones = :observaciones,
                      responsable_id = :responsable_id,
                      fecha_validacion = NOW()
                  WHERE registro_id = :registro_id
                  AND estudiante_id = :estudiante_id";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':observaciones', $observaciones);
        $stmt->bindParam(':responsable_id', $responsable_id, PDO::PARAM_INT);
        $stmt->bindParam(':registro_id', $registro_id, PDO::PARAM_INT);
        $stmt->bindParam(':estudiante_id', $estudiante_id, PDO::PARAM_INT);
        $stmt->execute();
        
        // 3. Actualizar horas del estudiante
        if ($registroAnterior['estado'] == 'aprobado') {
            restarHorasEstudiante($estudiante_id, $registroAnterior['horas_acumuladas']);
        }
        
        if ($estado == 'aprobado') {
            sumarHorasEstudiante($estudiante_id, $registroAnterior['horas_acumuladas']);
        }
        
        // 4. Obtener datos actualizados del estudiante
        $queryEstudiante = "SELECT horas_requeridas, horas_completadas 
                            FROM estudiantes 
                            WHERE estudiante_id = :estudiante_id";
        $stmtEstudiante = $conn->prepare($queryEstudiante);
        $stmtEstudiante->bindParam(':estudiante_id', $estudiante_id, PDO::PARAM_INT);
        $stmtEstudiante->execute();
        $estudiante = $stmtEstudiante->fetch(PDO::FETCH_ASSOC);
        
        $conn->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Validación actualizada correctamente',
            'horas_requeridas' => $estudiante['horas_requeridas'],
            'horas_completadas' => $estudiante['horas_completadas']
        ]);
    } catch (PDOException $e) {
        $conn->rollBack();
        error_log('Error en validarRegistroEstudiante: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error al validar registro: ' . $e->getMessage()]);
    }
}

function sumarHorasEstudiante($estudiante_id, $horas) {
    global $conn;
    
    $query = "UPDATE estudiantes 
              SET horas_completadas = horas_completadas + :horas 
              WHERE estudiante_id = :estudiante_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':horas', $horas);
    $stmt->bindParam(':estudiante_id', $estudiante_id, PDO::PARAM_INT);
    $stmt->execute();
}

function restarHorasEstudiante($estudiante_id, $horas) {
    global $conn;
    
    $query = "UPDATE estudiantes 
              SET horas_completadas = GREATEST(0, horas_completadas - :horas)
              WHERE estudiante_id = :estudiante_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':horas', $horas);
    $stmt->bindParam(':estudiante_id', $estudiante_id, PDO::PARAM_INT);
    $stmt->execute();
}
?>
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

// Verificar que el usuario sea encargado
if (!isset($_SESSION['responsable_id'])) {
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado']);
    exit;
}

$accion = $_GET['accion'] ?? ($_POST['accion'] ?? '');

// Validar acción
if (!in_array($accion, ['listarRegistros', 'validarRegistro', 'obtenerDetalleRegistro'])) {
    echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    exit;
}

try {
    switch ($accion) {
        case 'listarRegistros':
            listarRegistros();
            break;
        case 'validarRegistro':
            validarRegistro();
            break;
        case 'obtenerDetalleRegistro':
            obtenerDetalleRegistro();
            break;
    }
} catch (PDOException $e) {
    error_log('Error en encargado_operaciones: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error en el servidor']);
}

function listarRegistros() {
    global $conn;
    
    $responsable_id = $_SESSION['responsable_id'];
    
    try {
        $query = "SELECT r.*, 
                         e.estudiante_id, e.matricula, e.nombre, e.apellido_paterno as apellido,
                         res.nombre as responsable_nombre, res.apellido_paterno as responsable_apellido
                  FROM registroshoras r
                  JOIN estudiantes e ON r.estudiante_id = e.estudiante_id
                  JOIN responsables res ON r.responsable_id = res.responsable_id
                  JOIN estudiantes_responsables er ON e.estudiante_id = er.estudiante_id
                  WHERE er.responsable_id = :responsable_id
                  ORDER BY r.fecha DESC, r.hora_entrada DESC";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':responsable_id', $responsable_id);
        $stmt->execute();
        
        $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($registros)) {
            echo json_encode([]);
            return;
        }
        
        $resultado = array_map(function($row) {
            return [
                'registro_id' => $row['registro_id'],
                'estudiante' => [
                    'estudiante_id' => $row['estudiante_id'],
                    'matricula' => $row['matricula'],
                    'nombre' => $row['nombre'],
                    'apellido' => $row['apellido']
                ],
                'responsable' => [
                    'nombre' => $row['responsable_nombre'],
                    'apellido' => $row['responsable_apellido']
                ],
                'fecha' => $row['fecha'],
                'hora_entrada' => $row['hora_entrada'],
                'hora_salida' => $row['hora_salida'],
                'horas_acumuladas' => $row['horas_acumuladas'],
                'estado' => $row['estado'],
                'observaciones' => $row['observaciones'],
                'fecha_validacion' => $row['fecha_validacion']
            ];
        }, $registros);
        
        echo json_encode($resultado);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error al listar registros: ' . $e->getMessage()]);
    }
}

function validarRegistro() {
    global $conn;
    
    $registro_id = $_POST['registro_id'] ?? null;
    $estado = $_POST['estado'] ?? null;
    $observaciones = $_POST['observaciones'] ?? null;
    
    if (!$registro_id || !$estado) {
        echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
        return;
    }
    
    try {
        // Verificar que el registro pertenezca a un estudiante del encargado
        $queryVerificar = "SELECT r.estado, r.horas_acumuladas, r.estudiante_id 
                          FROM registroshoras r
                          JOIN estudiantes_responsables er ON r.estudiante_id = er.estudiante_id
                          WHERE r.registro_id = :registro_id 
                          AND er.responsable_id = :responsable_id";
        
        $stmtVerificar = $conn->prepare($queryVerificar);
        $stmtVerificar->bindParam(':registro_id', $registro_id);
        $stmtVerificar->bindParam(':responsable_id', $_SESSION['responsable_id']);
        $stmtVerificar->execute();
        $registroAnterior = $stmtVerificar->fetch(PDO::FETCH_ASSOC);
        
        if (!$registroAnterior) {
            echo json_encode(['success' => false, 'message' => 'Registro no encontrado o no autorizado']);
            return;
        }
        
        // Actualizar el registro
        $query = "UPDATE registroshoras 
                  SET estado = :estado, 
                      observaciones = :observaciones,
                      fecha_validacion = NOW()
                  WHERE registro_id = :registro_id";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':observaciones', $observaciones);
        $stmt->bindParam(':registro_id', $registro_id);
        $stmt->execute();
        
        // Manejar las horas del estudiante
        if ($registroAnterior['estado'] == 'aprobado') {
            restarHorasEstudiante($registroAnterior['estudiante_id'], $registroAnterior['horas_acumuladas']);
        }
        
        if ($estado == 'aprobado') {
            sumarHorasEstudiante($registroAnterior['estudiante_id'], $registroAnterior['horas_acumuladas']);
        }
        
        echo json_encode(['success' => true, 'message' => 'Validación actualizada correctamente']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al validar registro: ' . $e->getMessage()]);
    }
}

function sumarHorasEstudiante($estudiante_id, $horas) {
    global $conn;
    
    try {
        $query = "UPDATE estudiantes 
                 SET horas_completadas = horas_completadas + :horas 
                 WHERE estudiante_id = :estudiante_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':horas', $horas);
        $stmt->bindParam(':estudiante_id', $estudiante_id);
        $stmt->execute();
    } catch (PDOException $e) {
        error_log('Error al sumar horas del estudiante: ' . $e->getMessage());
    }
}

function restarHorasEstudiante($estudiante_id, $horas) {
    global $conn;
    
    try {
        $query = "UPDATE estudiantes 
                 SET horas_completadas = GREATEST(0, horas_completadas - :horas)
                 WHERE estudiante_id = :estudiante_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':horas', $horas);
        $stmt->bindParam(':estudiante_id', $estudiante_id);
        $stmt->execute();
    } catch (PDOException $e) {
        error_log('Error al restar horas del estudiante: ' . $e->getMessage());
    }
}

function obtenerDetalleRegistro() {
    global $conn;
    
    $registro_id = $_GET['registro_id'] ?? null;
    
    if (!$registro_id) {
        echo json_encode(['success' => false, 'message' => 'ID de registro no proporcionado']);
        return;
    }
    
    try {
        $query = "SELECT r.*, 
                         e.estudiante_id, e.matricula, e.nombre, e.apellido_paterno as apellido,
                         res.nombre as responsable_nombre, res.apellido_paterno as responsable_apellido
                  FROM registroshoras r
                  JOIN estudiantes e ON r.estudiante_id = e.estudiante_id
                  JOIN responsables res ON r.responsable_id = res.responsable_id
                  JOIN estudiantes_responsables er ON e.estudiante_id = er.estudiante_id
                  WHERE r.registro_id = :registro_id
                  AND er.responsable_id = :responsable_id";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':registro_id', $registro_id);
        $stmt->bindParam(':responsable_id', $_SESSION['responsable_id']);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$row) {
            echo json_encode(['success' => false, 'message' => 'Registro no encontrado o no autorizado']);
            return;
        }
        
        $registro = [
            'registro_id' => $row['registro_id'],
            'estudiante' => [
                'estudiante_id' => $row['estudiante_id'],
                'matricula' => $row['matricula'],
                'nombre' => $row['nombre'],
                'apellido' => $row['apellido']
            ],
            'responsable' => [
                'nombre' => $row['responsable_nombre'],
                'apellido' => $row['responsable_apellido']
            ],
            'fecha' => $row['fecha'],
            'hora_entrada' => $row['hora_entrada'],
            'hora_salida' => $row['hora_salida'],
            'horas_acumuladas' => $row['horas_acumuladas'],
            'estado' => $row['estado'],
            'observaciones' => $row['observaciones'],
            'fecha_validacion' => $row['fecha_validacion']
        ];
        
        echo json_encode(['success' => true, 'data' => $registro]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al obtener detalle: ' . $e->getMessage()]);
    }
}
?>
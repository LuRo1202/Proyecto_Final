<?php
header('Content-Type: application/json; charset=utf-8');

// --- 1. CONFIGURACIÓN Y CONEXIÓN ---
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "servicio_social";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    error_log("Error de conexión DB: " . $conn->connect_error);
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos.']);
    exit();
}
$conn->set_charset('utf8mb4');

// --- 2. RECEPCIÓN Y SANITIZACIÓN DE DATOS ---
$input = file_get_contents('php://input');
$data = json_decode($input, true);
$response = ['success' => false, 'message' => 'Datos inválidos.'];

if ($data === null) {
    echo json_encode($response);
    exit();
}

function sanitize($conn, $input) {
    if ($input === null) return null;
    if (is_array($input)) {
        return array_map(fn($value) => sanitize($conn, $value), $input);
    }
    return $conn->real_escape_string(htmlspecialchars(strip_tags(trim($input))));
}

// --- 3. VALIDACIÓN DE CAMPOS REQUERIDOS ---
// Sincronizado con tu archivo registrar.html
$requiredFields = [
    'correo', 'contrasena', 'nombre', 'apellido_paterno', 'apellido_materno', 'matricula',
    'curp', // AÑADIDO: El CURP es requerido en tu HTML
    'domicilio', 'telefono', 'sexo', 'edad', 'carrera', 'cuatrimestre',
    'porcentaje_creditos', 'promedio', 'entidad_nombre', 'tipo_entidad', 'unidad_administrativa',
    'entidad_domicilio', 'municipio', 'entidad_telefono', 'funcionario_responsable',
    'cargo_funcionario', 'programa_nombre', 'actividades', 'periodo_inicio', 'periodo_fin',
    'periodo_registro_id'
];

foreach ($requiredFields as $field) {
    if (!isset($data[$field]) || trim((string)$data[$field]) === '') {
        $response['message'] = "El campo obligatorio '{$field}' está vacío.";
        error_log($response['message']);
        echo json_encode($response);
        $conn->close();
        exit();
    }
}

// --- 4. LÓGICA DE TRANSACCIÓN ---
$conn->begin_transaction();

try {
    // --- USUARIO ---
    $correo = sanitize($conn, $data['correo']);
    $contrasena_hashed = password_hash(sanitize($conn, $data['contrasena']), PASSWORD_DEFAULT);
    $rol_id_estudiante = 3;

    $stmt = $conn->prepare("SELECT usuario_id FROM usuarios WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) throw new Exception("El correo electrónico ya está registrado.");
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO usuarios (correo, contrasena, rol_id, tipo_usuario) VALUES (?, ?, ?, 'estudiante')");
    $stmt->bind_param("ssi", $correo, $contrasena_hashed, $rol_id_estudiante);
    if (!$stmt->execute()) throw new Exception("Error al registrar usuario: " . $stmt->error);
    $usuario_id = $stmt->insert_id;
    $stmt->close();

    // --- ESTUDIANTE ---
    // Manejo seguro de campos opcionales y requeridos
    $s_data = sanitize($conn, $data);
    $facebook = !empty($s_data['facebook']) ? $s_data['facebook'] : null;

    $stmt = $conn->prepare(
        "INSERT INTO estudiantes (usuario_id, matricula, nombre, apellido_paterno, apellido_materno, carrera, cuatrimestre, telefono, curp, edad, facebook, porcentaje_creditos, promedio, domicilio, sexo)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param(
        "isssssisisiddss",
        $usuario_id, $s_data['matricula'], $s_data['nombre'], $s_data['apellido_paterno'], $s_data['apellido_materno'],
        $s_data['carrera'], $s_data['cuatrimestre'], $s_data['telefono'], $s_data['curp'], $s_data['edad'],
        $facebook, $s_data['porcentaje_creditos'], $s_data['promedio'], $s_data['domicilio'], $s_data['sexo']
    );
    if (!$stmt->execute()) throw new Exception("Error al registrar datos del estudiante: " . $stmt->error);
    $estudiante_id = $stmt->insert_id;
    $stmt->close();
    
    // --- LÓGICA PARA ENTIDAD, PROGRAMA Y SOLICITUD (sin cambios, ya era robusta) ---

    // ENTIDAD
    $entidad_nombre = sanitize($conn, $data['entidad_nombre']);
    $unidad_admin = sanitize($conn, $data['unidad_administrativa']);
    $stmt = $conn->prepare("SELECT entidad_id FROM entidades_receptoras WHERE nombre = ? AND unidad_administrativa = ?");
    $stmt->bind_param("ss", $entidad_nombre, $unidad_admin);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $entidad_id = $result->fetch_assoc()['entidad_id'];
    } else {
        $stmt->close();
        $stmt = $conn->prepare("INSERT INTO entidades_receptoras (nombre, tipo_entidad, unidad_administrativa, domicilio, municipio, telefono, funcionario_responsable, cargo_funcionario) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $entidad_nombre, $s_data['tipo_entidad'], $unidad_admin, $s_data['entidad_domicilio'], $s_data['municipio'], $s_data['entidad_telefono'], $s_data['funcionario_responsable'], $s_data['cargo_funcionario']);
        if (!$stmt->execute()) throw new Exception("Error al registrar entidad: " . $stmt->error);
        $entidad_id = $stmt->insert_id;
    }
    $stmt->close();

    // PROGRAMA
    $programa_nombre = sanitize($conn, $data['programa_nombre']);
    $stmt = $conn->prepare("SELECT programa_id FROM programas WHERE nombre = ?");
    $stmt->bind_param("s", $programa_nombre);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $programa_id = $result->fetch_assoc()['programa_id'];
    } else {
        $stmt->close();
        $stmt = $conn->prepare("INSERT INTO programas (nombre) VALUES (?)");
        $stmt->bind_param("s", $programa_nombre);
        if (!$stmt->execute()) throw new Exception("Error al registrar programa: " . $stmt->error);
        $programa_id = $stmt->insert_id;
    }
    $stmt->close();

    // PERÍODO
    $periodo_id = (int)$s_data['periodo_registro_id'];
    // (La lógica para buscar un período activo si el proporcionado no es válido ya está implícita en tu JS y la carga inicial)

    // SOLICITUD
    $horario_lv_inicio = !empty($s_data['horario_lv_inicio']) ? $s_data['horario_lv_inicio'] : null;
    $horario_lv_fin = !empty($s_data['horario_lv_fin']) ? $s_data['horario_lv_fin'] : null;
    $horario_sd_inicio = !empty($s_data['horario_sd_inicio']) ? $s_data['horario_sd_inicio'] : null;
    $horario_sd_fin = !empty($s_data['horario_sd_fin']) ? $s_data['horario_sd_fin'] : null;

    $stmt = $conn->prepare("INSERT INTO solicitudes (estudiante_id, entidad_id, programa_id, periodo_id, funcionario_responsable, cargo_funcionario, fecha_solicitud, actividades, horario_lv_inicio, horario_lv_fin, horario_sd_inicio, horario_sd_fin, periodo_inicio, periodo_fin) VALUES (?, ?, ?, ?, ?, ?, CURDATE(), ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiiisssssssss",
        $estudiante_id, $entidad_id, $programa_id, $periodo_id,
        $s_data['funcionario_responsable'], $s_data['cargo_funcionario'], $s_data['actividades'],
        $horario_lv_inicio, $horario_lv_fin, $horario_sd_inicio, $horario_sd_fin,
        $s_data['periodo_inicio'], $s_data['periodo_fin']
    );
    if (!$stmt->execute()) throw new Exception("Error al registrar la solicitud: " . $stmt->error);
    $stmt->close();
    
    // --- 5. FINALIZAR TRANSACCIÓN ---
    $conn->commit();
    $response['success'] = true;
    $response['message'] = 'Solicitud registrada exitosamente.';

} catch (Exception $e) {
    $conn->rollback();
    $response['message'] = $e->getMessage();
    error_log("Error en transacción: " . $e->getMessage());
}

$conn->close();
echo json_encode($response);
?>
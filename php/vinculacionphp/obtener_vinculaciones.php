<?php
// C:\xampp\htdocs\Proyecto\Proyecto_Integrador\php\vinculacionphp\obtener_vinculaciones.php

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$config = ['host' => '127.0.0.1', 'user' => 'root', 'pass' => '', 'db' => 'servicio_social'];
$conn = new mysqli($config['host'], $config['user'], $config['pass'], $config['db']);

if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(["error" => "Error de conexión a la base de datos: " . $conn->connect_error]));
}
$conn->set_charset('utf8');

// --- Columnas para el ordenamiento de DataTables ---
$columnas_orden = [
    's.solicitud_id',
    "CONCAT(e.nombre, ' ', e.apellido_paterno)",
    'e.matricula',
    'er.nombre'
    // Las columnas de estado no son directamente ordenables de esta forma simple.
];

// --- Base de la consulta, ahora con JOINS específicos para cada estado ---
// <-- CAMBIO CLAVE: Se añaden LEFT JOINS para cada tipo de documento que necesitamos validar.
$query_base = "
    FROM solicitudes s
    JOIN estudiantes e ON s.estudiante_id = e.estudiante_id
    JOIN entidades_receptoras er ON s.entidad_id = er.entidad_id
    LEFT JOIN documentos_servicio ds_presentacion ON s.solicitud_id = ds_presentacion.solicitud_id AND ds_presentacion.tipo_documento_id = 1
    LEFT JOIN documentos_servicio ds_aceptacion ON s.solicitud_id = ds_aceptacion.solicitud_id AND ds_aceptacion.tipo_documento_id = 6
    LEFT JOIN documentos_servicio ds_informe1 ON s.solicitud_id = ds_informe1.solicitud_id AND ds_informe1.tipo_documento_id = 2
    LEFT JOIN documentos_servicio ds_informe2 ON s.solicitud_id = ds_informe2.solicitud_id AND ds_informe2.tipo_documento_id = 3
    LEFT JOIN documentos_servicio ds_pago ON s.solicitud_id = ds_pago.solicitud_id AND ds_pago.tipo_documento_id = 5
    LEFT JOIN documentos_servicio ds_general ON s.solicitud_id = ds_general.solicitud_id
";

// --- Construcción de la cláusula WHERE para los filtros ---
$where_clauses = ["1=1"];
$params = [];
$types = '';

if (!empty($_POST['periodo'])) {
    $where_clauses[] = "s.periodo_id = ?";
    $params[] = $_POST['periodo'];
    $types .= 'i';
}
if (!empty($_POST['estadoCarta'])) {
    $where_clauses[] = "s.estado_carta_aceptacion = ?";
    $params[] = $_POST['estadoCarta'];
    $types .= 's';
}
if (!empty($_POST['alumno'])) {
    $where_clauses[] = "CONCAT(e.nombre, ' ', e.apellido_paterno) LIKE ?";
    $params[] = '%' . $_POST['alumno'] . '%';
    $types .= 's';
}

$query_where = " WHERE " . implode(" AND ", $where_clauses);

// --- Conteo total de registros filtrados ---
$query_conteo = "SELECT COUNT(DISTINCT s.solicitud_id) " . $query_base . $query_where;
$stmt_conteo = $conn->prepare($query_conteo);
if ($stmt_conteo === false) { die(json_encode(["error" => "Error al preparar conteo: " . $conn->error])); }
if (count($params) > 0) { $stmt_conteo->bind_param($types, ...$params); }
$stmt_conteo->execute();
$stmt_conteo->bind_result($totalRecords);
$stmt_conteo->fetch();
$stmt_conteo->close();

// --- Obtención de los datos para la tabla ---
$query_data = "
    SELECT 
        s.solicitud_id, 
        CONCAT(e.nombre, ' ', e.apellido_paterno, ' ', e.apellido_materno) as nombre_alumno,
        e.matricula, 
        er.nombre as nombre_entidad,
        -- <-- CAMBIO CLAVE: Obtenemos el estado de las tablas unidas. Usamos COALESCE para poner 'Pendiente' si no hay registro.
        COALESCE(ds_presentacion.estado, 'pendiente') as estado_carta_presentacion,
        COALESCE(ds_aceptacion.estado, 'pendiente') as estado_carta_aceptacion,
        COALESCE(ds_informe1.estado, 'pendiente') as estado_primer_informe,
        COALESCE(ds_informe2.estado, 'pendiente') as estado_segundo_informe,
        COALESCE(ds_pago.estado, 'pendiente') as estado_comprobante_pago,
        GROUP_CONCAT(DISTINCT CONCAT_WS('::', ds_general.tipo_documento_id, ds_general.ruta_archivo, ds_general.documento_id) SEPARATOR '||') as documentos_subidos
    " . $query_base . $query_where . "
    GROUP BY s.solicitud_id
";

// --- Ordenamiento ---
$orderColumnIndex = $_POST['order'][0]['column'] ?? 1;
$orderDir = $_POST['order'][0]['dir'] ?? 'asc';
$orderColumn = $columnas_orden[$orderColumnIndex] ?? "nombre_alumno";
if ($orderColumn) {
    $query_data .= " ORDER BY $orderColumn " . ($orderDir === 'asc' ? 'ASC' : 'DESC');
}

// --- Paginación ---
$start = $_POST['start'] ?? 0;
$length = $_POST['length'] ?? 10;
if ($length != -1) {
    $params[] = $start;
    $params[] = $length;
    $types .= 'ii';
    $query_data .= " LIMIT ?, ?";
}

$stmt_data = $conn->prepare($query_data);
if ($stmt_data === false) { die(json_encode(["error" => "Error al preparar datos: " . $conn->error])); }
if (count($params) > 0) { $stmt_data->bind_param($types, ...$params); }
$stmt_data->execute();
$result = $stmt_data->get_result();
$data = $result->fetch_all(MYSQLI_ASSOC);
$stmt_data->close();

echo json_encode([
    "draw" => isset($_POST['draw']) ? intval($_POST['draw']) : 0,
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalRecords,
    "data" => $data
]);

$conn->close();
?>
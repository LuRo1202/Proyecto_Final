<?php
// C:\xampp\htdocs\Proyecto\Proyecto_Integrador\php\vinculacionphp\obtener_vinculaciones.php

header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// --- Configuración y Conexión a la Base de Datos ---
$config = ['host' => '127.0.0.1', 'user' => 'root', 'pass' => '', 'db' => 'servicio_social'];
$conn = new mysqli($config['host'], $config['user'], $config['pass'], $config['db']);

if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(["error" => "Error de conexión a la base de datos: " . $conn->connect_error]));
}
$conn->set_charset('utf8');

// --- Columnas para el ordenamiento de DataTables ---
// CORRECCIÓN: Se alinea el array con las columnas del frontend.
// Las columnas no ordenables se marcan como null para evitar errores.
$columnas_orden = [
    0 => 's.solicitud_id',
    1 => 'nombre_alumno',
    2 => 'e.matricula',
    3 => 'nombre_entidad',
    4 => null, // Docs. Subidos (no ordenable)
    5 => 'estado_carta_presentacion',
    6 => 'estado_carta_aceptacion',
    7 => 'estado_primer_informe',
    8 => 'estado_segundo_informe',
    9 => 'estado_comprobante_pago',
    10 => null, // Generar Docs (no ordenable)
];

// --- Base de la consulta con todos los JOINS ---
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

// --- Construcción de cláusulas WHERE y HAVING para los filtros ---
$where_clauses = [];
$having_clauses = [];
$params_where = [];
$types_where = '';
$params_having = [];
$types_having = '';

if (!empty($_POST['periodo'])) {
    $where_clauses[] = "s.periodo_id = ?";
    $params_where[] = $_POST['periodo'];
    $types_where .= 'i';
}
if (!empty($_POST['alumno'])) {
    $where_clauses[] = "CONCAT(e.nombre, ' ', e.apellido_paterno, ' ', e.apellido_materno) LIKE ?";
    $params_where[] = '%' . $_POST['alumno'] . '%';
    $types_where .= 's';
}

// CORRECCIÓN CLAVE: El filtro de estado se mueve a una cláusula HAVING porque se aplica sobre una columna calculada.
if (!empty($_POST['estadoCarta'])) {
    $having_clauses[] = "estado_carta_aceptacion = ?";
    $params_having[] = $_POST['estadoCarta'];
    $types_having .= 's';
}

$query_where = count($where_clauses) > 0 ? " WHERE " . implode(" AND ", $where_clauses) : "";
$query_having = count($having_clauses) > 0 ? " HAVING " . implode(" AND ", $having_clauses) : "";

// --- Consulta para contar los registros filtrados (recordsFiltered) ---
// CORRECCIÓN: Se usa una subconsulta para contar correctamente después de aplicar GROUP BY y HAVING.
$query_conteo_sql = "
    SELECT COUNT(*) FROM (
        SELECT s.solicitud_id,
               COALESCE(ds_aceptacion.estado, 'pendiente') as estado_carta_aceptacion
        $query_base
        $query_where
        GROUP BY s.solicitud_id
        $query_having
    ) AS filtered_count
";

$stmt_conteo = $conn->prepare($query_conteo_sql);
if ($stmt_conteo === false) { die(json_encode(["error" => "Error al preparar conteo: " . $conn->error])); }

$params_conteo = array_merge($params_where, $params_having);
$types_conteo = $types_where . $types_having;
if (!empty($params_conteo)) {
    $stmt_conteo->bind_param($types_conteo, ...$params_conteo);
}
$stmt_conteo->execute();
$stmt_conteo->bind_result($totalRecords);
$stmt_conteo->fetch();
$stmt_conteo->close();

// --- Consulta principal para obtener los datos de la página actual ---
$query_data_sql = "
    SELECT 
        s.solicitud_id, 
        CONCAT(e.nombre, ' ', e.apellido_paterno, ' ', e.apellido_materno) as nombre_alumno,
        e.matricula, 
        er.nombre as nombre_entidad,
        COALESCE(ds_presentacion.estado, 'pendiente') as estado_carta_presentacion,
        COALESCE(ds_aceptacion.estado, 'pendiente') as estado_carta_aceptacion,
        COALESCE(ds_informe1.estado, 'pendiente') as estado_primer_informe,
        COALESCE(ds_informe2.estado, 'pendiente') as estado_segundo_informe,
        COALESCE(ds_pago.estado, 'pendiente') as estado_comprobante_pago,
        GROUP_CONCAT(DISTINCT CONCAT_WS('::', ds_general.tipo_documento_id, ds_general.ruta_archivo, ds_general.documento_id) SEPARATOR '||') as documentos_subidos
    $query_base
    $query_where
    GROUP BY s.solicitud_id
    $query_having
";

// --- Ordenamiento ---
$orderColumnIndex = $_POST['order'][0]['column'] ?? 1; // Default a la columna de nombre de alumno
$orderDir = $_POST['order'][0]['dir'] ?? 'asc';
$orderColumn = $columnas_orden[$orderColumnIndex] ?? $columnas_orden[1]; // Fallback a nombre de alumno

if ($orderColumn) {
    $query_data_sql .= " ORDER BY $orderColumn " . ($conn->real_escape_string($orderDir));
}

// --- Paginación ---
$start = $_POST['start'] ?? 0;
$length = $_POST['length'] ?? 10;
$params_data = array_merge($params_where, $params_having);
$types_data = $types_where . $types_having;

if ($length != -1) {
    $query_data_sql .= " LIMIT ?, ?";
    $params_data[] = $start;
    $params_data[] = $length;
    $types_data .= 'ii';
}

$stmt_data = $conn->prepare($query_data_sql);
if ($stmt_data === false) { die(json_encode(["error" => "Error al preparar datos: " . $conn->error])); }

if (!empty($params_data)) {
    $stmt_data->bind_param($types_data, ...$params_data);
}
$stmt_data->execute();
$result = $stmt_data->get_result();
$data = $result->fetch_all(MYSQLI_ASSOC);
$stmt_data->close();

// --- Respuesta JSON para DataTables ---
echo json_encode([
    "draw" => isset($_POST['draw']) ? intval($_POST['draw']) : 0,
    "recordsTotal" => $totalRecords, // Total de registros después de aplicar filtros
    "recordsFiltered" => $totalRecords, // Mismo valor, ya que no tenemos un conteo total sin filtros
    "data" => $data
]);

$conn->close();
?>
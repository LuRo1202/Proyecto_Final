<?php
// Muestra todos los errores de PHP para un diagnóstico claro.
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['usuario_id'])) { 
    die('Acceso denegado. Debes iniciar sesión.'); 
}

if (!isset($_GET['carrera']) || empty($_GET['carrera'])) { 
    die('Error: No se ha especificado una carrera.'); 
}
$carrera_seleccionada = $_GET['carrera'];

// --- Conexión a la Base de Datos ---
$conn = new mysqli('127.0.0.1', 'root', '', 'servicio_social');
if ($conn->connect_error) { 
    die('Error de conexión a la base de datos: ' . $conn->connect_error); 
}
$conn->set_charset('utf8');

// --- Consulta a la Base de Datos ---
$query = "
    SELECT 
        e.nombre, e.apellido_paterno, e.apellido_materno, e.matricula, e.carrera, e.sexo,
        er.nombre as entidad_nombre, er.funcionario_responsable, er.cargo_funcionario,
        s.periodo_inicio, s.periodo_fin,
        p.nombre as programa_nombre
    FROM solicitudes s
    JOIN estudiantes e ON s.estudiante_id = e.estudiante_id
    JOIN entidades_receptoras er ON s.entidad_id = er.entidad_id
    JOIN programas p ON s.programa_id = p.programa_id
    WHERE s.estado IN ('pendiente', 'aprobada') AND e.carrera = ?
";

$stmt = $conn->prepare($query);
if ($stmt === false) {
    die('Error al preparar la consulta: ' . $conn->error);
}
$stmt->bind_param("s", $carrera_seleccionada);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) { 
    die('No se encontraron alumnos con solicitudes activas para la carrera: ' . htmlspecialchars($carrera_seleccionada)); 
}
$alumnos = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

// --- FUNCIÓN DE FECHA CORREGIDA ---
function formatearFecha($fecha) {
    if (empty($fecha)) {
        return '[Fecha no especificada]';
    }
    try {
        $date = new DateTime($fecha);
        // IntlDateFormatter es la forma moderna y correcta de formatear fechas.
        if (class_exists('IntlDateFormatter')) {
            $formatter = new IntlDateFormatter('es_ES', IntlDateFormatter::LONG, IntlDateFormatter::NONE);
            return $formatter->format($date);
        }
        // Fallback manual si la extensión 'intl' no está habilitada.
        $meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
        return $date->format('d') . ' de ' . $meses[intval($date->format('m')) - 1] . ' de ' . $date->format('Y');
    } catch (Exception $e) {
        return '[Fecha inválida]';
    }
}

$fecha_actual_str = formatearFecha(date('Y-m-d'));

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Generación Masiva de Cartas - <?= htmlspecialchars($carrera_seleccionada) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body { background-color: #e9ecef; }
        .carta-container { width: 21cm; min-height: 29.7cm; padding: 2cm; margin: 1cm auto; background: white; box-shadow: 0 0 15px rgba(0,0,0,0.1); font-family: 'Times New Roman', serif; font-size: 12pt; line-height: 1.8; }
        .page-break { page-break-after: always; }
        .encabezado { text-align: right; }
        .destinatario { margin-top: 3rem; margin-bottom: 2rem; }
        .cuerpo-carta { text-align: justify; }
        .despedida { margin-top: 3rem; }
        .firma { text-align: center; margin-top: 5rem; }
        .no-print { position: fixed; top: 1rem; right: 1rem; z-index: 100; }
        @media print {
            body { background-color: white; }
            .carta-container { margin: 0; padding: 0; box-shadow: none; border: none; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" class="btn btn-primary"><i class="bi bi-printer-fill"></i> Imprimir o Guardar como PDF</button>
    </div>

    <?php foreach ($alumnos as $index => $data): 
        $nombre_completo = ucwords(strtolower(trim($data['nombre'] . ' ' . $data['apellido_paterno'] . ' ' . $data['apellido_materno'])));
        $fecha_inicio_str = formatearFecha($data['periodo_inicio']);
        $fecha_fin_str = formatearFecha($data['periodo_fin']);
        $articulo = ($data['sexo'] == 'Femenino') ? 'a la' : 'al';
    ?>
    
    <!-- CARTA DE PRESENTACIÓN -->
    <div class="carta-container">
        <div class="encabezado">
            <p class="mb-0"><strong>DEPARTAMENTO DE SERVICIO SOCIAL</strong></p>
            <p><strong>ASUNTO: CARTA DE PRESENTACIÓN</strong></p>
            <p>Texcoco, Estado de México, a <?= htmlspecialchars($fecha_actual_str) ?>.</p>
        </div>
        <div class="destinatario">
            <p class="mb-0"><strong><?= htmlspecialchars(strtoupper($data['funcionario_responsable'])) ?></strong></p>
            <p class="mb-0"><strong><?= htmlspecialchars(strtoupper($data['cargo_funcionario'])) ?></strong></p>
            <p><strong><?= htmlspecialchars(strtoupper($data['entidad_nombre'])) ?></strong></p>
            <p><strong>P R E S E N T E</strong></p>
        </div>
        <div class="cuerpo-carta">
            <p>Por este conducto, me permito presentar a sus finas atenciones al/la C. <strong><?= htmlspecialchars($nombre_completo) ?></strong>, con número de matrícula <strong><?= htmlspecialchars($data['matricula']) ?></strong>, pasante de la carrera de <strong><?= htmlspecialchars($data['carrera']) ?></strong>, quien desea realizar su Servicio Social en la institución a su digno cargo.</p>
            <p>El/La estudiante cubrirá un total de 480 horas en el periodo comprendido del <?= htmlspecialchars($fecha_inicio_str) ?> al <?= htmlspecialchars($fecha_fin_str) ?>.</p>
        </div>
        <div class="despedida"><p><strong>ATENTAMENTE</strong></p></div>
        <div class="firma">
            <p>_________________________________________</p>
            <p><strong>Dr. Eduardo Castellanos Sahagún</strong></p>
        </div>
    </div>
    <div class="page-break"></div>

    <!-- CARTA DE ACEPTACIÓN -->
    <div class="carta-container">
        <div class="encabezado" style="text-align: center;">
            <h4 class="mb-0"><strong>Constancia de Aceptación de Servicio Social</strong></h4>
            <p class="mt-4 text-end">Texcoco, Estado de México, a <?= htmlspecialchars($fecha_actual_str) ?>.</p>
        </div>
        <div class="cuerpo-carta" style="margin-top: 4rem;">
            <p>Por medio de la presente, se hace constar que el/la C. <strong><?= htmlspecialchars($nombre_completo) ?></strong>, con número de matrícula <strong><?= htmlspecialchars($data['matricula']) ?></strong>, ha sido aceptado<?= (($data['sexo'] == 'Femenino') ? 'a' : '') ?> para realizar su Servicio Social en esta institución: <strong><?= htmlspecialchars(strtoupper($data['entidad_nombre'])) ?></strong>.</p>
            <p>El periodo de realización será del <?= htmlspecialchars($fecha_inicio_str) ?> al <?= htmlspecialchars($fecha_fin_str) ?>.</p>
            <p>Se extiende la presente constancia para los fines que <?= $articulo ?> interesado<?= (($data['sexo'] == 'Femenino') ? 'a' : '') ?> convengan.</p>
        </div>
        <div class="despedida"><p><strong>ATENTAMENTE</strong></p></div>
        <div class="firma">
            <p>_________________________________________</p>
            <p><strong><?= htmlspecialchars(strtoupper($data['funcionario_responsable'])) ?></strong></p>
            <p><strong><?= htmlspecialchars(strtoupper($data['cargo_funcionario'])) ?></strong></p>
        </div>
    </div>
    <?php if ($index < count($alumnos) - 1): ?>
        <div class="page-break"></div>
    <?php endif; ?>

    <?php endforeach; ?>
</body>
</html>
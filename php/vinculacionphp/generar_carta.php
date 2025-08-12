<?php
// Muestra errores de PHP para un diagnóstico claro.
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['usuario_id'])) { die('Acceso denegado.'); }

if (!isset($_GET['id']) || !is_numeric($_GET['id']) || !isset($_GET['tipo'])) { 
    die('Parámetros no válidos.'); 
}

$solicitud_id = intval($_GET['id']);
$tipo_carta = $_GET['tipo'];

$conn = new mysqli('127.0.0.1', 'root', '', 'servicio_social');
if ($conn->connect_error) { die('Error de conexión.'); }
$conn->set_charset('utf8');

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
    WHERE s.solicitud_id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $solicitud_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) { die('No se encontraron datos para esta solicitud.'); }
$data = $result->fetch_assoc();
$stmt->close();
$conn->close();

$nombre_completo = ucwords(strtolower(trim($data['nombre'] . ' ' . $data['apellido_paterno'] . ' ' . $data['apellido_materno'])));

// --- FUNCIÓN DE FECHA CORREGIDA ---
// Esta función ya no usa la obsoleta strftime()
function formatearFecha($fecha) {
    if (empty($fecha)) {
        return '[Fecha no especificada]';
    }
    try {
        $date = new DateTime($fecha);
        // IntlDateFormatter es la forma moderna y correcta de formatear fechas con nombres de meses en español.
        if (class_exists('IntlDateFormatter')) {
            $formatter = new IntlDateFormatter('es_ES', IntlDateFormatter::LONG, IntlDateFormatter::NONE);
            return $formatter->format($date);
        }
        // Si la extensión 'intl' no está habilitada en el servidor, usamos un método manual.
        $meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
        return $date->format('d') . ' de ' . $meses[intval($date->format('m')) - 1] . ' de ' . $date->format('Y');
    } catch (Exception $e) {
        return '[Fecha inválida]';
    }
}

$fecha_inicio_str = formatearFecha($data['periodo_inicio']);
$fecha_fin_str = formatearFecha($data['periodo_fin']);
$fecha_actual_str = formatearFecha(date('Y-m-d'));

$titulo_pagina = "Documento de Servicio Social";
$contenido_carta = "";

// --- Lógica para seleccionar la plantilla de la carta (sin cambios) ---
switch ($tipo_carta) {
    case 'presentacion':
        $titulo_pagina = "Carta de Presentación - " . $nombre_completo;
        $contenido_carta = '
            <div class="destinatario">
                <p class="mb-0"><strong>' . htmlspecialchars(strtoupper($data['funcionario_responsable'])) . '</strong></p>
                <p class="mb-0"><strong>' . htmlspecialchars(strtoupper($data['cargo_funcionario'])) . '</strong></p>
                <p><strong>' . htmlspecialchars(strtoupper($data['entidad_nombre'])) . '</strong></p>
                <p><strong>P R E S E N T E</strong></p>
            </div>
            <div class="cuerpo-carta">
                <p>Por este conducto, me permito presentar a sus finas atenciones al/la C. <strong>' . htmlspecialchars($nombre_completo) . '</strong>, con número de matrícula <strong>' . htmlspecialchars($data['matricula']) . '</strong>, pasante de la carrera de <strong>' . htmlspecialchars($data['carrera']) . '</strong>, quien desea realizar su Servicio Social en la institución a su digno cargo.</p>
                <p>El/La estudiante cubrirá un total de 480 horas en el periodo comprendido del ' . htmlspecialchars($fecha_inicio_str) . ' al ' . htmlspecialchars($fecha_fin_str) . ', participando en el programa "' . htmlspecialchars($data['programa_nombre']) . '".</p>
                <p>Agradezco de antemano el apoyo que se sirva brindar al portador de la presente para el cumplimiento de este requisito indispensable para su formación profesional.</p>
            </div>';
        break;

    case 'aceptacion':
        $titulo_pagina = "Constancia de Aceptación - " . $nombre_completo;
        $articulo = ($data['sexo'] == 'Femenino') ? 'a la' : 'al';
        $contenido_carta = '
            <div class="cuerpo-carta" style="margin-top: 4rem;">
                <p>Por medio de la presente, se hace constar que el/la C. <strong>' . htmlspecialchars($nombre_completo) . '</strong>, con número de matrícula <strong>' . htmlspecialchars($data['matricula']) . '</strong>, ha sido aceptado' . (($data['sexo'] == 'Femenino') ? 'a' : '') . ' para realizar su Servicio Social en esta institución: <strong>' . htmlspecialchars(strtoupper($data['entidad_nombre'])) . '</strong>.</p>
                <p>El periodo de realización será del ' . htmlspecialchars($fecha_inicio_str) . ' al ' . htmlspecialchars($fecha_fin_str) . ', dentro del programa "' . htmlspecialchars($data['programa_nombre']) . '".</p>
                <p>Se extiende la presente constancia para los fines que ' . $articulo . ' interesado' . (($data['sexo'] == 'Femenino') ? 'a' : '') . ' convengan.</p>
            </div>';
        break;

    case 'termino':
        $titulo_pagina = "Carta de Término - " . $nombre_completo;
        $contenido_carta = '
            <div class="destinatario">
                <p class="mb-0"><strong>DR. EDUARDO CASTELLANOS SAHAGUN</strong></p>
                <p class="mb-0"><strong>UNIVERSIDAD POLITECNICA DE TEXCOCO</strong></p>
                <p><strong>P R E S E N T E</strong></p>
            </div>
            <div class="cuerpo-carta">
                <p>Por medio del presente, me permito informarle que el/la C. <strong>' . htmlspecialchars($nombre_completo) . '</strong>, con número de matrícula <strong>' . htmlspecialchars($data['matricula']) . '</strong>, pasante de la carrera de <strong>' . htmlspecialchars($data['carrera']) . '</strong>, ha cumplido satisfactoriamente su Servicio Social en el programa "' . htmlspecialchars($data['programa_nombre']) . '", cubriendo un total de 480 horas.</p>
                <p>Dicho servicio fue realizado en la institución a su digno cargo, durante el periodo comprendido del ' . htmlspecialchars($fecha_inicio_str) . ' al ' . htmlspecialchars($fecha_fin_str) . '.</p>
                <p>Agradeciendo de antemano la atención y el apoyo brindado para la realización de esta actividad formativa, le envío un cordial saludo.</p>
            </div>';
        break;
    
    default:
        die('Tipo de carta no reconocido.');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($titulo_pagina) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body { background-color: #e9ecef; font-size: 12pt; }
        .carta-container { max-width: 800px; margin: 2rem auto; background: white; padding: 4rem; box-shadow: 0 0 15px rgba(0,0,0,0.1); font-family: 'Times New Roman', serif; line-height: 1.8; }
        .encabezado { text-align: center; margin-bottom: 3rem; }
        .destinatario, .cuerpo-carta, .despedida, .firma { margin-bottom: 2rem; }
        .firma { text-align: center; margin-top: 5rem; }
        .no-print { position: fixed; top: 1rem; right: 1rem; z-index: 100; }
        @media print {
            body { background-color: white; }
            .carta-container { margin: 0; padding: 0; box-shadow: none; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" class="btn btn-primary"><i class="bi bi-printer-fill"></i> Imprimir o Guardar como PDF</button>
    </div>
    <div class="carta-container">
        <div class="encabezado">
            <h4 class="mb-0"><strong><?= htmlspecialchars(ucwords(str_replace('_', ' ', $tipo_carta))) ?> de Servicio Social</strong></h4>
            <p class="mt-4 text-end">Texcoco, Estado de México, a <?= htmlspecialchars($fecha_actual_str) ?>.</p>
        </div>
        
        <?= $contenido_carta ?>

        <div class="despedida">
            <p><strong>ATENTAMENTE</strong></p>
        </div>
        
        <div class="firma">
            <p>_________________________________________</p>
            <p><strong>Dr. Eduardo Castellanos Sahagún</strong></p>
            <p><strong>Universidad Politecnica de Texco</strong></p>
        </div>
    </div>
</body>
</html>
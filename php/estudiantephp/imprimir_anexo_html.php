<?php
// 1. OBTENER DATOS Y VALIDAR SESIÓN
require_once '../conexion.php'; 
session_start();

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(403);
    die("Acceso denegado. No ha iniciado sesión.");
}
if (!isset($_GET['id']) || empty($_GET['id'])) {
    http_response_code(400);
    die("Error: No se proporcionó un ID de solicitud válido.");
}

$usuario_id = $_SESSION['usuario_id'];
$solicitud_id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
$solicitud = null;

try {
    $stmt_est = $conn->prepare("SELECT estudiante_id FROM estudiantes WHERE usuario_id = :usuario_id");
    $stmt_est->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
    $stmt_est->execute();
    $estudiante = $stmt_est->fetch(PDO::FETCH_ASSOC);

    if (!$estudiante) { throw new Exception("No se encontró el perfil del estudiante."); }
    $estudiante_id = $estudiante['estudiante_id'];

    $sql = "SELECT
                e.nombre, e.apellido_paterno, e.apellido_materno, e.domicilio, e.telefono, e.edad, e.sexo, e.carrera,
                e.porcentaje_creditos, e.promedio, e.facebook, e.matricula, e.curp,
                u.correo,
                er.nombre as entidad_nombre, er.tipo_entidad, er.unidad_administrativa, er.domicilio as entidad_domicilio, er.municipio, er.telefono as entidad_telefono,
                er.funcionario_responsable, er.cargo_funcionario,
                p.nombre as programa_nombre,
                s.fecha_solicitud, s.horario_lv_inicio, s.horario_lv_fin, s.periodo_inicio, s.periodo_fin, s.actividades
            FROM solicitudes s
            JOIN estudiantes e ON s.estudiante_id = e.estudiante_id
            JOIN usuarios u ON e.usuario_id = u.usuario_id
            JOIN entidades_receptoras er ON s.entidad_id = er.entidad_id
            JOIN programas p ON s.programa_id = p.programa_id
            WHERE s.solicitud_id = :solicitud_id AND s.estudiante_id = :estudiante_id";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':solicitud_id', $solicitud_id, PDO::PARAM_INT);
    $stmt->bindParam(':estudiante_id', $estudiante_id, PDO::PARAM_INT);
    $stmt->execute();
    $solicitud = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$solicitud) { throw new Exception("No se encontró la solicitud o no tiene permiso para verla."); }

    $solicitud['nombre_alumno_firma'] = $solicitud['nombre'] . ' ' . $solicitud['apellido_paterno'] . ' ' . $solicitud['apellido_materno'];
    $solicitud['nombre_empresa_firma'] = 'Universidad Politécnica de Texcoco';

    $meses_espanol = ['January' => 'JULIO', 'February' => 'FEBRERO', 'March' => 'MARZO', 'April' => 'ABRIL', 'May' => 'MAYO', 'June' => 'JUNIO', 'July' => 'JULIO', 'August' => 'AGOSTO', 'September' => 'SEPTIEMBRE', 'October' => 'OCTUBRE', 'November' => 'NOVIEMBRE', 'December' => 'DICIEMBRE'];
    
    $fecha_inicio = new DateTime($solicitud['periodo_inicio']);
    $solicitud['periodo_inicio_dia'] = $fecha_inicio->format('d');
    $solicitud['periodo_inicio_mes'] = $meses_espanol[$fecha_inicio->format('F')];
    $solicitud['periodo_inicio_anio'] = $fecha_inicio->format('Y');

    $fecha_fin = new DateTime($solicitud['periodo_fin']);
    $solicitud['periodo_fin_dia'] = $fecha_fin->format('d');
    $solicitud['periodo_fin_mes'] = $meses_espanol[$fecha_fin->format('F')];
    $solicitud['periodo_fin_anio'] = $fecha_fin->format('Y');

} catch (Exception $e) {
    http_response_code(500);
    die("Error en la base de datos: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Anexo F - Solicitud</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 9pt; }
        @page { size: letter portrait; margin: 1cm; }
        .no-print { text-align: center; padding: 10px; background: #333; color:white; }
        .main-container { border: 2px solid black; padding: 5px; }
        .layout-table { width: 100%; border-collapse: collapse; }
        .layout-table td { padding: 1.5px 3px; vertical-align: bottom; }
        .header { font-weight: bold; font-size: 11pt; text-align: center; border: 1px solid black; background-color: #E7E6E6; padding: 4px; }
        .label { font-weight: bold; white-space: nowrap; padding-right: 4px; }
        .data-field { border-bottom: 1px solid black; width: 100%; display: block; min-height: 16px; padding: 0 2px; }
        .data-field-center { border-bottom: 1px solid black; width: 100%; display: block; text-align: center; min-height: 16px; }
        .sub-label { font-size: 8pt; text-align: center; padding-top: 1px; }
        .checkbox { width: 10px; height: 10px; border: 1px solid black; display: inline-block; text-align: center; line-height: 10px; font-weight: bold; vertical-align: middle; }
        .programas-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1px 10px; margin-top: 4px;}
        .programa-item { font-size: 8pt; display: flex; align-items: flex-start; }
        .programa-item .checkbox { flex-shrink: 0; margin-top: 2px; margin-right: 4px;}
        .firma { border-top: 1px solid black; padding-top: 5px; text-align: center; min-height: 18px; font-weight: bold; }
        .firma-label { text-align: center; font-weight: bold; font-size: 9pt; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print">
        <p>RECUERDA: En el menú de impresión (Ctrl+P), en "Más ajustes" -> "Escala", selecciona "Ajustar al área de impresión".</p>
        <button onclick="window.print()">Imprimir</button>
        <button onclick="window.close()">Cerrar</button>
    </div>

    <div class="main-container">
        <div class="header" style="font-size:12pt;">ANEXO F "SOLICITUD - REGISTRO/AUTORIZACIÓN"</div>
        <div class="header" style="margin-top: 5px;">I. DATOS DEL PRESTADOR E INSTITUCIÓN EDUCATIVA</div>
        
        <table class="layout-table" style="margin-top: 8px;">
            <tr>
                <td style="width:12%;"><span class="label">1.- Fecha:</span></td>
                <td style="width:28%;"><span class="data-field"><?= htmlspecialchars($solicitud['fecha_solicitud']) ?></span></td>
                <td style="width:8%;" class="label">2.- Nombre:</td>
                <td style="width:18%;"><div class="data-field-center"><?= htmlspecialchars($solicitud['apellido_paterno']) ?></div></td>
                <td style="width:18%;"><div class="data-field-center"><?= htmlspecialchars($solicitud['apellido_materno']) ?></div></td>
                <td style="width:16%;"><div class="data-field-center"><?= htmlspecialchars($solicitud['nombre']) ?></div></td>
            </tr>
            <tr><td></td><td></td><td></td><td class="sub-label">Apellido Paterno</td><td class="sub-label">Apellido Materno</td><td class="sub-label">Nombre(s)</td></tr>
            <tr><td colspan="6" style="padding-top: 5px;"><span class="label">3.- Domicilio:</span><span class="data-field"><?= htmlspecialchars($solicitud['domicilio']) ?></span></td></tr>
            <tr>
                <td colspan="2"><span class="label">4.- Teléfono Fijo:</span><span class="data-field">&nbsp;</span></td>
                <td colspan="2"><span class="label">Teléfono Celular:</span><span class="data-field"><?= htmlspecialchars($solicitud['telefono']) ?></span></td>
                <td><span class="label">EDAD:</span><span class="data-field-center"><?= htmlspecialchars($solicitud['edad']) ?></span></td>
                <td><span class="label">SEXO:</span><span class="data-field-center"><?= htmlspecialchars($solicitud['sexo']) ?></span></td>
            </tr>
            <tr>
                <td colspan="3"><span class="label">5.- Correo Electrónico:</span><span class="data-field"><?= htmlspecialchars($solicitud['correo']) ?></span></td>
                <td colspan="3"><span class="label">6.- Carrera:</span><span class="data-field"><?= htmlspecialchars($solicitud['carrera']) ?></span></td>
            </tr>
            <tr>
                <td colspan="2"><span class="label">7.- % Créditos:</span><span class="data-field-center"><?= htmlspecialchars($solicitud['porcentaje_creditos']) ?> %</span></td>
                <td colspan="2"><span class="label">Promedio:</span><span class="data-field-center"><?= htmlspecialchars($solicitud['promedio']) ?></span></td>
                <td colspan="2"><span class="label">8.- Facebook:</span><span class="data-field"><?= htmlspecialchars($solicitud['facebook']) ?></span></td>
            </tr>
            <tr>
                <td colspan="3"><span class="label">9.- Matrícula:</span><span class="data-field"><?= htmlspecialchars($solicitud['matricula']) ?></span></td>
                <td colspan="3"><span class="label">10.- CURP:</span><span class="data-field"><?= htmlspecialchars($solicitud['curp']) ?></span></td>
            </tr>
        </table>

        <div class="header" style="margin-top: 8px;">II. DATOS DE LA ENTIDAD RECEPTORA</div>

        <table class="layout-table" style="margin-top: 8px;">
            <tr><td colspan="6"><span class="label">11.- Nombre de la Entidad Receptora:</span><span class="data-field"><?= htmlspecialchars($solicitud['entidad_nombre']) ?></span></td></tr>
            <tr>
                 <td class="label" style="width:18%;">12.- Tipo de Entidad:</td>
                 <td colspan="5" style="text-align: center; padding: 4px 0;">
                    <?php $tipos_entidad = ['Federal', 'Estatal', 'Municipal', 'O.N.G.', 'I.E.', 'I.P.'];
                    foreach ($tipos_entidad as $tipo): ?>
                        <span style="margin: 0 4px;"><?= $tipo ?> <span class="checkbox"><?= (strcasecmp($solicitud['tipo_entidad'], $tipo) == 0) ? 'X' : '&nbsp;' ?></span></span>
                    <?php endforeach; ?>
                </td>
            </tr>
            <tr><td colspan="6"><span class="label">13.- Unidad Admva:</span><span class="data-field"><?= htmlspecialchars($solicitud['unidad_administrativa']) ?></span></td></tr>
            <tr><td colspan="6"><span class="label">14.- Domicilio Entidad:</span><span class="data-field"><?= htmlspecialchars($solicitud['entidad_domicilio']) ?></span></td></tr>
            <tr>
                <td colspan="3"><span class="label">15.- Municipio:</span><span class="data-field"><?= htmlspecialchars($solicitud['municipio']) ?></span></td>
                <td colspan="3"><span class="label">16.- Teléfono:</span><span class="data-field"><?= htmlspecialchars($solicitud['entidad_telefono']) ?></span></td>
            </tr>
            <tr><td colspan="6"><span class="label">17.- Funcionario responsable y cargo:</span><span class="data-field"><?= htmlspecialchars($solicitud['funcionario_responsable']) . ' - ' . htmlspecialchars($solicitud['cargo_funcionario']) ?></span></td></tr>
            
            <tr>
                <td colspan="6" style="padding-top: 5px;">
                     <div class="label">18.- Programa en el que participará el prestador</div>
                     <div class="programas-grid">
                        <?php $programa_seleccionado = $solicitud['programa_nombre']; ?>
                        <div class="programa-item"><span class="checkbox"><?= (strcasecmp($programa_seleccionado, 'Vivienda') == 0) ? 'X' : '&nbsp;' ?></span>Vivienda</div>
                        <div class="programa-item"><span class="checkbox"><?= (strcasecmp($programa_seleccionado, 'Empleo y capacitación para el trabajo') == 0) ? 'X' : '&nbsp;' ?></span>Empleo y capacitación para el trabajo</div>
                        <div class="programa-item"><span class="checkbox"><?= (strcasecmp($programa_seleccionado, 'Grupos vulnerables con capacidades diferentes, infantes y tercera edad') == 0) ? 'X' : '&nbsp;' ?></span>Grupos vulnerables con capacidades diferentes, infantes y tercera edad</div>
                        <div class="programa-item"><span class="checkbox"><?= (strcasecmp($programa_seleccionado, 'Pueblos indigenas') == 0) ? 'X' : '&nbsp;' ?></span>Pueblos indigenas</div>
                        <div class="programa-item"><span class="checkbox"><?= (strcasecmp($programa_seleccionado, 'Derechos humanos') == 0) ? 'X' : '&nbsp;' ?></span>Derechos humanos</div>
                        <div class="programa-item"><span class="checkbox"><?= (strcasecmp($programa_seleccionado, 'Infraestructura hidraulica y de saneamiento') == 0) ? 'X' : '&nbsp;' ?></span>Infraestructura hidraulica y de saneamiento</div>
                        <div class="programa-item"><span class="checkbox"><?= (strcasecmp($programa_seleccionado, 'Asistencia y seguridad social') == 0) ? 'X' : '&nbsp;' ?></span>Asistencia y seguridad social</div>
                        <div class="programa-item"><span class="checkbox"><?= (strcasecmp($programa_seleccionado, 'Medio ambiente') == 0) ? 'X' : '&nbsp;' ?></span>Medio ambiente</div>
                        <div class="programa-item"><span class="checkbox"><?= (strcasecmp($programa_seleccionado, 'Educación, arte, cultura y deporte') == 0) ? 'X' : '&nbsp;' ?></span>Educación, arte, cultura y deporte</div>
                        <div class="programa-item"><span class="checkbox"><?= (strcasecmp($programa_seleccionado, 'Alimentación y Nutrición') == 0) ? 'X' : '&nbsp;' ?></span>Alimentación y Nutrición</div>
                        <div class="programa-item"><span class="checkbox"><?= (strcasecmp($programa_seleccionado, 'Apoyo a proyectos productivos') == 0) ? 'X' : '&nbsp;' ?></span>Apoyo a proyectos productivos</div>
                        <div class="programa-item"><span class="checkbox"><?= (strcasecmp($programa_seleccionado, 'Gobierno, justicia y seguridad pública') == 0) ? 'X' : '&nbsp;' ?></span>Gobierno, justicia y seguridad pública</div>
                        <div class="programa-item"><span class="checkbox"><?= (strcasecmp($programa_seleccionado, 'Política y planeación económica y social') == 0) ? 'X' : '&nbsp;' ?></span>Política y planeación económica y social</div>
                        <div class="programa-item"><span class="checkbox"><?= (strcasecmp($programa_seleccionado, 'Comercio, abasto y almacenamiento de productos básicos') == 0) ? 'X' : '&nbsp;' ?></span>Comercio, abasto y almacenamiento de productos básicos</div>
                        <div class="programa-item"><span class="checkbox"><?= (strcasecmp($programa_seleccionado, 'Desarrollo urbano') == 0) ? 'X' : '&nbsp;' ?></span>Desarrollo urbano</div>
                        <div class="programa-item"><span class="checkbox"><?= (strcasecmp($programa_seleccionado, 'Desarrollo Tecnológico') == 0) ? 'X' : '&nbsp;' ?></span>Desarrollo Tecnológico</div>
                     </div>
                </td>
            </tr>
    
            <tr><td colspan="6" style="padding-top: 5px;"><span class="label">19.- Actividades que desarrollará el prestador:</span><div style="border-bottom: 1px solid black; min-height: 25px; padding: 2px;"><?= nl2br(htmlspecialchars($solicitud['actividades'])) ?></div></td></tr>
            
            <tr>
                <td colspan="6" style="padding-top:8px;">
                    <table class="layout-table">
                        <tr>
                            <td style="width:15%;" class="label">20.- En que horario:</td>
                            <td style="width:18%;">Lunes a Viernes <span class="checkbox">X</span></td>
                            <td style="width:15%;">de <span class="data-field-center" style="display:inline-block; width: 60%;"><?= substr($solicitud['horario_lv_inicio'], 0, 5) ?></span></td>
                            <td style="width:12%;">a <span class="data-field-center" style="display:inline-block; width: 70%;"><?= substr($solicitud['horario_lv_fin'], 0, 5) ?></span></td>
                            <td style="width:25%;">Sábado, Domingo, Días Festivos <span class="checkbox">&nbsp;</span></td>
                            <td style="width:15%;">de ____ a ____</td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr>
                <td colspan="6" style="padding-top: 8px;">
                    <table class="layout-table">
                        <tr>
                            <td style="width: 22%;" class="label">21.- Periodo de Prestación:</td>
                            <td style="width: 4%;">del:</td>
                            <td style="width: 10%;"><div class="data-field-center"><?= $solicitud['periodo_inicio_dia'] ?></div></td>
                            <td style="width: 15%;"><div class="data-field-center"><?= $solicitud['periodo_inicio_mes'] ?></div></td>
                            <td style="width: 10%;"><div class="data-field-center"><?= $solicitud['periodo_inicio_anio'] ?></div></td>
                            <td style="width: 3%; text-align:center;">al:</td>
                            <td style="width: 10%;"><div class="data-field-center"><?= $solicitud['periodo_fin_dia'] ?></div></td>
                            <td style="width: 15%;"><div class="data-field-center"><?= $solicitud['periodo_fin_mes'] ?></div></td>
                            <td style="width: 10%;"><div class="data-field-center"><?= $solicitud['periodo_fin_anio'] ?></div></td>
                        </tr>
                        <tr class="sub-label"><td></td><td></td><td>Día</td><td>Mes</td><td>Año</td><td></td><td>Día</td><td>Mes</td><td>Año</td></tr>
                    </table>
                </td>
            </tr>
            
            <tr>
                <td colspan="6" style="padding-top: 8px;">
                    <span class="label">22.- Horas de duración del programa o proyecto:</span>
                    <span style="margin: 0 8px;">480 horas:</span><span class="checkbox">X</span>
                    <span style="margin: 0 8px;">Otras</span><span class="data-field" style="width: 150px; display:inline-block;"></span>
                </td>
            </tr>
            
            <tr style="height: 60px;"><td colspan="6"></td></tr>

            <tr>
                <td colspan="3" style="padding: 10px; vertical-align: bottom;">
                    <div class="firma"><?= htmlspecialchars($solicitud['nombre_empresa_firma']) ?></div>
                    <div class="firma-label">Nombre y firma</div>
                </td>
                <td colspan="3" style="padding: 10px; vertical-align: bottom;">
                    <div class="firma"><?= htmlspecialchars($solicitud['nombre_alumno_firma']) ?></div>
                    <div class="firma-label">Nombre y Firma el Prestador</div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
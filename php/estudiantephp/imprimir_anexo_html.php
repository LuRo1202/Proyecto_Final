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
    $solicitud['nombre_empresa_firma'] = $solicitud['entidad_nombre'];

    $meses_espanol = ['January' => 'ENERO', 'February' => 'FEBRERO', 'March' => 'MARZO', 'April' => 'ABRIL', 'May' => 'MAYO', 'June' => 'JUNIO', 'July' => 'JULIO', 'August' => 'AGOSTO', 'September' => 'SEPTIEMBRE', 'October' => 'OCTUBRE', 'November' => 'NOVIEMBRE', 'December' => 'DICIEMBRE'];
    
    $fecha_inicio = new DateTime($solicitud['periodo_inicio']);
    $solicitud['periodo_inicio_dia'] = $fecha_inicio->format('d');
    $solicitud['periodo_inicio_mes'] = $meses_espanol[$fecha_inicio->format('F')];
    $solicitud['periodo_inicio_anio'] = $fecha_inicio->format('Y');

    $fecha_fin = new DateTime($solicitud['periodo_fin']);
    $solicitud['periodo_fin_dia'] = $fecha_fin->format('d');
    $solicitud['periodo_fin_mes'] = $meses_espanol[$fecha_fin->format('F')];
    $solicitud['periodo_fin_anio'] = $fecha_fin->format('Y');

    // Suponemos un valor para el tipo de programa, ya que no estaba en la consulta.
    // En tu sistema real, deberás añadir `s.tipo_programa` a la consulta SQL.
    $solicitud['tipo_programa'] = $solicitud['tipo_programa'] ?? 'Apoyo a proyectos productivos';


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
        body { font-family: Arial, sans-serif; font-size: 10pt; margin: 0; }
        @page { size: letter portrait; margin: 1.5cm; }
        .container { width: 100%; }
        .main-table { border-collapse: collapse; width: 100%; border: 2px solid black; }
        .main-table td { border: 1px solid black; padding: 2px 4px; vertical-align: bottom; height: 17px; }
        .header { font-weight: bold; text-align: center; background-color: #E7E6E6; }
        .no-border { border: none !important; }
        .border-bottom { border-top: none; border-left: none; border-right: none; border-bottom: 1px solid black !important; }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        .checkbox-container { display: inline-block; margin: 0 8px; }
        .checkbox { width: 12px; height: 12px; border: 1px solid black; display: inline-block; text-align: center; line-height: 12px; font-weight: bold; vertical-align: middle; }
        .firma { border-top: 1px solid black; margin: 40px 20px 0 20px; padding-top: 5px; }
        .firma-label { text-align: center; font-weight: bold; }
        .firma-desc { text-align: center; font-size: 9pt; }

        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align:center; padding: 10px; background-color: #333; color: white;">
        <p>Para imprimir en una sola página, usa el menú de impresión (Ctrl+P) y en "Más ajustes" -> "Escala", selecciona "Ajustar al área de impresión" o "Ajustar a la página".</p>
        <button onclick="window.print()" style="padding: 5px 15px;">Imprimir</button>
        <button onclick="window.close()" style="padding: 5px 15px;">Cerrar</button>
    </div>

    <div class="container">
        <table class="main-table">
            <tr><td colspan="12" class="header">ANEXO F "SOLICITUD - REGISTRO/AUTORIZACIÓN"</td></tr>
            <tr><td colspan="12" class="header">I. DATOS DEL PRESTADOR E INSTITUCIÓN EDUCATIVA</td></tr>
            <tr>
                <td class="no-border text-bold" colspan="2">1.- Fecha:</td>
                <td class="border-bottom" colspan="10"><?php echo htmlspecialchars($solicitud['fecha_solicitud']); ?></td>
            </tr>
            <tr>
                <td class="no-border text-bold" colspan="2">2.- Nombre:</td>
                <td class="border-bottom text-center" colspan="3"><?php echo htmlspecialchars($solicitud['apellido_paterno']); ?></td>
                <td class="border-bottom text-center" colspan="4"><?php echo htmlspecialchars($solicitud['apellido_materno']); ?></td>
                <td class="border-bottom text-center" colspan="3"><?php echo htmlspecialchars($solicitud['nombre']); ?></td>
            </tr>
            <tr>
                <td class="no-border" colspan="2"></td><td class="text-center" colspan="3">Apellido Paterno</td><td class="text-center" colspan="4">Apellido Materno</td><td class="text-center" colspan="3">Nombre(s)</td>
            </tr>
            <tr>
                <td class="no-border text-bold" colspan="2">3.- Domicilio:</td>
                <td class="border-bottom" colspan="10"><?php echo htmlspecialchars($solicitud['domicilio']); ?></td>
            </tr>
            <tr>
                <td class="no-border text-bold" colspan="2">4.- Teléfono Celular:</td>
                <td class="border-bottom" colspan="4"><?php echo htmlspecialchars($solicitud['telefono']); ?></td>
                <td class="no-border text-bold">EDAD:</td>
                <td class="border-bottom text-center"><?php echo htmlspecialchars($solicitud['edad']); ?></td>
                <td class="no-border text-bold">SEXO:</td>
                <td class="border-bottom text-center" colspan="3"><?php echo htmlspecialchars($solicitud['sexo']); ?></td>
            </tr>
            <tr>
                <td class="no-border text-bold" colspan="2">5.- Correo Electrónico:</td>
                <td class="border-bottom" colspan="10"><?php echo htmlspecialchars($solicitud['correo']); ?></td>
            </tr>
            <tr>
                <td class="no-border text-bold" colspan="2">6.- Carrera:</td>
                <td class="border-bottom" colspan="10"><?php echo htmlspecialchars($solicitud['carrera']); ?></td>
            </tr>
            <tr>
                <td class="no-border text-bold" colspan="4">7.- % de créditos cubiertos:</td>
                <td class="border-bottom text-center" colspan="2"><?php echo htmlspecialchars($solicitud['porcentaje_creditos']); ?> %</td>
                <td class="no-border text-bold">Promedio:</td>
                <td class="border-bottom text-center"><?php echo htmlspecialchars($solicitud['promedio']); ?></td>
                <td class="no-border text-bold" colspan="2">8.- Facebook:</td>
                <td class="border-bottom" colspan="2"><?php echo htmlspecialchars($solicitud['facebook']); ?></td>
            </tr>
            <tr>
                <td class="no-border text-bold" colspan="2">9.- Matrícula:</td>
                <td class="border-bottom" colspan="4"><?php echo htmlspecialchars($solicitud['matricula']); ?></td>
                <td class="no-border text-bold" colspan="2">10.- CURP:</td>
                <td class="border-bottom" colspan="4"><?php echo htmlspecialchars($solicitud['curp']); ?></td>
            </tr>
            <tr><td colspan="12" class="header">II. DATOS DE LA ENTIDAD RECEPTORA</td></tr>
            <tr>
                <td class="no-border text-bold" colspan="4">5.- Nombre de la Entidad Receptora:</td>
                <td class="border-bottom" colspan="8"><?php echo htmlspecialchars($solicitud['entidad_nombre']); ?></td>
            </tr>
            <tr>
                <td class="no-border text-center" colspan="12">
                    <span class="checkbox-container">Federal <span class="checkbox"><?php echo (strcasecmp($solicitud['tipo_entidad'], 'Federal') == 0) ? 'X' : '&nbsp;'; ?></span></span>
                    <span class="checkbox-container">Estatal <span class="checkbox"><?php echo (strcasecmp($solicitud['tipo_entidad'], 'Estatal') == 0) ? 'X' : '&nbsp;'; ?></span></span>
                    <span class="checkbox-container">Municipal <span class="checkbox"><?php echo (strcasecmp($solicitud['tipo_entidad'], 'Municipal') == 0) ? 'X' : '&nbsp;'; ?></span></span>
                    <span class="checkbox-container">O.N.G. <span class="checkbox"><?php echo (strcasecmp($solicitud['tipo_entidad'], 'O.N.G.') == 0) ? 'X' : '&nbsp;'; ?></span></span>
                    <span class="checkbox-container">I.E. <span class="checkbox"><?php echo (strcasecmp($solicitud['tipo_entidad'], 'I.E.') == 0) ? 'X' : '&nbsp;'; ?></span></span>
                    <span class="checkbox-container">I.P. <span class="checkbox"><?php echo (strcasecmp($solicitud['tipo_entidad'], 'I.P.') == 0) ? 'X' : '&nbsp;'; ?></span></span>
                </td>
            </tr>
            <tr>
                <td class="no-border text-bold" colspan="5">6.- Unidad Administrativa Responsable:</td>
                <td class="border-bottom" colspan="7"><?php echo htmlspecialchars($solicitud['unidad_administrativa']); ?></td>
            </tr>
            <tr>
                <td class="no-border text-bold" colspan="3">7.- Domicilio de la Entidad:</td>
                <td class="border-bottom" colspan="9"><?php echo htmlspecialchars($solicitud['entidad_domicilio']); ?></td>
            </tr>
            <tr>
                <td class="no-border text-bold" colspan="2">8.- Municipio:</td>
                <td class="border-bottom" colspan="4"><?php echo htmlspecialchars($solicitud['municipio']); ?></td>
                <td class="no-border text-bold" colspan="2">Teléfono:</td>
                <td class="border-bottom" colspan="4"><?php echo htmlspecialchars($solicitud['entidad_telefono']); ?></td>
            </tr>
            <tr>
                <td class="no-border text-bold" colspan="5">9.- Funcionario responsable y cargo:</td>
                <td class="border-bottom" colspan="7"><?php echo htmlspecialchars($solicitud['funcionario_responsable']); ?> - <?php echo htmlspecialchars($solicitud['cargo_funcionario']); ?></td>
            </tr>
            <tr>
                <td class="no-border text-bold" colspan="5">10.- Programa en el que participará:</td>
                <td class="border-bottom" colspan="7"><?php echo htmlspecialchars($solicitud['programa_nombre']); ?></td>
            </tr>
            <tr>
                <td class="no-border text-bold" colspan="5">11.- Actividades que desarrollará:</td>
                <td class="border-bottom" colspan="7" style="height:40px;"><?php echo htmlspecialchars($solicitud['actividades']); ?></td>
            </tr>
            <tr>
                <td class="no-border text-bold" colspan="2">En que horario:</td>
                <td class="text-center" colspan="3">Lunes a Viernes</td>
                <td class="checkbox text-center">X</td>
                <td class="text-center" colspan="3">Sábado, Domingo, Días Festivos</td>
                <td class="checkbox text-center">&nbsp;</td>
                <td class="no-border" colspan="2"></td>
            </tr>
            <tr>
                <td class="no-border" colspan="4" style="text-align:right;">de: <?php echo htmlspecialchars(substr($solicitud['horario_lv_inicio'], 0, 5)); ?></td>
                <td class="no-border" colspan="2">a: <?php echo htmlspecialchars(substr($solicitud['horario_lv_fin'], 0, 5)); ?></td>
                <td class="no-border text-center" colspan="4">de: ________ a ________</td>
                <td class="no-border" colspan="2"></td>
            </tr>
            <tr>
                <td class="no-border text-bold" colspan="3">12.- Periodo de Prestación:</td>
                <td class="text-center no-border">del:</td>
                <td class="border-bottom text-center" colspan="2"><?php echo htmlspecialchars($solicitud['periodo_inicio_dia']); ?></td>
                <td class="border-bottom text-center" colspan="2"><?php echo htmlspecialchars($solicitud['periodo_inicio_mes']); ?></td>
                <td class="border-bottom text-center" colspan="2"><?php echo htmlspecialchars($solicitud['periodo_inicio_anio']); ?></td>
                <td class="text-center no-border">al:</td>
                <td class="border-bottom text-center" colspan="2">...</td>
            </tr>
            <tr>
                <td class="no-border" colspan="4"></td>
                <td class="text-center" colspan="2">Día</td><td class="text-center" colspan="2">Mes</td><td class="text-center" colspan="2">Año</td>
                <td class="no-border"></td>
                <td class="text-center" colspan="2">...</td>
            </tr>
            <tr>
                 <td class="no-border text-bold" colspan="4">13.- Horas de duración:</td>
                 <td class="no-border" colspan="2">480 horas:</td>
                 <td class="checkbox text-center" colspan="1">X</td>
                 <td class="no-border" colspan="5"></td>
            </tr>
             <tr>
                <td colspan="6" class="no-border text-center" style="height: 100px;">
                    <div class="firma"><?php echo htmlspecialchars($solicitud['nombre_empresa_firma']); ?></div>
                    <div class="firma-label">Nombre y Firma</div>
                    <div class="firma-desc">de la empresa, organización ó institución educativa</div>
                </td>
                <td colspan="6" class="no-border text-center" style="height: 100px;">
                    <div class="firma"><?php echo htmlspecialchars($solicitud['nombre_alumno_firma']); ?></div>
                    <div class="firma-label">Nombre y Firma el Prestador</div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
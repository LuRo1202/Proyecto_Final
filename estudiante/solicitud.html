<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Solicitud de Servicio Social</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-dark: #8856dd; 
            --primary-medium: #b957ca; 
            --pastel-background: #F8F5FB; 
            --text-dark: #343a40; 
            --text-light: #FFFFFF;
        }
        body { background-color: var(--pastel-background); color: var(--text-dark); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .navbar { background-color: var(--primary-dark) !important; }
        .card { border-radius: 15px; box-shadow: 0 8px 25px rgba(0,0,0,0.1); border: none; }
        .card-header { background: linear-gradient(to right, var(--primary-dark), var(--primary-medium)); color: var(--text-light); font-weight: 700; border-top-left-radius: 15px; border-top-right-radius: 15px;}
        .form-label { font-weight: 600; }
        .btn-primary { background-color: var(--primary-medium); border-color: var(--primary-medium); }
        .btn-success { background-color: #198754; border-color: #198754; }
        #alertContainer { position: fixed; top: 20px; right: 20px; z-index: 1050; width: auto; max-width: 400px; }
        .section-title { font-size: 1.2rem; font-weight: bold; color: var(--primary-dark); margin-top: 1.5rem; margin-bottom: 1rem; border-bottom: 2px solid var(--primary-medium); padding-bottom: 8px;}
    </style>
</head>
<body>
    <div id="alertContainer"></div>
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="estudiante.html"><i class="bi bi-arrow-left-circle-fill me-2"></i>Volver al Dashboard</a>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-11 col-xl-10">
                <div class="card">
                    <div class="card-header text-center"><h4 class="mb-0 py-2"><i class="bi bi-pencil-square me-2"></i>Editar Información de la Solicitud (Anexo F)</h4></div>
                    <div class="card-body p-4 p-md-5">
                        <div id="spinnerContainer" class="text-center p-5">
                            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status"></div>
                            <p class="mt-3 text-muted">Cargando tus datos...</p>
                        </div>

                        <form id="formSolicitud" class="row g-3" style="display: none;" novalidate>
                            <input type="hidden" id="solicitud_id">
                            <input type="hidden" id="entidad_id">
                            <input type="hidden" id="programa_id"> 

                            <h5 class="section-title">Datos Personales y Académicos</h5>
                            <div class="col-md-4"><label for="nombre" class="form-label">Nombre(s)</label><input type="text" class="form-control" id="nombre" required></div>
                            <div class="col-md-4"><label for="apellido_paterno" class="form-label">Apellido Paterno</label><input type="text" class="form-control" id="apellido_paterno" required></div>
                            <div class="col-md-4"><label for="apellido_materno" class="form-label">Apellido Materno</label><input type="text" class="form-control" id="apellido_materno" required></div>
                            <div class="col-md-6"><label for="matricula" class="form-label">Matrícula</label><input type="text" class="form-control" id="matricula" required></div>
                            <div class="col-md-6"><label for="curp" class="form-label">CURP</label><input type="text" class="form-control text-uppercase" id="curp" required maxlength="18"></div>
                            <div class="col-md-6"><label for="correo" class="form-label">Correo Electrónico</label><input type="email" class="form-control" id="correo" readonly disabled></div>
                            <div class="col-md-3"><label for="sexo" class="form-label">Sexo</label><select class="form-select" id="sexo" required><option value="" disabled selected>Seleccionar...</option><option value="Masculino">Masculino</option><option value="Femenino">Femenino</option><option value="Otro">Otro</option></select></div>
                            <div class="col-md-3"><label for="edad" class="form-label">Edad</label><input type="number" class="form-control" id="edad" required min="17" max="100"></div>
                            <div class="col-md-6"><label for="telefono" class="form-label">Teléfono Móvil</label><input type="tel" class="form-control" id="telefono" required maxlength="10"></div>
                            <div class="col-md-6"><label for="domicilio" class="form-label">Domicilio</label><input type="text" class="form-control" id="domicilio" required></div>
                            <div class="col-12"><label for="facebook" class="form-label">Facebook (Opcional)</label><input type="text" class="form-control" id="facebook"></div>
                            <div class="col-md-6"><label for="carrera" class="form-label">Carrera</label><select class="form-select" id="carrera" required><option value="" disabled selected>Seleccionar...</option><option value="Ingeniería en Sistemas Electrónicos">Ingeniería en Sistemas Electrónicos</option><option value="Ingeniería en Mecatrónica">Ingeniería en Mecatrónica</option><option value="Ingeniería en Tecnologías de la Información e Innovación Digital">Ingeniería en Tecnologías de la Información</option><option value="Ingeniería en Logística">Ingeniería en Logística</option><option value="Licenciatura en Administración">Licenciatura en Administración</option><option value="Licenciatura en Comercio Internacional y Aduanas">Licenciatura en Comercio Internacional y Aduanas</option></select></div>
                            <div class="col-md-6"><label for="cuatrimestre" class="form-label">Cuatrimestre</label><select class="form-select" id="cuatrimestre" required><option value="" disabled selected>Seleccionar...</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10</option></select></div>
                            <div class="col-md-6"><label for="porcentaje_creditos" class="form-label">% Créditos</label><input type="number" class="form-control" id="porcentaje_creditos" step="0.1" required></div>
                            <div class="col-md-6"><label for="promedio" class="form-label">Promedio</label><input type="number" class="form-control" id="promedio" step="0.01" required></div>

                            <h5 class="section-title">Datos del Servicio Social</h5>
                            <div class="col-md-6"><label for="fecha_solicitud" class="form-label">Fecha de Solicitud</label><input type="date" class="form-control" id="fecha_solicitud" readonly></div>
                            <div class="col-md-6"><label for="periodo_nombre" class="form-label">Período de Registro</label><input type="text" class="form-control" id="periodo_nombre" readonly></div>
                            <div class="col-md-8"><label for="entidad_nombre" class="form-label">Nombre de la Entidad Receptora</label><input type="text" class="form-control" id="entidad_nombre" required></div>
                            <div class="col-md-4"><label for="tipo_entidad" class="form-label">Tipo de Entidad</label><select class="form-select" id="tipo_entidad" name="tipo_entidad" required><option value="" disabled selected>Seleccionar...</option><option value="Federal">Federal</option><option value="Estatal">Estatal</option><option value="Municipal">Municipal</option><option value="O.N.G.">O.N.G.</option><option value="I.E.">Institución Educativa (I.E.)</option><option value="I.P.">Iniciativa Privada (I.P.)</option></select></div>
                            <div class="col-12"><label for="unidad_administrativa" class="form-label">Unidad Administrativa donde se realizará el Servicio</label><input type="text" class="form-control" id="unidad_administrativa" required></div>
                            <div class="col-12"><label for="entidad_domicilio" class="form-label">Domicilio de la Entidad</label><input type="text" class="form-control" id="entidad_domicilio" required></div>
                            <div class="col-md-6"><label for="entidad_municipio" class="form-label">Municipio de la Entidad</label><input type="text" class="form-control" id="entidad_municipio" required></div>
                            <div class="col-md-6"><label for="entidad_telefono" class="form-label">Teléfono de la Entidad</label><input type="tel" class="form-control" id="entidad_telefono" required maxlength="10"></div>
                            <div class="col-md-6"><label for="funcionario_responsable" class="form-label">Nombre del Titular de la Entidad</label><input type="text" class="form-control" id="funcionario_responsable" required></div>
                            <div class="col-md-6"><label for="cargo_funcionario" class="form-label">Cargo del Titular</label><input type="text" class="form-control" id="cargo_funcionario" required></div>
                            <div class="col-12"><label for="programa_nombre" class="form-label">Programa/Proyecto</label><select class="form-select" id="programa_nombre" name="programa_nombre" required><option value="" disabled selected>Seleccionar...</option><option value="Salud">Salud</option><option value="Educación, arte, cultura y deporte">Educación, arte, cultura y deporte</option><option value="Alimentación y Nutrición">Alimentación y Nutrición</option><option value="Vivienda">Vivienda</option><option value="Empleo y capacitación para el trabajo">Empleo y capacitación para el trabajo</option><option value="Apoyo a proyectos productivos">Apoyo a proyectos productivos</option><option value="Grupos vulnerables con capacidades diferentes, infantes y tercera edad">Grupos vulnerables</option><option value="Gobierno, justicia y seguridad pública">Gobierno, justicia y seguridad pública</option><option value="Pueblos indígenas">Pueblos indígenas</option><option value="Derechos humanos">Derechos humanos</option><option value="Política y planeación económica y social">Política y planeación</option><option value="Infraestructura hidráulica y de saneamiento">Infraestructura</option><option value="Comercio, abasto y almacenamiento de productos básicos">Comercio y abasto</option><option value="Asistencia y seguridad social">Asistencia y seguridad social</option><option value="Medio ambiente">Medio ambiente</option><option value="Desarrollo urbano">Desarrollo urbano</option><option value="Desarrollo Tecnológico">Desarrollo Tecnológico</option></select></div>
                            <div class="col-12"><label for="actividades" class="form-label">Actividades a Desarrollar</label><textarea class="form-control" id="actividades" rows="3" required></textarea></div>
                            <div class="col-md-6"><label for="periodo_inicio" class="form-label">Fecha de Inicio</label><input type="date" class="form-control" id="periodo_inicio" required></div>
                            <div class="col-md-6"><label for="periodo_fin" class="form-label">Fecha de Terminación</label><input type="date" class="form-control" id="periodo_fin" required></div>
                            <div class="col-md-6"><label class="form-label">Horario L-V</label><div class="input-group"><input type="time" class="form-control" id="horario_lv_inicio"><span class="input-group-text">a</span><input type="time" class="form-control" id="horario_lv_fin"></div></div>
                            <div class="col-md-6"><label class="form-label">Horario S-D</label><div class="input-group"><input type="time" class="form-control" id="horario_sd_inicio"><span class="input-group-text">a</span><input type="time" class="form-control" id="horario_sd_fin"></div></div>
                            
                            <div class="col-12 mt-4 pt-3 text-center border-top">
                                <button type="button" id="btnGuardar" class="btn btn-primary btn-lg mx-2"><i class="bi bi-save-fill me-2"></i>Guardar Cambios</button>
                                <button type="button" id="btnGenerar" class="btn btn-success btn-lg mx-2"><i class="bi bi-printer-fill me-2"></i>Imprimir Solicitud</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/estudiantejs/solicitud.js"></script>
</body>
</html>
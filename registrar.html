<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Solicitud - Servicio Social</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-dark: #8E7CC3;
            --primary-medium: #AEA0D8;
            --primary-light: #D4C9EC;
            --green-action: #6ACC6E;
            --green-hover: #5BAD60;
            --text-dark: #343a40;
            --text-light: #FFFFFF;
            --text-muted: #6c757d;
            --border-light: #dee2e6;
            --pastel-background: #F8F5FB;
        }

        body {
            background-color: var(--pastel-background);
            font-family: 'Segoe UI', Roboto, sans-serif;
            color: var(--text-dark);
        }

        .main-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 2rem 0;
        }

        .card {
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            border-radius: 15px;
            border: none;
            background-color: var(--text-light);
            overflow: hidden;
        }

        .card-body h3 {
            color: var(--primary-dark);
            font-weight: 700;
        }

        #progress-bar-container {
            padding: 1.5rem 2rem;
            background-color: #fdfcff;
        }

        .progress-bar-steps {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            justify-content: space-between;
        }

        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            flex-grow: 1;
            text-align: center;
        }

        .step-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-light);
            color: var(--primary-dark);
            border: 2px solid var(--primary-light);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            font-weight: bold;
            transition: all 0.4s ease;
            z-index: 2;
        }

        .step-label {
            margin-top: 0.5rem;
            font-size: 0.85rem;
            color: var(--text-muted);
            transition: all 0.4s ease;
            font-weight: 500;
        }

        .step:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 20px;
            left: 50%;
            width: 100%;
            height: 2px;
            background-color: var(--primary-light);
            z-index: 1;
            transform: translateY(-50%);
        }

        .step.active .step-icon {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
            color: var(--text-light);
            transform: scale(1.1);
        }

        .step.active .step-label {
            color: var(--primary-dark);
            font-weight: 600;
        }

        .step.completed .step-icon {
            background-color: var(--green-action);
            border-color: var(--green-action);
            color: var(--text-light);
        }

        .step.completed:not(:last-child)::after {
            background-color: var(--green-action);
        }

        .form-step {
            display: none;
            animation: fadeIn 0.5s ease;
        }

        .form-step.active {
            display: block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-medium);
            box-shadow: 0 0 0 0.25rem rgba(174, 160, 216, 0.25);
        }

        .btn-primary {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }

        .btn-primary:hover {
            background-color: var(--primary-medium);
            border-color: var(--primary-medium);
        }

        .btn-secondary {
            background-color: #f0f0f0;
            border-color: #ddd;
            color: var(--text-dark);
        }

        .alert-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
            width: 90%;
            max-width: 380px;
        }

        #step-1-content {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        #step-1-content .form-group-centered {
            width: 100%;
            max-width: 350px;
            margin-bottom: 1rem;
        }

        #step-1-content .form-control {
            font-size: 0.95rem;
            padding: 0.6rem 0.75rem;
        }

        .password-input-group {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--text-muted);
        }

        .toggle-password:hover {
            color: var(--primary-dark);
        }

        .only-letters {
            text-transform: capitalize;
        }

        .spinner-border {
            vertical-align: middle;
        }

        .valid-feedback {
            display: none;
            color: var(--green-action);
        }

        .is-valid + .valid-feedback {
            display: block;
        }

        .is-valid {
            border-color: var(--green-action) !important;
        }
    </style>
</head>
<body>
    <div id="alertContainer" class="alert-container"></div>
    <div class="container main-container">
        <div class="row justify-content-center w-100">
            <div class="col-md-10 col-lg-9">
                <div class="card">
                    <div id="progress-bar-container">
                        <ol class="progress-bar-steps">
                            <li class="step active" data-step="1">
                                <div class="step-icon"><i class="bi bi-person-badge"></i></div>
                                <div class="step-label">Acceso</div>
                            </li>
                            <li class="step" data-step="2">
                                <div class="step-icon"><i class="bi bi-person-vcard"></i></div>
                                <div class="step-label">Estudiante</div>
                            </li>
                            <li class="step" data-step="3">
                                <div class="step-icon"><i class="bi bi-building"></i></div>
                                <div class="step-label">Entidad</div>
                            </li>
                            <li class="step" data-step="4">
                                <div class="step-icon"><i class="bi bi-file-earmark-text"></i></div>
                                <div class="step-label">Programa</div>
                            </li>
                        </ol>
                    </div>

                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-4">
                            <h3>Formulario de Registro</h3>
                            <p class="text-muted" id="form-step-title">Paso 1: Datos de Acceso</p>
                        </div>

                        <form id="formSolicitud" novalidate>
                            <!-- Paso 1 -->
                            <div class="form-step active" id="step-1">
                                <div id="step-1-content">
                                    <div class="mb-3 form-group-centered">
                                        <label for="correo" class="form-label">Correo Electrónico <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="correo" name="correo" required
                                               placeholder="ejemplo@uptex.edu.mx">
                                        <div class="invalid-feedback">
                                            Por favor, introduce un correo electrónico válido.
                                        </div>
                                        <div class="valid-feedback">
                                            Correo electrónico disponible.
                                        </div>
                                    </div>
                                    <div class="mb-3 form-group-centered password-input-group">
                                        <label for="contrasena" class="form-label">Contraseña <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control" id="contrasena" name="contrasena" required minlength="6">
                                        <span class="toggle-password" id="togglePassword">
                                            <i class="bi bi-eye"></i>
                                        </span>
                                        <div class="invalid-feedback">
                                            La contraseña es obligatoria y debe tener al menos 6 caracteres.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Paso 2 -->
                            <div class="form-step" id="step-2">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="nombre" class="form-label">Nombre(s) <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control only-letters" id="nombre" name="nombre" required pattern="[A-Za-záéíóúÁÉÍÓÚñÑ ]+">
                                        <div class="invalid-feedback">Solo se permiten letras y espacios.</div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="apellido_paterno" class="form-label">Apellido Paterno <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control only-letters" id="apellido_paterno" name="apellido_paterno" required pattern="[A-Za-záéíóúÁÉÍÓÚñÑ ]+">
                                        <div class="invalid-feedback">Solo se permiten letras y espacios.</div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="apellido_materno" class="form-label">Apellido Materno <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control only-letters" id="apellido_materno" name="apellido_materno" required pattern="[A-Za-záéíóúÁÉÍÓÚñÑ ]+">
                                        <div class="invalid-feedback">Solo se permiten letras y espacios.</div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="matricula" class="form-label">Matrícula <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control only-numbers" id="matricula" name="matricula" required>
                                        <div class="invalid-feedback">Este campo es obligatorio.</div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="curp" class="form-label">CURP <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="curp" name="curp" required pattern="[A-Z]{4}[0-9]{6}[H,M][A-Z]{5}[0-9,A-Z]{2}" maxlength="18">
                                        <div class="invalid-feedback">Formato de CURP inválido. (Ej: ABCD123456HABCDE01)</div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="domicilio" class="form-label">Domicilio Completo <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="domicilio" name="domicilio" required placeholder="Calle, Número, Colonia, C.P., Ciudad">
                                    <div class="invalid-feedback">Este campo es obligatorio.</div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label for="telefono" class="form-label">Teléfono Móvil <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control only-numbers" id="telefono" name="telefono" required pattern="[0-9]{10}" maxlength="10">
                                        <div class="invalid-feedback">Por favor, introduce un teléfono válido (10 dígitos).</div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="edad" class="form-label">Edad <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="edad" name="edad" required min="17" max="100">
                                        <div class="invalid-feedback">Por favor, introduce una edad válida.</div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="sexo" class="form-label">Sexo <span class="text-danger">*</span></label>
                                        <select class="form-select" id="sexo" name="sexo" required>
                                            <option value="">Seleccionar...</option>
                                            <option value="Masculino">Masculino</option>
                                            <option value="Femenino">Femenino</option>
                                            <option value="Otro">Otro</option>
                                        </select>
                                        <div class="invalid-feedback">Este campo es obligatorio.</div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="facebook" class="form-label">Facebook</label>
                                        <input type="text" class="form-control" id="facebook" name="facebook" placeholder="Usuario o enlace">
                                        <div class="invalid-feedback">Este campo es opcional.</div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="carrera" class="form-label">Carrera <span class="text-danger">*</span></label>
                                        <select class="form-select" id="carrera" name="carrera" required>
                                            <option value="">Seleccione una carrera</option>
                                            <option value="Ingeniería en Sistemas Electrónicos">Ingeniería en Sistemas Electrónicos</option>
                                            <option value="Ingeniería en Mecatrónica">Ingeniería en Mecatrónica</option>
                                            <option value="Ingeniería en Tecnologías de la Información e Innovación Digital">Ingeniería en Tecnologías de la Información e Innovación Digital</option>
                                            <option value="Ingeniería en Logística">Ingeniería en Logística</option>
                                            <option value="Licenciatura en Administración">Licenciatura en Administración</option>
                                            <option value="Licenciatura en Comercio Internacional y Aduanas">Licenciatura en Comercio Internacional y Aduanas</option>
                                        </select>
                                        <div class="invalid-feedback">Este campo es obligatorio.</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="cuatrimestre" class="form-label">Cuatrimestre <span class="text-danger">*</span></label>
                                        <select class="form-select" id="cuatrimestre" name="cuatrimestre" required>
                                            <option value="">Seleccionar...</option>
                                            <option value="7">7° Cuatrimestre</option>
                                            <option value="8">8° Cuatrimestre</option>
                                            <option value="9">9° Cuatrimestre</option>
                                            <option value="10">10° Cuatrimestre</option>
                                        </select>
                                        <div class="invalid-feedback">Este campo es obligatorio.</div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="porcentaje_creditos" class="form-label">% Créditos Cubiertos <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="porcentaje_creditos" name="porcentaje_creditos" min="60" max="100" step="0.1" required>
                                        <div class="invalid-feedback">El porcentaje debe ser entre 60 y 100.</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="promedio" class="form-label">Promedio General <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="promedio" name="promedio" min="0" max="10" step="0.01" required>
                                        <div class="invalid-feedback">El promedio debe ser entre 0 y 10.</div>
                                    </div>
                                </div>
                            </div>
                            <!-- Paso 3 -->
                            <div class="form-step" id="step-3">
                                <div class="mb-3">
                                    <label for="entidad_nombre" class="form-label">Nombre de la Entidad <span class="text-danger">*</span></label>
                                    <select class="form-select" id="entidad_nombre" name="entidad_nombre" required>
                                        <option value="">Seleccionar entidad...</option>
                                        <option value="Universidad Politécnica de Texcoco">Universidad Politécnica de Texcoco</option>
                                        <option value="Otra">Otra entidad...</option>
                                    </select>
                                    <div class="invalid-feedback">Este campo es obligatorio.</div>
                                </div>
                                <div id="otra-entidad-container" style="display: none;">
                                    <div class="mb-3">
                                        <label for="otra_entidad_nombre" class="form-label">Nombre de la Otra Entidad <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="otra_entidad_nombre" name="otra_entidad_nombre">
                                        <div class="invalid-feedback">Por favor ingrese el nombre de la entidad.</div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="tipo_entidad" class="form-label">Tipo de Entidad <span class="text-danger">*</span></label>
                                        <select class="form-select" id="tipo_entidad" name="tipo_entidad" required>
                                            <option value="">Seleccionar...</option>
                                            <option value="Federal">Federal</option>
                                            <option value="Estatal">Estatal</option>
                                            <option value="Municipal">Municipal</option>
                                            <option value="O.N.G.">O.N.G.</option>
                                            <option value="I.E.">Institución Educativa (I.E.)</option>
                                            <option value="I.P.">Iniciativa Privada (I.P.)</option>
                                        </select>
                                        <div class="invalid-feedback">Este campo es obligatorio.</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="unidad_administrativa" class="form-label">Unidad Administrativa <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="unidad_administrativa" name="unidad_administrativa" required>
                                        <div class="invalid-feedback">Este campo es obligatorio.</div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="entidad_domicilio" class="form-label">Domicilio de la Unidad <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="entidad_domicilio" name="entidad_domicilio" required>
                                    <div class="invalid-feedback">Este campo es obligatorio.</div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="municipio" class="form-label">Municipio <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="municipio" name="municipio" required>
                                        <div class="invalid-feedback">Este campo es obligatorio.</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="entidad_telefono" class="form-label">Teléfono de la Entidad <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control only-numbers" id="entidad_telefono" name="entidad_telefono" required pattern="[0-9]{10}" maxlength="10">
                                        <div class="invalid-feedback">Por favor, introduce un teléfono válido (10 dígitos).</div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="funcionario_responsable" class="form-label">Funcionario Responsable <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control only-letters" id="funcionario_responsable" name="funcionario_responsable" required pattern="[A-Za-záéíóúÁÉÍÓÚñÑ ]+">
                                        <div class="invalid-feedback">Solo se permiten letras y espacios.</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="cargo_funcionario" class="form-label">Cargo del Funcionario <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="cargo_funcionario" name="cargo_funcionario" required>
                                        <div class="invalid-feedback">Este campo es obligatorio.</div>
                                    </div>
                                </div>
                            </div>
                            <!-- Paso 4 -->
                            <div class="form-step" id="step-4">
                                <div class="mb-3">
                                    <label for="programa_nombre" class="form-label">Programa/Proyecto <span class="text-danger">*</span></label>
                                    <select class="form-select" id="programa_nombre" name="programa_nombre" required>
                                        <option value="">Seleccionar programa...</option>
                                        <option value="Salud">Salud</option>
                                        <option value="Educación, arte, cultura y deporte">Educación, arte, cultura y deporte</option>
                                        <option value="Alimentación y Nutrición">Alimentación y Nutrición</option>
                                        <option value="Vivienda">Vivienda</option>
                                        <option value="Empleo y capacitación para el trabajo">Empleo y capacitación para el trabajo</option>
                                        <option value="Apoyo a proyectos productivos">Apoyo a proyectos productivos</option>
                                        <option value="Grupos vulnerables con capacidades diferentes, infantes y tercera edad">Grupos vulnerables con capacidades diferentes, infantes y tercera edad</option>
                                        <option value="Gobierno, justicia y seguridad pública">Gobierno, justicia y seguridad pública</option>
                                        <option value="Pueblos indígenas">Pueblos indígenas</option>
                                        <option value="Derechos humanos">Derechos humanos</option>
                                        <option value="Política y planeación económica y social">Política y planeación económica y social</option>
                                        <option value="Infraestructura hidráulica y de saneamiento">Infraestructura hidráulica y de saneamiento</option>
                                        <option value="Comercio, abasto y almacenamiento de productos básicos">Comercio, abasto y almacenamiento de productos básicos</option>
                                        <option value="Asistencia y seguridad social">Asistencia y seguridad social</option>
                                        <option value="Medio ambiente">Medio ambiente</option>
                                        <option value="Desarrollo urbano">Desarrollo urbano</option>
                                        <option value="Desarrollo Tecnológico">Desarrollo Tecnológico</option>
                                        <option value="Otro">Otro programa...</option>
                                    </select>
                                    <div class="invalid-feedback">Este campo es obligatorio.</div>
                                </div>
                                <div id="otro-programa-container" style="display: none;">
                                    <div class="mb-3">
                                        <label for="otro_programa_nombre" class="form-label">Nombre del Otro Programa <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="otro_programa_nombre" name="otro_programa_nombre">
                                        <div class="invalid-feedback">Por favor ingrese el nombre del programa.</div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="periodo_registro_id" class="form-label">Período de Registro <span class="text-danger">*</span></label>
                                    <select class="form-select" id="periodo_registro_id" name="periodo_registro_id" required>
                                        <option value="" disabled selected>Cargando períodos...</option>
                                    </select>
                                    <div class="invalid-feedback">Por favor selecciona un período de registro.</div>
                                </div>
                                <div class="mb-3">
                                    <label for="actividades" class="form-label">Actividades que Desarrollará <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="actividades" name="actividades" rows="3" required></textarea>
                                    <div class="invalid-feedback">Este campo es obligatorio.</div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Horario Lunes a Viernes</label>
                                        <div class="input-group">
                                            <input type="time" class="form-control" name="horario_lv_inicio" id="horario_lv_inicio">
                                            <span class="input-group-text">a</span>
                                            <input type="time" class="form-control" name="horario_lv_fin" id="horario_lv_fin">
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Horario Sábado y Domingo</label>
                                        <div class="input-group">
                                            <input type="time" class="form-control" name="horario_sd_inicio" id="horario_sd_inicio">
                                            <span class="input-group-text">a</span>
                                            <input type="time" class="form-control" name="horario_sd_fin" id="horario_sd_fin">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="periodo_inicio" class="form-label">Fecha de Inicio del Servicio <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="periodo_inicio" name="periodo_inicio" required>
                                        <div class="invalid-feedback">Este campo es obligatorio.</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="periodo_fin" class="form-label">Fecha de Fin del Servicio <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="periodo_fin" name="periodo_fin" required>
                                        <div class="invalid-feedback">Este campo es obligatorio.</div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-5">
                                <button type="button" class="btn btn-secondary" id="btn-prev" style="display: none;">
                                    <i class="bi bi-arrow-left"></i> Anterior
                                </button>
                                <button type="button" class="btn btn-primary" id="btn-next">
                                    Siguiente <i class="bi bi-arrow-right"></i>
                                </button>
                                <button type="submit" class="btn btn-primary" id="btn-submit" style="display: none;">
                                    <i class="bi bi-check-circle"></i> Enviar Solicitud
                                </button>
                            </div>
                        </form>

                        <p class="text-center mt-3 mb-0">
                            ¿Ya tienes cuenta? <a href="index.html" class="text-decoration-none">Inicia Sesión aquí</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/registro.js"></script>
</body>
</html>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes por Alumno - Servicio Social</title>
    <!-- Librerías CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    <!-- Estilos personalizados -->
    <style>
        :root {
            --sidebar-bg: #2c3e50;
            --sidebar-link-color: rgba(255, 255, 255, 0.8);
            --sidebar-link-hover-active: #ffffff;
            --sidebar-link-bg-hover-active: rgba(255, 255, 255, 0.1);
            --sidebar-active-border: #8856dd;
            --sidebar-active-bg: #8856dd;
            --primary-dark: #8856dd;
            --primary-light: #ece0ff;
        }
        body { background-color: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
        .sidebar { min-height: 100vh; background-color: var(--sidebar-bg); }
        .sidebar .nav-link { color: var(--sidebar-link-color) !important; padding: 0.75rem 1rem; border-radius: 8px; transition: all 0.3s ease; }
        .sidebar .nav-link:hover { color: var(--sidebar-link-hover-active) !important; background-color: var(--sidebar-link-bg-hover-active); }
        .sidebar .nav-link.active { color: var(--sidebar-link-hover-active) !important; background-color: var(--sidebar-active-bg); border-left: 5px solid var(--sidebar-active-border); padding-left: calc(1rem - 5px); }
        .user-info { padding: 1rem; color: #ffffff; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .user-info #user-role { background-color: rgba(255,255,255,0.2); font-size: 0.85rem; padding: 0.4rem 0.8rem; }
        
        /* Estilos para el tema morado */
        .card-header { background-color: var(--primary-dark); color: white; font-weight: 600; }
        .table-hover > tbody > tr:hover { background-color: var(--primary-light) !important; cursor: pointer; }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover { background: var(--primary-dark) !important; color: white !important; border-color: var(--primary-dark) !important; }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover { background: var(--primary-light) !important; color: #333 !important; border-color: var(--primary-light) !important; }
        .progress-bar { background-color: var(--primary-dark); }
        .student-info-card { background-color: #f8f9fa; }
    </style>
</head>
<body>

    <!-- Barra lateral -->
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse show">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <img src="../imagenes/data.png" alt="Logo" class="img-fluid" style="max-height: 90px;">
                        <h5 class="text-white mt-2">Servicio Social</h5>
                    </div>
                    <div class="user-info">
                        <div id="user-email" >Cargando...</div>
                        <span id="user-role" class="badge">Administrador</span>
                    </div>
                    <ul class="nav flex-column px-3 mt-3">
                        <li class="nav-item"><a class="nav-link" href="admin.html"><i class="bi bi-speedometer2 me-2"></i>Inicio</a></li>
                        <li class="nav-item"><a class="nav-link" href="asignacion_estudiantes.html"><i class="bi bi-people-fill me-2"></i>Asignar Estudiantes</a></li>
                        <li class="nav-item"><a class="nav-link" href="gestion_estudiantes.html"><i class="bi bi-person-vcard me-2"></i>Gestión Estudiantes</a></li>
                        <li class="nav-item"><a class="nav-link" href="gestion_responsables.html"><i class="bi bi-person-badge me-2"></i>Gestión Responsables</a></li>
                        <li class="nav-item"><a class="nav-link" href="gestion_administradores.html"><i class="bi bi-person-gear me-2"></i>Gestión Administradores</a></li>
                        <li class="nav-item"><a class="nav-link" href="gestion_vinculacion.html"><i class="bi bi-person-circle me-2"></i>Gestión Personal de Vinculación</a></li>
                        <li class="nav-item"><a class="nav-link" href="registro_horas.html"><i class="bi bi-clock-history me-2"></i>Registro Horas</a></li>
                        <li class="nav-item"><a class="nav-link active" href="reportes.html"><i class="bi bi-graph-up me-2"></i>Reportes</a></li>
                        <li class="nav-item"><a class="nav-link" href="gestion_periodos.html"><i class="bi bi-calendar-event me-2"></i>Gestionar Períodos</a></li>
                        <li class="nav-item mt-4"><a class="nav-link" href="perfil.html"><i class="bi bi-person-circle me-2"></i>Mi Perfil</a></li>
                        
                    </ul>
                </div>
            </nav>

            <!-- Contenido principal -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Reportes por Alumno</h1>
                </div>

                <!-- Contenedor de la lista de estudiantes -->
                <div id="student-list-container">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5>Seleccionar Estudiante para ver Reporte</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="tablaEstudiantesReportes" class="table table-striped table-hover" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Matrícula</th><th>Nombre Completo</th><th>Carrera</th><th>Horas / Progreso</th><th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contenedor del reporte individual -->
                <div id="student-report-container" style="display: none;">
                    <div class="card student-info-card shadow-sm mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start flex-wrap">
                                <div>
                                    <h4 id="student-name"></h4>
                                    <p class="mb-1"><strong>Matrícula:</strong> <span id="student-matricula"></span></p>
                                    <p class="mb-1"><strong>Carrera:</strong> <span id="student-carrera"></span></p>
                                    <p class="mb-1"><strong>Responsable:</strong> <span id="student-responsable"></span></p>
                                </div>
                                <div class="text-center my-2 my-md-0" style="min-width: 200px;">
                                    <h6 class="mb-2">Progreso General</h6>
                                    <div class="progress" style="height: 25px;"><div id="student-progress-bar" class="progress-bar" role="progressbar" style="width: 0%;">0%</div></div>
                                    <span id="student-progress-text" class="form-text mt-1">0 de 480 horas</span>
                                </div>
                                <button id="btn-back-to-list" class="btn btn-secondary align-self-start"><i class="bi bi-arrow-left-circle me-2"></i>Volver</button>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4"><div class="card h-100 shadow-sm"><div class="card-body"><canvas id="horasSemanalesChart"></canvas></div></div></div>
                        <div class="col-md-6 mb-4"><div class="card h-100 shadow-sm"><div class="card-body"><canvas id="horasMensualesChart"></canvas></div></div></div>
                        <div class="col-12">
                            <div class="card h-100 shadow-sm">
                                <div class="card-header"><h5>Últimos 10 Registros Aprobados</h5></div>
                                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                                    <table class="table table-sm table-hover">
                                        <thead><tr><th>Fecha</th><th>Horas</th><th>Estado</th><th>Responsable</th></tr></thead>
                                        <tbody id="recent-registros-table"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Librerías JS -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../js/adminjs/reportes.js"></script>
</body>
</html>

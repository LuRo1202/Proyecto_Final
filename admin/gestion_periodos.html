<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Períodos - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-bg: #2c3e50;
            --sidebar-link-color: rgba(255, 255, 255, 0.8);
            --sidebar-link-hover-active: #ffffff;
            --sidebar-link-bg-hover-active: rgba(136, 86, 221, 0.2);
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
        .sidebar .nav-link.text-danger:hover { background-color: rgba(220, 53, 69, 0.2); }
        .user-info { padding: 1rem; color: #ffffff; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .user-info #user-role { background-color: rgba(255,255,255,0.2); font-size: 0.85rem; padding: 0.4rem 0.8rem; }
        .card-header { background-color: var(--primary-dark); color: white; font-weight: 600; }
        .table-hover > tbody > tr:hover { background-color: var(--primary-light) !important; cursor: pointer; }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover { background: var(--primary-dark) !important; color: white !important; border-color: var(--primary-dark) !important; }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover { background: var(--primary-light) !important; color: #333 !important; border-color: var(--primary-light) !important; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse show">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <img src="../imagenes/data.png" alt="Logo" class="img-fluid" style="max-height: 90px;">
                        <h5 class="text-white mt-2">Servicio Social</h5>
                    </div>
                    <div class="user-info">
                        <div id="user-email">Cargando...</div>
                        <span id="user-role" class="badge rounded-pill">Administrador</span>
                    </div>
                    <ul class="nav flex-column px-3 mt-3">
                        <li class="nav-item"><a class="nav-link" href="admin.html"><i class="bi bi-speedometer2 me-2"></i>Inicio</a></li>
                        <li class="nav-item"><a class="nav-link" href="asignacion_estudiantes.html"><i class="bi bi-people-fill me-2"></i>Asignar Estudiantes</a></li>
                        <li class="nav-item"><a class="nav-link" href="gestion_estudiantes.html"><i class="bi bi-person-vcard me-2"></i>Gestión Estudiantes</a></li>
                        <li class="nav-item"><a class="nav-link" href="gestion_responsables.html"><i class="bi bi-person-badge me-2"></i>Gestión Responsables</a></li>
                        <li class="nav-item"><a class="nav-link" href="gestion_administradores.html"><i class="bi bi-person-gear me-2"></i>Gestión Administradores</a></li>
                        <li class="nav-item"><a class="nav-link" href="gestion_vinculacion.html"><i class="bi bi-person-circle me-2"></i>Gestión Personal de Vinculación</a></li>
                        <li class="nav-item"><a class="nav-link" href="registro_horas.html"><i class="bi bi-clock-history me-2"></i>Registro Horas</a></li>
                        <li class="nav-item"><a class="nav-link" href="reportes.html"><i class="bi bi-graph-up me-2"></i>Reportes</a></li>
                        <li class="nav-item"><a class="nav-link active" href="gestion_periodos.html"><i class="bi bi-calendar-event me-2"></i>Gestionar Períodos</a></li>
                        <li class="nav-item mt-4"><a class="nav-link" href="perfil.html"><i class="bi bi-person-circle me-2"></i>Mi Perfil</a></li>
                        <li class="nav-item"><a class="nav-link text-danger" id="btn-logout" href="#"><i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión</a></li>
                    </ul>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Gestionar Períodos de Registro</h1>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-calendar-plus-fill me-2"></i>Crear Nuevo Período</h5>
                    </div>
                    <div class="card-body">
                        <form id="form-crear-periodo" novalidate>
                            <input type="hidden" name="action" value="crear">
                            <div class="row align-items-end">
                                <div class="col-md-4 mb-3"><label for="nombre" class="form-label">Nombre del Período*</label><input type="text" class="form-control" id="nombre" name="nombre" placeholder="Ej: Marzo - Septiembre 2025" required></div>
                                <div class="col-md-3 mb-3"><label for="fecha_inicio" class="form-label">Fecha de Inicio*</label><input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required></div>
                                <div class="col-md-3 mb-3"><label for="fecha_fin" class="form-label">Fecha de Fin*</label><input type="date" class="form-control" id="fecha_fin" name="fecha_fin" required></div>
                                <div class="col-md-2 mb-3"><button type="submit" class="btn btn-primary w-100"><i class="bi bi-plus-lg"></i> Crear</button></div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header"><h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Historial de Períodos</h5></div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="tablaPeriodos" class="table table-striped table-hover" style="width:100%">
                                <thead>
                                    <tr><th>Nombre</th><th>Fecha Inicio</th><th>Fecha Fin</th><th>Estado</th><th class="text-center">Acciones</th></tr>
                                </thead>
                                <tbody id="tabla-periodos-body"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../js/adminjs/gestion_periodos.js"></script>
</body>
</html>
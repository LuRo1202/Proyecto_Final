<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel del Encargado - Estudiantes a Cargo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        :root {
            --primary-dark: #8856dd;
            --primary-medium: #8867c2;
            --primary-light: #D1C4E9;
            --green-action: #4CAF50;
            --green-hover: #43A047;
            --red-action: #F44336;
            --red-hover: #E53935;
            --text-dark: #343a40;
            --text-light: #FFFFFF;
            --text-muted: #6c757d;
            --border-light: #dee2e6;
            --pastel-background: #F8F5FB;
            --status-success: #28a745; 
            --status-danger: #dc3545;
            --status-warning: #ffc107;
            --status-info: #17a2b8;
        }
        body { background-color: var(--pastel-background); font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; color: var(--text-dark); }
        .sidebar { min-height: 100vh; background-color: #2c3e50; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .sidebar .nav-link { color: rgba(255, 255, 255, 0.8) !important; font-weight: 600; padding: 0.75rem 1rem; border-radius: 8px; transition: background-color 0.3s ease, color 0.3s ease, border-left 0.3s ease; display: block; }
        .sidebar .nav-link:hover { color: #ffffff !important; background-color: rgba(255, 255, 255, 0.1); }
        .sidebar .nav-link.active { color: #ffffff !important; background-color: #8856dd; border-left: 5px solid #8867c2; padding-left: calc(1rem - 5px); }
        .user-info { padding: 1rem; color: #ffffff; text-align: center; border-bottom: 1px solid rgba(255, 255, 255, 0.1); }
        .user-info .user-name { font-size: 1.05rem; font-weight: 500; color: #ffffff; margin-bottom: 0.4rem; min-height: 1.2rem; }
        .user-info .user-role-badge { display: inline-block; background-color: #4A5A6A; color: #ffffff; padding: 0.4rem 1.2rem; border-radius: 1rem; font-size: 0.9rem; font-weight: 500; }
        .sidebar .nav-link.text-danger:hover { color: #ffc107 !important; }
        .text-center.mb-4 img { max-height: 100px !important; }
        .card { box-shadow: 0 6px 12px rgba(0,0,0,0.1); border-radius: 12px; overflow: hidden; border: none; background-color: var(--text-light); }
        .card-header, .modal-header { font-weight: 700; background-color: var(--primary-dark); color: var(--text-light); padding: 1rem 1.5rem; border-bottom: 1px solid rgba(0,0,0,0.125); display: flex; align-items: center; }
        .modal-title { color: var(--text-light); }
        .btn-close { filter: invert(1); }
        .progress { height: 25px; border-radius: 8px; background-color: var(--primary-light); }
        .progress-bar { font-size: 0.9rem; line-height: 25px; background-color: var(--green-action) !important; color: var(--text-light) !important; font-weight: bold; transition: width 1s ease-in-out; border-radius: 8px; }
        .table th { font-weight: 600; background-color: var(--primary-light); color: var(--text-dark); vertical-align: middle; font-size: 1rem; padding: 1rem; }
        .table td { vertical-align: middle; color: var(--text-dark); padding: 0.75rem 1rem; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <img src="../imagenes/data.png" alt="Logo" class="img-fluid" style="max-height: 100px;">
                        <h5 class="text-white mt-2">Servicio Social</h5>
                    </div>

                    <div class="user-info text-center">
                        <div id="user-name" class="user-name">Cargando...</div>
                        <div class="user-role-badge">Encargado</div>
                    </div>
                    
                    <ul class="nav flex-column px-3 mt-3">
                        <li class="nav-item">
                            <a class="nav-link" href="encargado.html">
                                <i class="bi bi-clock-history me-2"></i>Validar Horas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="estudiantes.html">
                                <i class="bi bi-people me-2"></i>Estudiantes 
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="encargado_reportes.html">
                                <i class="bi bi-graph-up me-2"></i>Reportes
                            </a>
                        </li>
                        <li class="nav-item mt-4">
                            <a class="nav-link text-danger" href="../php/cerrar_sesion.php" onclick="cerrarSesion()">
                                <i class="bi bi-box-arrow-left me-2"></i>Cerrar Sesión
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Estudiantes a mi Cargo</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button id="btnExportarExcel" class="btn btn-sm btn-success">
                            <i class="bi bi-file-excel me-1"></i> Exportar a Excel
                        </button>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-body">
                        <form id="filtroForm">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="filtroCarrera" class="form-label">Carrera</label>
                                    <select id="filtroCarrera" class="form-select">
                                        <option value="">Todas</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="filtroCuatrimestre" class="form-label">Cuatrimestre</label>
                                    <select id="filtroCuatrimestre" class="form-select">
                                        <option value="">Todos</option>
                                        <option value="7">7°</option>
                                        <option value="8">8°</option>
                                        <option value="9">9°</option>
                                        <option value="10">10°</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="filtroEstudiante" class="form-label">Buscar Estudiante</label>
                                    <div class="input-group">
                                        <input type="text" id="filtroEstudiante" class="form-control" placeholder="Nombre o matrícula...">
                                        <button class="btn btn-outline-secondary" type="button" id="btnBuscar"><i class="bi bi-search"></i></button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="tablaEstudiantes" class="table table-striped table-hover" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Matrícula</th>
                                        <th>Nombre</th>
                                        <th>Carrera</th>
                                        <th>Cuatrimestre</th>
                                        <th>Horas Completadas</th>
                                        <th>Progreso</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <div class="modal fade" id="modalDetalleEstudiante" tabindex="-1" aria-labelledby="modalDetalleEstudianteLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDetalleEstudianteLabel">Detalles del Estudiante</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Información Personal</h6><hr>
                            <p><strong>Nombre:</strong> <span id="detalleNombre"></span></p>
                            <p><strong>Matrícula:</strong> <span id="detalleMatricula"></span></p>
                            <p><strong>Carrera:</strong> <span id="detalleCarrera"></span></p>
                            <p><strong>Cuatrimestre:</strong> <span id="detalleCuatrimestre"></span></p>
                            <p><strong>Teléfono:</strong> <span id="detalleTelefono"></span></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Progreso de Servicio Social</h6><hr>
                            <p><strong>Horas requeridas:</strong> <span id="detalleHorasRequeridas"></span></p>
                            <p><strong>Horas completadas:</strong> <span id="detalleHorasCompletadas"></span></p>
                            <p><strong>Horas restantes:</strong> <span id="detalleHorasRestantes"></span></p>
                            <div class="progress mt-3"><div id="detalleProgresoBar" class="progress-bar" role="progressbar"></div></div>
                        </div>
                    </div>
                    <h6>Últimos registros de horas</h6><hr>
                    <div class="table-responsive">
                        <table id="tablaRegistrosEstudiante" class="table table-sm">
                            <thead><tr><th>Fecha</th><th>Entrada</th><th>Salida</th><th>Horas</th><th>Estado</th></tr></thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" id="btnVerTodosRegistros"><i class="bi bi-clock-history me-1"></i> Ver todos los registros</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="../js/encargadojs/estudiantes.js"></script>
</body>
</html>
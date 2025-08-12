<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle del Estudiante</title>
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
            --pastel-background: #F8F5FB;
            --status-success: #28a745; 
            --status-danger: #dc3545;
            --status-warning: #ffc107;
            --status-info: #17a2b8;
        }

        body {
            background-color: var(--pastel-background);
            font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }
        
        /* ===== BARRA DE NAVEGACIÓN ===== */
        .sidebar { min-height: 100vh; background-color: #2c3e50; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .sidebar .nav-link { color: rgba(255, 255, 255, 0.8) !important; font-weight: 600; padding: 0.75rem 1rem; border-radius: 8px; transition: all 0.3s ease; display: block; }
        .sidebar .nav-link:hover { color: #ffffff !important; background-color: rgba(255, 255, 255, 0.1); }
        .sidebar .nav-link.active { color: #ffffff !important; background-color: #8856dd; border-left: 5px solid #8867c2; padding-left: calc(1rem - 5px); }
        .user-info { padding: 1rem; color: #ffffff; text-align: center; border-bottom: 1px solid rgba(255, 255, 255, 0.1); }
        .user-info .user-name { font-size: 1.05rem; font-weight: 500; margin-bottom: 0.4rem; min-height: 1.2rem; }
        .user-info .user-role-badge { display: inline-block; background-color: #4A5A6A; padding: 0.4rem 1.2rem; border-radius: 1rem; font-size: 0.9rem; font-weight: 500; }
        .sidebar .nav-link.text-danger:hover { color: #ffc107 !important; }
        .text-center.mb-4 img { max-height: 100px !important; }

        /* ===== ESTILOS GENERALES ===== */
        .card { box-shadow: 0 6px 12px rgba(0,0,0,0.1); border-radius: 12px; border: none; }
        .card-header, .modal-header { font-weight: 700; background-color: var(--primary-dark); color: var(--text-light); }
        .modal-title { color: var(--text-light); }
        .btn-close { filter: invert(1); }
        .progress { height: 25px; border-radius: 8px; background-color: var(--primary-light); }
        .progress-bar { font-size: 0.9rem; line-height: 25px; font-weight: bold; }
        .table th { font-weight: 600; background-color: var(--primary-light); color: var(--text-dark); vertical-align: middle; }
        .badge { font-size: 0.8em; padding: 0.6em 0.9em; border-radius: 0.5rem; font-weight: 600; }
        .card-estudiante { border-left: 5px solid var(--primary-medium); }

        /* Estilos para los botones de la tabla */
        .btn-primary { background-color: var(--primary-dark); border-color: var(--primary-dark); }
        .btn-warning { background-color: var(--status-warning); border-color: var(--status-warning); color: var(--text-dark); }

        /* ===== CORRECCIÓN AQUÍ: Estilo para que el botón de validar sea verde ===== */
        .btn.btn-primary.btnValidar {
            background-color: var(--green-action);
            border-color: var(--green-action);
        }
        .btn.btn-primary.btnValidar:hover {
            background-color: var(--green-hover);
            border-color: var(--green-hover);
        }

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
                            <a class="nav-link" href="encargado.html"><i class="bi bi-clock-history me-2"></i>Validar Horas</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="estudiantes.html"><i class="bi bi-people me-2"></i>Estudiantes</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="encargado_reportes.html"><i class="bi bi-graph-up me-2"></i>Reportes</a>
                        </li>
                        <li class="nav-item mt-4">
                            <a class="nav-link text-danger" href="../php/cerrar_sesion.php" onclick="cerrarSesion()"><i class="bi bi-box-arrow-left me-2"></i>Cerrar Sesión</a>
                        </li>
                    </ul>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Detalle del Estudiante</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="estudiantes.html" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Regresar</a>
                    </div>
                </div>

                <div class="card card-estudiante mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5 id="nombreEstudiante"></h5>
                                <p class="mb-1"><strong>Matrícula:</strong> <span id="matriculaEstudiante"></span></p>
                                <p class="mb-1"><strong>Carrera:</strong> <span id="carreraEstudiante"></span></p>
                                <p class="mb-1"><strong>Cuatrimestre:</strong> <span id="cuatrimestreEstudiante"></span></p>
                                <p class="mb-1"><strong>Teléfono:</strong> <span id="telefonoEstudiante"></span></p>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <strong>Progreso de horas:</strong>
                                    <div class="progress mt-2">
                                        <div id="progresoHoras" class="progress-bar" role="progressbar"></div>
                                    </div>
                                    <small class="text-muted"><span id="horasCompletadas"></span> / <span id="horasRequeridas"></span> horas</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Registros de Horas</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="tablaRegistros" class="table table-striped table-hover" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Fecha</th>
                                        <th>Entrada</th>
                                        <th>Salida</th>
                                        <th>Horas</th>
                                        <th>Estado</th>
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

    <div class="modal fade" id="modalValidacion" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Validar Registro</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <form id="formValidacion">
                        <input type="hidden" id="registroId"><input type="hidden" id="estudianteId"><input type="hidden" id="responsableId">
                        <div class="mb-3">
                            <label for="estadoValidacion" class="form-label">Estado</label>
                            <select class="form-select" id="estadoValidacion" required><option value="aprobado">Aprobar</option><option value="rechazado">Rechazar</option><option value="pendiente">Marcar como Pendiente</option></select>
                        </div>
                        <div class="mb-3">
                            <label for="observaciones" class="form-label">Observaciones</label>
                            <textarea class="form-control" id="observaciones" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="button" id="btnGuardarValidacion" class="btn btn-primary">Guardar</button></div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modalNuevoRegistro" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Nuevo Registro</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <form id="formNuevoRegistro">
                        <input type="hidden" id="nuevoEstudianteId"><input type="hidden" id="nuevoResponsableId">
                        <div class="mb-3"><label for="fechaRegistro" class="form-label">Fecha</label><input type="date" class="form-control" id="fechaRegistro" required></div>
                        <div class="row">
                            <div class="col-md-6 mb-3"><label for="horaEntrada" class="form-label">Entrada</label><input type="time" class="form-control" id="horaEntrada" required></div>
                            <div class="col-md-6 mb-3"><label for="horaSalida" class="form-label">Salida</label><input type="time" class="form-control" id="horaSalida" required></div>
                        </div>
                        <div class="mb-3"><label for="observacionesRegistro" class="form-label">Actividades</label><textarea class="form-control" id="observacionesRegistro" rows="3" required></textarea></div>
                    </form>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="button" id="btnGuardarRegistro" class="btn btn-primary">Guardar</button></div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../js/encargadojs/estudiante_detalle.js"></script>
</body>
</html>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Vinculación - Servicio Social</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <style>
        :root {
            --sidebar-bg: #2c3e50;
            --sidebar-link-color: rgba(255, 255, 255, 0.8);
            --sidebar-link-hover-active: #ffffff;
            --sidebar-link-bg-hover-active: rgba(255, 255, 255, 0.1);
            --sidebar-active-bg: #8856dd;
        }
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            color: #343a40;
        }
        .sidebar {
            min-height: 100vh;
            background-color: var(--sidebar-bg);
        }
        .sidebar .nav-link {
            color: var(--sidebar-link-color) !important;
            padding: 0.8rem 1.5rem;
            display: flex;
            align-items: center;
            border-radius: 8px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .sidebar .nav-link .bi {
            font-size: 1.2rem;
            margin-right: 0.8rem;
        }
        .sidebar .nav-link:hover {
            color: var(--sidebar-link-hover-active) !important;
            background-color: var(--sidebar-link-bg-hover-active);
        }
        .sidebar .nav-link.active {
            color: var(--sidebar-link-hover-active) !important;
            background-color: var(--sidebar-active-bg);
        }
        .user-role-badge {
            background-color: rgba(255, 255, 255, 0.15) !important;
            font-size: 0.85rem;
            padding: 0.4rem 1rem;
            font-weight: 500;
        }
        .main-header {
            background-color: #ffffff;
            padding: 0.8rem 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            border-bottom: 1px solid #dee2e6;
        }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
        }
        .card-header {
            font-weight: 600;
            background-color: var(--sidebar-active-bg);
            color: var(--sidebar-link-hover-active);
            padding: 1rem 1.5rem;
        }
        .table thead th {
            background-color: #f8f9fa;
            color: #6c757d;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
        }
        .btn-primary {
            background-color: var(--sidebar-active-bg);
            border-color: var(--sidebar-active-bg);
        }
    </style>
</head>
<body>
    <div class="container-fluid p-0 d-flex">
        <nav id="sidebar" class="d-flex flex-column flex-shrink-0 p-3 text-white sidebar">
            <div class="text-center mb-4 mt-3">
                <img src="../imagenes/data.png" class="img-fluid" style="max-height: 90px;" alt="Logo"/>
                <h5 class="text-white mt-2">Servicio Social</h5>
            </div>
            <div class="text-center text-white mb-3">
                <h6 id="userName" class="mb-1">Cargando...</h6>
                <span id="userEmail" class="badge rounded-pill user-role-badge"></span>
            </div>
            <ul class="nav nav-pills flex-column mb-auto">
                <li class="nav-item mb-1"><a href="vinculacion.html" class="nav-link active"><i class="bi bi-link-45deg"></i> Vinculación</a></li>
            </ul>
            <div class="p-3 border-top" style="border-color: rgba(255,255,255,0.1) !important;"><a href="../php/cerrar_sesion.php" class="nav-link"><i class="bi bi-box-arrow-left"></i> Cerrar Sesión</a></div>
        </nav>

        <div class="w-100">
            <header class="main-header"><h5 class="h5 m-0 fw-bold">Gestión de Vinculación</h5></header>
            <main class="p-4">
                <div class="card mb-4">
                    <div class="card-header">Generación Masiva de Cartas</div>
                    <div class="card-body">
                        <p class="text-muted">Selecciona una carrera para generar las cartas de Presentación y Aceptación de todos sus alumnos con solicitud activa.</p>
                        <div class="row g-3 align-items-end">
                            <div class="col-md-6">
                                <label for="filtroCarrera" class="form-label">Carrera</label>
                                <select id="filtroCarrera" class="form-select"></select>
                            </div>
                            <div class="col-md-6">
                                <button type="button" id="btnGenerarPorCarrera" class="btn btn-primary w-100">
                                    <i class="bi bi-printer-fill me-2"></i>Generar Cartas por Carrera
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">Filtros de Búsqueda</div>
                    <div class="card-body">
                        <form id="filtroForm">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-3">
                                    <label for="filtroPeriodo" class="form-label">Periodo de Servicio</label>
                                    <select id="filtroPeriodo" class="form-select"></select>
                                </div>
                                <div class="col-md-3">
                                    <label for="filtroCarta" class="form-label">Estado Carta Aceptación</label>
                                    <select id="filtroCarta" class="form-select">
                                        <option value="">Todos</option>
                                        <option value="Aprobada">Aprobada</option>
                                        <option value="Pendiente">Pendiente</option>
                                        <option value="Rechazada">Rechazada</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="filtroAlumno" class="form-label">Nombre del Alumno</label>
                                    <input type="text" id="filtroAlumno" class="form-control" placeholder="Buscar por nombre...">
                                </div>
                                <div class="col-md-3 d-flex justify-content-start align-items-end">
                                    <button type="button" id="btnFiltrar" class="btn btn-primary me-2"><i class="bi bi-funnel-fill me-1"></i>Filtrar</button>
                                    <button type="button" id="btnRefrescar" class="btn btn-outline-info me-2"><i class="bi bi-arrow-clockwise me-1"></i>Refrescar</button>
                                    <button type="reset" id="btnLimpiar" class="btn btn-outline-secondary"><i class="bi bi-arrow-counterclockwise me-1"></i>Limpiar</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">Alumnos Vinculados</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="tablaVinculacion" class="table table-hover" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Alumno</th>
                                        <th>Matrícula</th>
                                        <th>Entidad</th>
                                        <th>Docs. Subidos</th>
                                        <th>C. Presentación</th>
                                        <th>C. Aceptación</th>
                                        <th>1er Informe</th>
                                        <th>2do Informe</th>
                                        <th>Comp. Pago</th>
                                        <th>Generar Docs</th>
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

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../js/vinculacionjs/vinculacion.js"></script> 
</body>
</html>
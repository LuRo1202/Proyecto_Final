<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel del Encargado - Validación de Horas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        :root {
            /* Colores Normales (no pastel) para elementos principales - PALETA MORADA REFINADA */
            --primary-dark: #8856dd;      /* Morado oscuro para navbar, card headers */
            --primary-medium: #8867c2;    /* Morado medio para spinners, acentos */
            --primary-light: #D1C4E9;     /* Morado claro, casi blanco para encabezados de tabla, fondos sutiles */
            
            /* Colores de Acción Normales (no pastel) */
            --green-action: #4CAF50;      /* Verde para acciones de éxito (Entrada, barra de progreso) */
            --green-hover: #43A047;        /* Verde más oscuro para hover */
            --red-action: #F44336;        /* Rojo para acciones de peligro (Salida) */
            --red-hover: #E53935;          /* Rojo más oscuro para hover */

            /* Colores de Texto y UI General */
            --text-dark: #343a40;          /* Texto oscuro para legibilidad en fondos claros */
            --text-light: #FFFFFF;        /* Texto blanco para fondos oscuros o contraste */
            --text-muted: #6c757d;        /* Texto atenuado para información secundaria */
            --border-light: #dee2e6;      /* Color de borde claro */

            /* Fondo Pastel (se mantiene) */
            --pastel-background: #F8F5FB;  /* Fondo muy claro general */
            
            /* Colores estándar de Bootstrap para insignias/estado (para retroalimentación clara) */
            --status-success: #28a745; 
            --status-danger: #dc3545;
            --status-warning: #ffc107;
            --status-info: #17a2b8;
        }

        body {
            background-color: var(--pastel-background); /* Mantiene el fondo pastel */
            font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            color: var(--text-dark); /* Color de texto predeterminado para contenido general */
        }
        
        /* ===== BARRA DE NAVEGACIÓN (MISMO DISEÑO, SOLO CAMBIO DE COLORES) ===== */
        .sidebar {
            min-height: 100vh;
            background-color: #2c3e50; /* Color corregido del fondo del sidebar */
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8) !important; /* Color corregido del texto base */
            font-weight: 600;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            transition: background-color 0.3s ease, color 0.3s ease, border-left 0.3s ease;
            display: block;
        }

        .sidebar .nav-link:hover {
            color: #ffffff !important; /* Color corregido al hacer hover */
            background-color: rgba(255, 255, 255, 0.1); /* Fondo hover corregido */
        }

        .sidebar .nav-link.active {
            color: #ffffff !important; /* Color corregido del texto activo */
            background-color: #8856dd; /* Fondo activo corregido */
            border-left: 5px solid #8867c2; /* Color corregido del borde activo */
            padding-left: calc(1rem - 5px);
        }

        .user-info {
            padding: 1rem;
            color: #ffffff; /* Color corregido del texto de usuario */
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        /* Estilos para el bloque de información de usuario */
        .user-info .user-name {
            font-size: 1.05rem; /* Tamaño de fuente para el nombre */
            font-weight: 500;   /* Grosor de la fuente */
            color: #ffffff;
            margin-bottom: 0.4rem; /* Espacio entre el nombre y el rol */
            min-height: 1.2rem; /* Evita que el layout salte mientras carga el nombre */
        }

        .user-info .user-role-badge {
            display: inline-block;
            background-color: #4A5A6A; /* Color de fondo del badge de rol */
            color: #ffffff;
            padding: 0.4rem 1.2rem; /* Espaciado interno */
            border-radius: 1rem;   /* Bordes redondeados */
            font-size: 0.9rem;     /* Tamaño de fuente del rol */
            font-weight: 500;
        }

        .sidebar .nav-link.text-danger:hover {
            color: #ffc107 !important; /* Color corregido para hover en 'Cerrar sesión' */
        }

        /* Estilo de las tarjetas */
        .card {
            box-shadow: 0 6px 12px rgba(0,0,0,0.1);
            border-radius: 12px;
            overflow: hidden;
            border: none;
            background-color: var(--text-light);
        }
        .card-header {
            font-weight: 700;
            background-color: var(--primary-dark);
            color: var(--text-light);
            padding: 1rem 1.5rem;
            border-bottom: 1px solid rgba(0,0,0,0.125);
            display: flex;
            align-items: center;
        }
        .modal-header {
            background-color: var(--primary-dark);
            color: var(--text-light);
            border-bottom: 1px solid rgba(0,0,0,0.125);
        }
        .modal-title {
            color: var(--text-light);
        }
        .btn-close {
            filter: invert(1);
        }

        /* Barra de Progreso */
        .progress { height: 25px; border-radius: 8px; background-color: var(--primary-light); }
        .progress-bar { font-size: 0.9rem; line-height: 25px; background-color: var(--green-action) !important; color: var(--text-light) !important; font-weight: bold; transition: width 1s ease-in-out; border-radius: 8px; }
        
        /* Estilo de los Botones */
        .btn { font-weight: 600; padding: 0.5rem 0.8rem; border-radius: 8px; border: 1px solid transparent; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.15); }
        .btn-primary { background-color: var(--primary-dark); border-color: var(--primary-dark); color: var(--text-light); }
        .btn-primary:hover { background-color: var(--primary-medium); border-color: var(--primary-medium); }
        .btn-outline-secondary { color: var(--primary-dark); border-color: var(--primary-dark); background-color: transparent; }
        .btn-outline-secondary:hover { background-color: var(--primary-light); border-color: var(--primary-dark); color: var(--text-dark); }
        .btn-secondary { background-color: var(--text-muted); border-color: var(--text-muted); color: var(--text-light); }
        .btn-secondary:hover { background-color: #5a6268; border-color: #545b62; }
        .btn-danger { background-color: var(--red-action); border-color: var(--red-action); color: var(--text-light); }
        .btn-danger:hover { background-color: var(--red-hover); border-color: var(--red-hover); }
        .btn-success { background-color: var(--green-action); border-color: var(--green-action); color: var(--text-light); }
        .btn-success:hover { background-color: var(--green-hover); border-color: var(--green-hover); }
        .btn-info { background-color: var(--status-info); border-color: var(--status-info); color: var(--text-light); }
        .btn-info:hover { background-color: #117a8b; border-color: #10707f; }

        /* ===== CORRECCIÓN AQUÍ: Estilo para que el botón de validar sea verde ===== */
        .btn.btn-primary.btnValidar {
            background-color: var(--green-action);
            border-color: var(--green-action);
        }
        .btn.btn-primary.btnValidar:hover {
            background-color: var(--green-hover);
            border-color: var(--green-hover);
        }

        /* Estilo de la Tabla */
        .table th { font-weight: 600; background-color: var(--primary-light); color: var(--text-dark); vertical-align: middle; font-size: 1rem; padding: 1rem; }
        .table td { vertical-align: middle; color: var(--text-dark); padding: 0.75rem 1rem; }
        .table-striped tbody tr:nth-of-type(odd) { background-color: rgba(0, 0, 0, 0.03); }
        .table-hover tbody tr:hover { background-color: rgba(0, 0, 0, 0.07); cursor: pointer; }
        .badge { font-size: 0.8em; padding: 0.6em 0.9em; border-radius: 0.5rem; font-weight: 600; color: var(--text-light); }
        .badge.bg-primary { background-color: var(--primary-medium) !important; }
        .badge.bg-success { background-color: var(--status-success) !important; }
        .badge.bg-danger { background-color: var(--status-danger) !important; }
        .badge.bg-warning { background-color: var(--status-warning) !important; color: var(--text-dark) !important; }
        .badge.bg-info { background-color: var(--status-info) !important; }
        .filter-actions { display: flex; gap: 0.5rem; justify-content: flex-end; margin-top: 1rem; }
        @media (max-width: 767.98px) { .filter-actions { justify-content: flex-start; margin-top: 1rem; } .sidebar { box-shadow: none; } .main-content { padding-top: 1rem; } }
        .text-center.mb-4 img { max-height: 100px !important; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <img src="../imagenes/data.png" alt="Logo" class="img-fluid" style="max-height: 90px;">
                        <h5 class="text-white mt-2">Servicio Social</h5>
                    </div>

                    <div class="user-info text-center">
                        <div id="user-name" class="user-name">Cargando...</div>
                        <div class="user-role-badge">Encargado</div>
                    </div>

                    <ul class="nav flex-column px-3 mt-3">
                        <li class="nav-item">
                            <a class="nav-link active" href="encargado.html">
                                <i class="bi bi-clock-history me-2"></i>Validar Horas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="estudiantes.html">
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
                <h1 class="h2 mb-4">Validación de Horas de Servicio Social</h1>
                
                <div class="card mb-4">
                    <div class="card-body">
                        <form id="filtroForm">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="filtroEstado" class="form-label">Estado</label>
                                    <select id="filtroEstado" class="form-select">
                                        <option value="">Todos</option>
                                        <option value="pendiente">Pendientes</option>
                                        <option value="aprobado">Aprobados</option>
                                        <option value="rechazado">Rechazados</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="filtroFecha" class="form-label">Fecha</label>
                                    <input type="date" id="filtroFecha" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label for="filtroEstudiante" class="form-label">Estudiante</label>
                                    <div class="input-group">
                                        <input type="text" id="filtroEstudiante" class="form-control" placeholder="Nombre o matrícula">
                                        <button class="btn btn-outline-secondary" type="button" id="btnBuscar">
                                            <i class="bi bi-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="filter-actions">
                                <button type="button" id="btnFiltrar" class="btn btn-primary">
                                    <i class="bi bi-funnel-fill me-1"></i>Filtrar
                                </button>
                                <button type="reset" id="btnLimpiar" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i>Limpiar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="tablaRegistros" class="table table-striped table-hover" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Estudiante</th>
                                        <th>Matrícula</th>
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

    <div class="modal fade" id="modalValidacion" tabindex="-1" aria-labelledby="modalValidacionLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalValidacionLabel">Validar Registro de Horas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formValidacion">
                        <input type="hidden" id="registroId">
                        <div class="mb-3">
                            <label for="estadoValidacion" class="form-label">Estado</label>
                            <select class="form-select" id="estadoValidacion" required>
                                <option value="aprobado">Aprobar</option>
                                <option value="rechazado">Rechazar</option>
                                <option value="pendiente">Marcar como Pendiente</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="observaciones" class="form-label">Observaciones</label>
                            <textarea class="form-control" id="observaciones" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="btnGuardarValidacion" class="btn btn-primary btn-sm">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../js/encargadojs/encargado.js"></script>
</body>
</html>
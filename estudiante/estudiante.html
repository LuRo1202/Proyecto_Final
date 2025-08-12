<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Estudiante - Servicio Social</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            /* Colores Normales (no pastel) para elementos principales */
            --primary-dark: #8856dd;      /* Morado oscuro para navbar, card headers */
            --primary-medium: #b957ca;    /* Morado medio para spinners, acentos */
            --primary-light: #D1C4E9;     /* Morado claro para encabezados de tabla */
            
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
        
        /* Estilo de la barra de navegación */
        .navbar {
            background-color: var(--primary-dark) !important; /* Color normal oscuro */
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .navbar-brand, .nav-link {
            color: var(--text-light) !important; /* Texto blanco */
            font-weight: 600;
        }
        .nav-link:hover {
            color: rgba(255, 255, 255, 0.8) !important;
        }

        /* Estilo de las tarjetas */
        .card {
            box-shadow: 0 6px 12px rgba(0,0,0,0.1);
            border-radius: 12px;
            overflow: hidden;
            border: none;
            background-color: var(--text-light); /* Fondo blanco para las tarjetas */
        }
        .card-header {
            font-weight: 700;
            background-color: var(--primary-dark); /* Color normal oscuro */
            color: var(--text-light); /* Texto blanco */
            padding: 1rem 1.5rem;
            border-bottom: 1px solid rgba(0,0,0,0.125);
            display: flex;
            align-items: center;
        }
        .card-header .card-title {
            margin-bottom: 0;
            font-size: 1.25rem;
        }

        /* Barra de Progreso */
        .progress-bar {
            background-color: var(--green-action) !important; /* Verde normal */
            color: var(--text-light) !important; /* Texto blanco */
            font-weight: bold;
            transition: width 1s ease-in-out;
        }

        /* Estilo de los Botones */
        .btn {
            font-weight: 600;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            border: 1px solid transparent;
            transition: all 0.3s ease;
        }
        .btn-success {
            background-color: var(--green-action); /* Verde normal */
            border-color: var(--green-action);
            color: var(--text-light); /* Texto blanco */
        }
        .btn-success:hover {
            background-color: var(--green-hover);
            border-color: var(--green-hover);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .btn-danger {
            background-color: var(--red-action); /* Rojo normal */
            border-color: var(--red-action);
            color: var(--text-light); /* Texto blanco */
        }
        .btn-danger:hover {
            background-color: var(--red-hover);
            border-color: var(--red-hover);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .btn-light {
            background-color: var(--text-light);
            color: var(--primary-dark); /* Texto oscuro para botón claro */
            border-color: var(--primary-light);
        }
        .btn-light:hover {
            background-color: var(--primary-light);
            border-color: var(--primary-medium);
        }

        /* Estilo de la Tabla */
        .table th {
            font-weight: 600;
            background-color: var(--primary-light); /* Morado claro normal */
            color: var(--text-dark); /* Texto oscuro */
            vertical-align: middle;
            font-size: 1rem;
        }
        .table td {
            vertical-align: middle;
            color: var(--text-dark); /* Texto oscuro */
        }
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 0, 0, 0.03);
        }

        /* */
        .pagination-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1rem;
        }

        /* Insignias */
        .badge {
            font-size: 0.8em;
            padding: 0.6em 0.9em;
            border-radius: 0.5rem;
            font-weight: 600;
            color: var(--text-light);
        }
        .badge.bg-success { background-color: var(--status-success) !important; }
        .badge.bg-danger { background-color: var(--status-danger) !important; }
        .badge.bg-warning { background-color: var(--status-warning) !important; color: var(--text-dark) !important; }
        .badge.bg-info { background-color: var(--status-info) !important; }

        /* Alertas */
        #alertContainer {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            width: 90%;
            max-width: 380px;
        }
        .alert-info {
            background-color: #e0f7fa;
            border-color: #b2ebf2;
            color: #006064;
        }
        
        /* Spinners */
        .spinner-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100px;
            color: var(--text-muted);
        }
        .spinner-border {
            color: var(--primary-medium) !important; /* Morado medio normal */
        }

        /* Ajustes Responsivos */
        @media (min-width: 768px) {
            .col-md-4, .col-md-8, .col-12 {
                flex: 1; 
                max-width: 100%; 
            }
            .row > div {
                display: flex;
                flex-direction: column;
            }
            .card {
                flex: 1;
            }
        }
        @media (max-width: 767.98px) {
            #alertContainer {
                left: 5%;
                right: 5%;
                width: 90%;
            }
            .card-header {
                padding: 0.75rem 1rem;
            }
            .card-header .card-title {
                font-size: 1.1rem;
            }
            .btn {
                padding: 0.6rem 0.8rem;
                font-size: 0.9rem;
            }
             /* */
            .pagination-controls {
                flex-direction: column;
                gap: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <div id="alertContainer"></div>

    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="bi bi-people-fill me-2"></i>Servicio Social
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="solicitud.html">
                            <i class="bi bi-file-earmark-text me-1"></i>Generar Solicitud
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="documentos.html">
                            <i class="bi bi-upload me-1"></i> Cargar Documentos
                        </a>
                    </li>

                    <li class="nav-item">
                        <span class="nav-link">
                            <i class="bi bi-person-circle me-1"></i>
                            <span id="nombreUsuarioText">Cargando usuario...</span>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../php/cerrar_sesion.php">
                            <i class="bi bi-box-arrow-right me-1"></i>Cerrar Sesión
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-4 d-flex">
                <div class="card mb-4 w-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-person-badge me-2"></i>Información del Estudiante
                        </h5>
                    </div>
                    <div class="card-body" id="infoEstudiante">
                        <div class="spinner-container" id="studentInfoSpinner">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            <p class="mt-2">Cargando información del estudiante...</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4 d-flex">
                <div class="card mb-4 w-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-clock-history me-2"></i>Progreso de Horas
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="progress mb-3" style="height: 30px;">
                            <div class="progress-bar" role="progressbar" id="progressBar" 
                                 style="width: 0%" 
                                 aria-valuenow="0" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                 0%
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong><i class="bi bi-check-circle-fill text-success me-1"></i>Completadas:</strong> 
                                <span id="horasCompletadas">0</span></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong><i class="bi bi-bookmark-star-fill text-primary me-1"></i>Requeridas:</strong> 
                                <span id="horasRequeridas">480</span></p>
                            </div>
                        </div>
                        <p><strong><i class="bi bi-hourglass-split text-warning me-1"></i>Restantes:</strong> 
                        <span id="horasRestantes">480</span></p>
                    </div>
                </div>
            </div>

            <div class="col-md-4 d-flex">
                <div class="card w-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-calendar-check me-2"></i>Registro de Horas
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="formRegistroHoras" class="mb-3">
                            <button type="button" id="btnRegistrarEntrada" class="btn btn-success w-100 mb-2">
                                <i class="bi bi-clock-fill me-1"></i> Registrar Entrada
                            </button>
                            <button type="button" id="btnRegistrarSalida" class="btn btn-danger w-100">
                                <i class="bi bi-clock-history me-1"></i> Registrar Salida
                            </button>
                        </form>
                        <div class="alert alert-info">
                            <small>
                                <i class="bi bi-info-circle-fill me-1"></i> 
                                Debes completar al menos 4 horas.
                                No puedes registrar salida antes de 4 horas desde la entrada.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-12 d-flex">
                <div class="card w-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-list-check me-2"></i>Historial de Registros
                        </h5>
                        <button type="button" class="btn btn-sm btn-light" id="btnActualizarRegistros">
                            <i class="bi bi-arrow-clockwise me-1"></i> Actualizar
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="tablaRegistros">
                                <thead class="table-light">
                                    <tr>
                                        <th><i class="bi bi-calendar-date me-1"></i>Fecha</th>
                                        <th><i class="bi bi-arrow-right-circle me-1"></i>Entrada</th>
                                        <th><i class="bi bi-arrow-left-circle me-1"></i>Salida</th>
                                        <th><i class="bi bi-clock me-1"></i>Horas</th>
                                        <th><i class="bi bi-patch-check me-1"></i>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    </tbody>
                            </table>
                            <p id="noRecordsMessage" class="text-center text-muted py-3" style="display: none;">
                                <i class="bi bi-info-circle me-1"></i> Aún no hay registros de horas para mostrar.
                            </p>
                             <div class="spinner-container" id="recordsTableSpinner">
                                <div class="spinner-border" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                                <p class="mt-2">Cargando historial de registros...</p>
                            </div>
                        </div>

                        <div id="paginationContainer" class="pagination-controls" style="display: none;">
                            <button id="btnAnterior" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Anterior
                            </button>
                            <span id="infoPagina" class="text-muted small">Página 1 de 1</span>
                            <button id="btnSiguiente" class="btn btn-sm btn-outline-secondary">
                                Siguiente <i class="bi bi-arrow-right"></i>
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-light mt-5 py-3">
        <div class="container text-center">
            <p class="mb-0 text-muted">
                <small>Sistema de Servicio Social &copy; 2025</small>
            </p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/estudiantejs/estudiante.js"></script>
</body>
</html>
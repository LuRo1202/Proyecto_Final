<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentos de Servicio Social</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-dark: #8856dd;
            --primary-medium: #b957ca;
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

        body {
            background-color: var(--pastel-background);
            font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            color: var(--text-dark);
        }
        
        .navbar {
            background-color: var(--primary-dark) !important;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .navbar-brand, .nav-link {
            color: var(--text-light) !important;
            font-weight: 600;
        }
        .nav-link:hover {
            color: rgba(255, 255, 255, 0.8) !important;
        }

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
        .card-header .card-title {
            margin-bottom: 0;
            font-size: 1.25rem;
        }

        .btn {
            font-weight: 600;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            border: 1px solid transparent;
            transition: all 0.3s ease;
        }
        .btn-success {
            background-color: var(--green-action);
            border-color: var(--green-action);
            color: var(--text-light);
        }
        .btn-success:hover {
            background-color: var(--green-hover);
            border-color: var(--green-hover);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .btn-primary {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
            color: var(--text-light);
        }
        .btn-primary:hover {
            background-color: var(--primary-medium);
            border-color: var(--primary-medium);
            color: var(--text-light);
        }

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

        /* Estilos específicos para documentos */
        .document-card {
            border-left: 4px solid var(--primary-medium);
            transition: all 0.3s ease;
        }
        .document-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }
        .upload-area {
            border: 2px dashed var(--primary-light);
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background-color: rgba(209, 196, 233, 0.1);
        }
        .upload-area:hover {
            border-color: var(--primary-medium);
            background-color: rgba(209, 196, 233, 0.2);
        }
        .file-info {
            font-size: 0.9rem;
            color: var(--text-muted);
        }
        .document-status {
            font-weight: 600;
        }
        .document-actions .btn {
            padding: 0.4rem 0.8rem;
            font-size: 0.9rem;
        }

        #alertContainer {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            width: 90%;
            max-width: 380px;
        }
    </style>
</head>
<body>
    <div id="alertContainer"></div>

    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="estudiante.html">
                <i class="bi bi-arrow-left me-2"></i>Volver
            </a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
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
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-file-earmark-arrow-up me-2"></i>Documentos de Servicio Social
                        </h5>
                        <button id="btnRefreshDocuments" class="btn btn-light btn-sm ms-auto">
                            <i class="bi bi-arrow-clockwise me-1"></i> Actualizar
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info mb-4">
                            <i class="bi bi-info-circle-fill me-2"></i>
                            Sube los documentos requeridos para tu servicio social. Asegúrate de que sean legibles y estén completos.
                        </div>
                        
                        <div class="row" id="documentosContainer">
                            <div class="col-12 text-center py-5" id="loadingDocuments">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                                <p class="mt-3">Cargando documentos requeridos...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadModalLabel">Subir Documento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="documentUploadForm">
                        <input type="hidden" id="documentTypeId">
                        <div class="mb-3">
                            <label for="documentFile" class="form-label">Selecciona el archivo</label>
                            <div class="upload-area" id="dropArea">
                                <i class="bi bi-cloud-arrow-up fs-1 text-primary"></i>
                                <p class="mt-2">Arrastra y suelta tu archivo aquí o haz clic para seleccionar</p>
                                <input class="form-control d-none" type="file" id="documentFile" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                <div class="file-info mt-2" id="fileInfo">Ningún archivo seleccionado</div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="documentObservations" class="form-label">Observaciones (opcional)</label>
                            <textarea class="form-control" id="documentObservations" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnUploadDocument">Subir Documento</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="viewDocumentModal" tabindex="-1" aria-labelledby="viewDocumentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewDocumentModalLabel">Documento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center" id="documentViewer">
                        <p>Cargando documento...</p>
                    </div>
                    <div class="mt-3">
                        <h6>Estado: <span id="documentStatusBadge" class="badge bg-secondary">Pendiente</span></h6>
                        <p id="documentObservationsText">Sin observaciones</p>
                        <p class="text-muted small">Subido el: <span id="documentUploadDate">-</span></p>
                        <p class="text-muted small">Validado el: <span id="documentValidationDate">-</span></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <a href="#" class="btn btn-primary" id="btnDownloadDocument" download>
                        <i class="bi bi-download me-1"></i> Descargar
                    </a>
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
    <script src="../js/estudiantejs/cargarDocumentos.js"></script>
</body>
</html>
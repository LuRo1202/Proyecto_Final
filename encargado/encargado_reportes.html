<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Reportes - Encargado</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet"/>
  <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet"/>
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

    /* ===== ESTILOS GENERALES (TARJETAS, BOTONES, TABLAS) ===== */
    .card { box-shadow: 0 6px 12px rgba(0,0,0,0.1); border-radius: 12px; border: none; overflow: hidden; }
    .card-header, .modal-header { font-weight: 700; background-color: var(--primary-dark); color: var(--text-light); }
    .modal-title { color: var(--text-light); }
    .btn-close { filter: invert(1); }
    .btn { font-weight: 600; border-radius: 8px; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .btn:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.15); }
    .btn-primary { background-color: var(--primary-dark); border-color: var(--primary-dark); }
    .btn-primary:hover { background-color: var(--primary-medium); border-color: var(--primary-medium); }
    .btn-info { background-color: var(--status-info); border-color: var(--status-info); }
    .btn-info:hover { background-color: #117a8b; border-color: #10707f; }
    .btn-outline-secondary { color: var(--text-dark); border-color: var(--text-muted); }
    .btn-outline-secondary:hover { background-color: #f1f1f1; }

    .table th { font-weight: 600; background-color: var(--primary-light); color: var(--text-dark); vertical-align: middle; }
    .table-hover tbody tr:hover { background-color: rgba(0, 0, 0, 0.07); }
    .progress { height: 22px; font-size: 0.8rem; background-color: #e9ecef; }
    .progress-bar { font-weight: 600; }
    .badge { font-weight: 600; }
    
    /* ===== ESTILOS ESPECÍFICOS DE LA PÁGINA DE REPORTES ===== */
    .badge.bg-liberado { background-color: #198754 !important; }
    .badge.bg-en-proceso { background-color: #0d6efd !important; }
    .chart-container { position: relative; height: 320px; width: 100%; }
    .student-info-card { border-left: 5px solid var(--primary-medium); }
    .badge-horas { font-size: 1rem; padding: 0.5em 0.75em; border-radius: 0.5rem; }
    #vista-tabla, #vista-detalle { transition: opacity 0.3s ease-in-out; }
  </style>
</head>

<body>
  <div class="container-fluid">
    <div class="row">
      <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse show">
        <div class="position-sticky pt-3">
          <div class="text-center mb-4">
            <img src="../imagenes/data.png" alt="Logo" class="img-fluid" style="max-height: 100px;" />
            <h5 class="text-white mt-2">Servicio Social</h5>
          </div>

          <div class="user-info text-center">
            <div id="user-name" class="user-name">Cargando...</div>
            <div class="user-role-badge">Encargado</div>
          </div>

          <ul class="nav flex-column px-3 mt-3">
            <li class="nav-item">
              <a class="nav-link" href="encargado.html"><i class="bi bi-check-circle me-2"></i>Validar Horas</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="estudiantes.html"><i class="bi bi-people me-2"></i>Estudiantes</a>
            </li>
            <li class="nav-item">
              <a class="nav-link active" href="encargado_reportes.html"><i class="bi bi-graph-up me-2"></i>Reportes</a>
            </li>
            <li class="nav-item mt-4">
              <a class="nav-link text-danger" id="btn-logout" href="#"><i class="bi bi-box-arrow-left me-2"></i>Cerrar Sesión</a>
            </li>
          </ul>
        </div>
      </nav>

      <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
        <div id="vista-tabla">
          <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">Progreso de Estudiantes Asignados</h1>
          </div>

          <div class="card">
            <div class="card-body">
              <div class="table-responsive">
                <table id="tabla-progreso" class="table table-striped table-hover" style="width:100%">
                  <thead>
                    <tr>
                      <th>Matrícula</th>
                      <th>Nombre Completo</th>
                      <th>Horas / Progreso</th>
                      <th>Estado</th>
                      <th>Acciones</th>
                    </tr>
                  </thead>
                  <tbody></tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <div id="vista-detalle" style="display: none;">
          <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">Reporte Detallado del Estudiante</h1>
            <button id="btn-volver" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver a la lista</button>
          </div>

          <div id="report-content">
            <div class="card student-info-card mb-4">
              <div class="card-body" id="student-info-header"></div>
            </div>

            <div class="row">
              <div class="col-xl-6 mb-4">
                <div class="card h-100">
                  <div class="card-header">Horas por Semana</div>
                  <div class="card-body chart-container"><canvas id="horasSemanalesChart"></canvas></div>
                </div>
              </div>
              <div class="col-xl-6 mb-4">
                <div class="card h-100">
                  <div class="card-header">Horas por Mes</div>
                  <div class="card-body chart-container"><canvas id="horasMensualesChart"></canvas></div>
                </div>
              </div>
              <div class="col-xl-6 mb-4">
                <div class="card h-100">
                  <div class="card-header">Horas por Trimestre</div>
                  <div class="card-body chart-container"><canvas id="horasTrimestralesChart"></canvas></div>
                </div>
              </div>
              <div class="col-xl-6 mb-4">
                <div class="card h-100">
                  <div class="card-header">Últimos Registros</div>
                  <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 350px;">
                      <table class="table table-sm table-hover mb-0">
                        <thead><tr><th>Fecha</th><th>Horas</th><th>Estado</th></tr></thead>
                        <tbody id="recent-registros-table"></tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div id="error-message" class="alert alert-danger" style="display:none;"></div>
        </div>
      </main>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="../js/encargadojs/encargado_reportes.js"></script>
</body>
</html>
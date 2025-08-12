<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Servicio Social</title>
    <!-- Librerías CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
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
        }
        body { background-color: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
        .sidebar { min-height: 100vh; background-color: var(--sidebar-bg); }
        .sidebar .nav-link { color: var(--sidebar-link-color) !important; padding: 0.75rem 1rem; border-radius: 8px; transition: all 0.3s ease; }
        .sidebar .nav-link:hover { color: var(--sidebar-link-hover-active) !important; background-color: var(--sidebar-link-bg-hover-active); }
        .sidebar .nav-link.active { color: var(--sidebar-link-hover-active) !important; background-color: var(--sidebar-active-bg); border-left: 5px solid var(--sidebar-active-border); padding-left: calc(1rem - 5px); }
        .user-info { padding: 1rem; color: #ffffff; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .user-info #user-role { background-color: rgba(255,255,255,0.2); font-size: 0.85rem; padding: 0.4rem 0.8rem; }
        .profile-img { width: 150px; height: 150px; object-fit: cover; border: 4px solid var(--primary-dark); }
        .profile-card { border-radius: 15px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .card-header { background-color: var(--primary-dark); color: white; font-weight: 600; }
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
                        <div id="user-email">Cargando...</div>
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
                        <li class="nav-item"><a class="nav-link" href="reportes.html"><i class="bi bi-graph-up me-2"></i>Reportes</a></li>
                        <li class="nav-item"><a class="nav-link" href="gestion_periodos.html"><i class="bi bi-calendar-event me-2"></i>Gestionar Períodos</a></li>
                        <li class="nav-item mt-4"><a class="nav-link active" href="perfil.html"><i class="bi bi-person-circle me-2"></i>Mi Perfil</a></li>
                        <li class="nav-item"><a class="nav-link text-danger" id="btn-logout" href="../php/cerrar_sesion.php"><i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión</a></li>
                    </ul>
                </div>
            </nav>

            <!-- Contenido principal -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Mi Perfil</h1>
                </div>

                <div class="row">
                    <div class="col-lg-4">
                        <div class="card profile-card mb-4">
                            <div class="card-body text-center p-4">
                                <img src="https://ui-avatars.com/api/?name=?&background=8856dd&color=fff&size=150" 
                                     alt="Foto de perfil" 
                                     class="profile-img rounded-circle mb-3" id="profile-img">
                                <h3 id="profile-name" class="mb-1">Cargando...</h3>
                                <p class="text-muted" id="profile-email">Cargando...</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-8">
                        <div class="card profile-card">
                            <div class="card-header">
                                Información Personal
                            </div>
                            <div class="card-body p-4">
                                <form id="profile-form" novalidate>
                                    <div class="row mb-3">
                                        <div class="col-md-4"><label for="nombre" class="form-label">Nombre(s)*</label><input type="text" class="form-control" id="nombre" required></div>
                                        <div class="col-md-4"><label for="apellido_paterno" class="form-label">Apellido Paterno*</label><input type="text" class="form-control" id="apellido_paterno" required></div>
                                        <div class="col-md-4"><label for="apellido_materno" class="form-label">Apellido Materno</label><input type="text" class="form-control" id="apellido_materno"></div>
                                    </div>
                                    <div class="mb-3"><label for="telefono" class="form-label">Teléfono</label><input type="tel" class="form-control" id="telefono"></div>
                                    <div class="mb-3"><label for="correo" class="form-label">Correo Electrónico*</label><input type="email" class="form-control" id="correo" required></div>
                                    
                                    <h5 class="mt-4 mb-3">Cambiar Contraseña</h5>
                                    <div class="mb-3"><label for="new-password" class="form-label">Nueva Contraseña</label><input type="password" class="form-control" id="new-password" placeholder="Dejar en blanco para no cambiar"></div>
                                    <div class="mb-3"><label for="confirm-password" class="form-label">Confirmar Nueva Contraseña</label><input type="password" class="form-control" id="confirm-password"></div>
                                    
                                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i>Guardar Cambios</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Librerías JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../js/adminjs/perfil.js"></script>
</body>
</html>
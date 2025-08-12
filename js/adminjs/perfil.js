$(document).ready(function() {

    // =================================================================================
    // 1. LÓGICA DE SESIÓN Y UI GENERAL (BARRA LATERAL)
    // =================================================================================

    function gestionarSesionYUI() {
        return new Promise((resolve, reject) => {
            // La ruta es relativa al HTML (admin/perfil.html), por lo que sube 1 nivel
            $.ajax({
                url: '../php/verificar_sesion.php', 
                type: 'GET',
                dataType: 'json',
                success: function(session) {
                    if (session.activa && session.rol === 'admin') {
                        $('#user-email').text(session.nombre_completo || session.correo || 'Usuario');
                        
                        // Lógica de Logout simplificada y corregida
                        $('#btn-logout').on('click', function(e) {
                            // 1. Prevenir la navegación inmediata del enlace
                            e.preventDefault(); 
                            
                            // 2. Guardar la URL del enlace
                            const logoutUrl = $(this).attr('href'); 

                            // 3. Mostrar la confirmación
                            Swal.fire({
                                title: '¿Cerrar sesión?',
                                icon: 'question',
                                showCancelButton: true,
                                confirmButtonText: 'Sí, salir',
                                cancelButtonText: 'Cancelar',
                                confirmButtonColor: '#d33'
                            }).then((result) => {
                                // 4. Si el usuario confirma, redirigir a la URL del enlace
                                if (result.isConfirmed) {
                                    window.location.href = logoutUrl;
                                }
                            });
                        });
                        resolve(session);
                    } else {
                        Swal.fire({ icon: 'error', title: 'Acceso Denegado', text: 'Sesión expirada o sin permisos.' })
                           .then(() => window.location.href = '../login.html');
                        reject('Sesión no válida');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error("Error en AJAX al verificar sesión:", jqXHR.responseText);
                    Swal.fire({ 
                        icon: 'error', 
                        title: 'Error de Conexión', 
                        text: 'No se pudo verificar la sesión. Revisa la consola (F12).' 
                    }).then(() => {
                        window.location.href = '../login.html';
                    });
                    reject('Error de conexión');
                }
            });
        });
    }

    // =================================================================================
    // 2. LÓGICA ESPEĆIFICA DE LA PÁGINA (MI PERFIL)
    // =================================================================================
    
    // La ruta es relativa al HTML, por lo que sube 1 nivel para entrar a php/adminphp
    const API_URL = '../php/adminphp/perfil_data.php';

    // --- Funciones de Alerta (Unificadas con SweetAlert2) ---
    const showSuccess = message => Swal.fire({ icon: 'success', title: '¡Éxito!', text: message, showConfirmButton: false, timer: 2000 });
    const showError = message => Swal.fire({ icon: 'error', title: 'Error', text: message });
    const showUpdatingAlert = title => Swal.fire({ title: title, allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    // --- Carga de Datos y Lógica del Formulario ---
    function loadProfileData() {
        showUpdatingAlert('Cargando perfil...');
        $.getJSON(API_URL, function(response) {
            Swal.close();
            if (response.success) {
                updateProfileUI(response.data);
            } else {
                showError(response.message || 'No se pudieron cargar los datos del perfil.');
            }
        }).fail(function(jqXHR) {
            Swal.close();
            console.error("Error en AJAX al cargar perfil:", jqXHR.responseText);
            showError('Error de conexión al cargar el perfil. Revisa la consola (F12).');
        });
    }

    function updateProfileUI(data) {
        const nombreCompleto = `${data.nombre || ''} ${data.apellido_paterno || ''} ${data.apellido_materno || ''}`.trim();
        const iniciales = `${data.nombre || '?'} ${data.apellido_paterno || ''}`.trim();

        $('#profile-name').text(nombreCompleto || 'Usuario');
        $('#profile-email').text(data.correo || 'sin-correo@ejemplo.com');
        $('#profile-img').attr('src', `https://ui-avatars.com/api/?name=${encodeURIComponent(iniciales)}&background=8856dd&color=fff&size=150`);
        
        $('#nombre').val(data.nombre || '');
        $('#apellido_paterno').val(data.apellido_paterno || '');
        $('#apellido_materno').val(data.apellido_materno || '');
        $('#telefono').val(data.telefono || '');
        $('#correo').val(data.correo || '');
    }

    function configurarEventListeners() {
        $('#profile-form').on('submit', function(e) {
            e.preventDefault();

            const newPassword = $('#new-password').val();
            const confirmPassword = $('#confirm-password').val();

            if (newPassword && newPassword !== confirmPassword) {
                showError('Las nuevas contraseñas no coinciden.');
                return;
            }
            if ($('#profile-form')[0].checkValidity() === false) {
                $(this).addClass('was-validated');
                return;
            }

            const formData = {
                nombre: $('#nombre').val(),
                apellido_paterno: $('#apellido_paterno').val(),
                apellido_materno: $('#apellido_materno').val(),
                telefono: $('#telefono').val(),
                correo: $('#correo').val(),
                newPassword: newPassword,
                confirmPassword: confirmPassword
            };

            showUpdatingAlert('Guardando cambios...');
            $.ajax({
                url: API_URL, type: 'POST', contentType: 'application/json',
                data: JSON.stringify(formData), dataType: 'json',
                success: function(response) {
                    Swal.close();
                    if (response.success) {
                        showSuccess(response.message || 'Perfil actualizado correctamente.');
                        $('#new-password').val('');
                        $('#confirm-password').val('');
                        gestionarSesionYUI().then(loadProfileData);
                    } else {
                        showError(response.message || 'No se pudieron guardar los cambios.');
                    }
                },
                error: function(jqXHR) {
                    Swal.close();
                    console.error("Error en AJAX al guardar perfil:", jqXHR.responseText);
                    showError('Error de conexión al guardar el perfil. Revisa la consola (F12).');
                }
            });
        });
    }

    // =================================================================================
    // 4. FUNCIÓN PRINCIPAL DE INICIALIZACIÓN
    // =================================================================================

    async function main() {
        try {
            await gestionarSesionYUI();
            loadProfileData();
            configurarEventListeners();
        } catch (error) {
            console.error("Inicialización detenida:", error);
        }
    }

    // Iniciar la aplicación
    main();
});

$(document).ready(function() {
    // Configuración base URL
    const baseUrl = '../php/adminphp/gestion_vinculacion.php';
    const SESSION_API = '../php/verificar_sesion.php';
    const LOGOUT_URL = '../php/cerrar_sesion.php';
    
    // Elementos del DOM
    const loadingOverlay = $('.loading-overlay');
    const btnRefresh = $('#btn-refresh');
    const btnLogout = $('#btn-logout');
    const userEmailElement = $('#user-email');
    const userRoleElement = $('#user-role');

    // --- Inicialización ---
    checkSessionAndLoadData();

    // --- Event Listeners ---
    btnRefresh.on('click', function() {
        tablaVinculacion.ajax.reload(null, false);
        showToast('success', 'Datos actualizados', 'La información se ha actualizado correctamente');
    });

    btnLogout.on('click', confirmLogout);

    // --- Funciones Principales ---
    function checkSessionAndLoadData() {
        showLoading(true);
        $.ajax({
            url: SESSION_API,
            method: 'GET',
            dataType: 'json',
            success: function(sessionData) {
                if (sessionData.activa && sessionData.rol === 'admin') {
                    updateUserInfo(sessionData);
                    initDataTable();
                } else {
                    showError('Sesión no válida o rol no autorizado. Redirigiendo al login.');
                    setTimeout(redirectToLogin, 2000);
                }
            },
            error: function(xhr) {
                console.error('Error al verificar la sesión:', xhr);
                showError('Error al verificar la sesión. Por favor, inténtalo de nuevo.');
                setTimeout(redirectToLogin, 2000);
            },
            complete: function() {
                showLoading(false);
            }
        });
    }

    function initDataTable() {
        window.tablaVinculacion = $('#tablaVinculacion').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: baseUrl + '?action=listar',
                type: 'GET',
                dataType: 'json',
                dataSrc: 'data',
                error: function(xhr, status, error) {
                    console.error('Error en AJAX:', status, error);
                    showError('No se pudieron cargar los datos. Ver consola para detalles.');
                }
            },
            columns: [
                { data: 'nombre', title: 'Nombre' },
                { data: 'apellido_paterno', title: 'Ap. Paterno' },
                { data: 'apellido_materno', title: 'Ap. Materno' },
                { data: 'correo', title: 'Correo' },
                { data: 'telefono', title: 'Teléfono' },
                { 
                    data: 'activo', 
                    title: 'Estado',
                    render: function(data) {
                        return data == 1 ? 
                            '<span class="badge bg-success">Activo</span>' : 
                            '<span class="badge bg-secondary">Inactivo</span>';
                    }
                },
                {
                    data: null,
                    title: 'Acciones',
                    render: function(data, type, row) {
                        return `
                            <div class="btn-group">
                                <button class="btn btn-sm ${row.activo == 1 ? 'btn-warning' : 'btn-success'} btn-estado" 
                                        data-correo="${row.correo}" 
                                        title="${row.activo == 1 ? 'Desactivar' : 'Activar'}">
                                    <i class="bi ${row.activo == 1 ? 'bi-toggle-on' : 'bi-toggle-off'}"></i>
                                </button>
                                <button class="btn btn-sm btn-primary btn-editar" data-correo="${row.correo}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-danger btn-eliminar" data-correo="${row.correo}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        `;
                    }
                }
            ],
            language: {
                "decimal": "",
                "emptyTable": "No hay datos disponibles en la tabla",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                "infoEmpty": "Mostrando 0 a 0 de 0 registros",
                "infoFiltered": "(filtrado de _MAX_ registros totales)",
                "infoPostFix": "",
                "thousands": ",",
                "lengthMenu": "Mostrar _MENU_ registros por página",
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "search": "Buscar:",
                "zeroRecords": "No se encontraron registros coincidentes",
                "paginate": {
                    "first": "Primero",
                    "last": "Último",
                    "next": "Siguiente",
                    "previous": "Anterior"
                }
            }
        });

        // Editar - Buscamos por correo
        $('#tablaVinculacion').on('click', '.btn-editar', function() {
            const correo = $(this).data('correo');
            
            $.ajax({
                url: baseUrl + '?action=obtener&correo=' + encodeURIComponent(correo),
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        $('#vinculacion_id').val(response.data.id);
                        $('#nombre').val(response.data.nombre);
                        $('#apellido_paterno').val(response.data.apellido_paterno);
                        $('#apellido_materno').val(response.data.apellido_materno || '');
                        $('#telefono').val(response.data.telefono || '');
                        $('#correo').val(response.data.correo);
                        $('#passwordHelp').text('Dejar en blanco para no cambiar la contraseña');
                        $('#modalVinculacionLabel').text('Editar Personal de Vinculación');
                        $('#modalVinculacion').modal('show');
                    } else {
                        showError(response.message || 'No se pudo cargar la información');
                    }
                },
                error: function(xhr) {
                    showError('Error al obtener datos: ' + xhr.statusText);
                }
            });
        });

        // Cambiar estado (activar/desactivar)
        $('#tablaVinculacion').on('click', '.btn-estado', function() {
            const correo = $(this).data('correo');
            const boton = $(this);
            
            Swal.fire({
                title: '¿Cambiar estado?',
                text: "Esta acción cambiará el estado del usuario",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, cambiar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: baseUrl + '?action=cambiar_estado',
                        method: 'POST',
                        data: { correo: correo },
                        dataType: 'json',
                        success: function(response) {
                            if(response.success) {
                                showToast('success', 'Estado actualizado', response.message);
                                tablaVinculacion.ajax.reload(null, false);
                            } else {
                                showError(response.message);
                            }
                        },
                        error: function(xhr) {
                            showError('Error al cambiar estado: ' + xhr.statusText);
                        }
                    });
                }
            });
        });

        // Eliminar - Buscamos por correo
        $('#tablaVinculacion').on('click', '.btn-eliminar', function() {
            const correo = $(this).data('correo');
            
            Swal.fire({
                title: '¿Eliminar registro?',
                text: "Esta acción no se puede deshacer",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: baseUrl + '?action=eliminar',
                        method: 'POST',
                        data: { correo: correo },
                        dataType: 'json',
                        success: function(response) {
                            if(response.success) {
                                showToast('success', 'Eliminado', response.message);
                                tablaVinculacion.ajax.reload(null, false);
                            } else {
                                showError(response.message);
                            }
                        },
                        error: function(xhr) {
                            showError('Error al eliminar: ' + xhr.statusText);
                        }
                    });
                }
            });
        });
    }

    // Botón Agregar
    $('#btnAgregarVinculacion').click(function() {
        $('#formVinculacion')[0].reset();
        $('#vinculacion_id').val('');
        $('#modalVinculacionLabel').text('Agregar Personal de Vinculación');
        $('#passwordHelp').text('Contraseña inicial: "Vinculacion123" (puede cambiarla después)');
        $('#modalVinculacion').modal('show');
    });

    // Guardar
    $('#btnGuardarVinculacion').click(function() {
        const form = $('#formVinculacion')[0];
        
        if(form.checkValidity()) {
            const formData = {
                id: $('#vinculacion_id').val(),
                nombre: $('#nombre').val(),
                apellido_paterno: $('#apellido_paterno').val(),
                apellido_materno: $('#apellido_materno').val(),
                telefono: $('#telefono').val(),
                correo: $('#correo').val(),
                contrasena: $('#contrasena').val()
            };
            
            const action = formData.id ? 'actualizar' : 'crear';
            
            $.ajax({
                url: baseUrl + '?action=' + action,
                method: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        showToast('success', 'Éxito', response.message);
                        tablaVinculacion.ajax.reload(null, false);
                        $('#modalVinculacion').modal('hide');
                    } else {
                        showError(response.message);
                    }
                },
                error: function(xhr) {
                    showError('Error en el servidor: ' + xhr.statusText);
                }
            });
        } else {
            form.reportValidity();
        }
    });

    // --- Funciones de UI ---
    function updateUserInfo(userInfo) {
        userEmailElement.text(userInfo.nombre_completo || userInfo.correo || 'Usuario no identificado');
        // userRoleElement.text(userInfo.rol.charAt(0).toUpperCase() + userInfo.rol.slice(1));
    }

    function showLoading(show) {
        loadingOverlay.css('display', show ? 'flex' : 'none');
    }

    function showToast(icon, title, text) {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });
        
        Toast.fire({
            icon: icon,
            title: title,
            text: text
        });
    }

    function showError(message) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: message,
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#dc3545'
        });
    }

    function redirectToLogin() {
        window.location.href = '../login.html';
    }

    function confirmLogout() {
        Swal.fire({
            title: '¿Cerrar sesión?',
            text: "¿Estás seguro de que deseas salir del sistema?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, cerrar sesión',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                showLoading(true);
                $.ajax({
                    url: LOGOUT_URL,
                    method: 'POST',
                    dataType: 'json',
                    success: function(response) {
                        if(response.success) {
                            redirectToLogin();
                        } else {
                            showError(response.message || 'Error desconocido al cerrar sesión.');
                        }
                    },
                    error: function(xhr) {
                        showError('No se pudo cerrar la sesión correctamente.');
                    },
                    complete: function() {
                        showLoading(false);
                    }
                });
            }
        });
    }
});
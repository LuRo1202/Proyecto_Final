$(document).ready(function() {

    // =================================================================================
    // 1. LÓGICA DE SESIÓN Y UI GENERAL (BARRA LATERAL)
    // =================================================================================

    function gestionarSesionYUI() {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: '../php/verificar_sesion.php', type: 'GET', dataType: 'json',
                success: function(session) {
                    if (session.activa && session.rol === 'admin') {
                        $('#user-email').text(session.nombre_completo || session.correo || 'Usuario');
                        $('#btn-logout').on('click', function(e) {
                            e.preventDefault();
                            Swal.fire({
                                title: '¿Cerrar sesión?', icon: 'question', showCancelButton: true,
                                confirmButtonText: 'Sí, salir', cancelButtonText: 'Cancelar', confirmButtonColor: '#d33'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $.post('../php/cerrar_sesion.php', () => { window.location.href = '../login.html'; });
                                }
                            });
                        });
                        resolve();
                    } else {
                        Swal.fire({ icon: 'error', title: 'Acceso Denegado', text: 'Sesión expirada o sin permisos.' })
                           .then(() => window.location.href = '../login.html');
                        reject('Sesión no válida');
                    }
                },
                error: function() {
                    Swal.fire({ icon: 'error', title: 'Error de Conexión', text: 'No se pudo verificar la sesión.' })
                       .then(() => window.location.href = '../login.html');
                    reject('Error de conexión');
                }
            });
        });
    }

    // =================================================================================
    // 2. LÓGICA ESPEĆIFICA DE LA PÁGINA (GESTIÓN DE ADMINISTRADORES)
    // =================================================================================
    
    let adminAEliminar = null;
    let tablaAdmins;
    const api_url = '../php/adminphp/gestion_administradores.php';

    // --- Función de Alertas Personalizada (Mantenida) ---
    function showAlert(message, type = 'success') {
        $('.alert').remove();
        const alert = $(`<div class="alert alert-${type} alert-dismissible fade show" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 1050;">${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`);
        $('body').append(alert);
        setTimeout(() => alert.fadeOut('slow', () => alert.remove()), 5000);
    }

    // --- Inicialización de la Tabla ---
    function inicializarTabla() {
        const LENGUAJE_ESPANOL = { "sProcessing": "Procesando...", "sLengthMenu": "Mostrar _MENU_ registros", "sZeroRecords": "No se encontraron resultados", "sEmptyTable": "Ningún dato disponible", "sInfo": "Mostrando del _START_ al _END_ de _TOTAL_ registros", "sSearch": "Buscar:", "oPaginate": { "sFirst": "Primero", "sLast": "Último", "sNext": "Siguiente", "sPrevious": "Anterior" } };
        
        tablaAdmins = $('#tablaAdministradores').DataTable({
            ajax: { url: api_url, dataSrc: 'data' },
            columns: [
                { data: 'admin_id', visible: false }, { data: 'nombre' }, { data: 'apellido_paterno' },
                { data: 'apellido_materno' }, { data: 'correo' },
                { data: 'ultimo_login', render: data => data ? new Date(data).toLocaleString('es-MX') : 'Nunca' },
                { data: 'activo', orderable: false, render: (data, type, row) => `<button class="btn btn-sm btn-cambiar-estado ${data == 1 ? 'btn-success' : 'btn-danger'}" data-id="${row.admin_id}" data-estado-actual="${data}">${data == 1 ? 'Activo' : 'Inactivo'}</button>` },
                { data: null, orderable: false, render: (data, type, row) => `<div class="btn-action-group"><button class="btn btn-outline-primary btn-sm btn-editar" data-id="${row.admin_id}" title="Editar"><i class="bi bi-pencil"></i></button><button class="btn btn-outline-danger btn-sm btn-eliminar" data-id="${row.admin_id}" title="Eliminar"><i class="bi bi-trash"></i></button></div>` }
            ],
            language: LENGUAJE_ESPANOL, responsive: true
        });
    }

    // --- Funciones de Ayuda y Modales ---
    function resetForm() {
        $('#formAdmin')[0].reset();
        $('#admin_id').val('');
        $('#formAdmin').removeClass('was-validated');
    }

    // --- Lógica de Eventos ---
    function configurarEventListeners() {
        $('#btnAgregarAdmin').click(function() {
            resetForm();
            $('#modalAdminLabel').text('Agregar Administrador');
            $('#contrasena').prop('required', true);
            $('#passwordHelp').text('La contraseña es requerida para nuevos administradores.');
            $('#modalAdmin').modal('show');
        });

        $('#modalAdmin').on('hidden.bs.modal', resetForm);

        $('#tablaAdministradores').on('click', '.btn-cambiar-estado', function() {
            const boton = $(this);
            const id = boton.data('id');
            const nuevoEstado = boton.data('estado-actual') == 1 ? 0 : 1;
            boton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
            $.ajax({
                url: api_url, method: 'PUT', contentType: 'application/json',
                data: JSON.stringify({ admin_id: id, activo: nuevoEstado }), dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        tablaAdmins.ajax.reload(null, false);
                        showAlert(response.message, 'success');
                    } else {
                        showAlert(response.message, 'danger');
                        boton.prop('disabled', false).text(boton.data('estado-actual') == 1 ? 'Activo' : 'Inactivo');
                    }
                },
                error: function() {
                    showAlert('Error de conexión.', 'danger');
                    boton.prop('disabled', false).text(boton.data('estado-actual') == 1 ? 'Activo' : 'Inactivo');
                }
            });
        });

        $('#tablaAdministradores').on('click', '.btn-editar', function() {
            const id = $(this).data('id');
            $.getJSON(`${api_url}?id=${id}`, function(response) {
                if (response.success && response.data) {
                    const admin = response.data;
                    resetForm();
                    $('#modalAdminLabel').text('Editar Administrador');
                    $('#admin_id').val(admin.admin_id);
                    $('#nombre').val(admin.nombre); $('#apellido_paterno').val(admin.apellido_paterno);
                    $('#apellido_materno').val(admin.apellido_materno); $('#telefono').val(admin.telefono);
                    $('#correo').val(admin.correo);
                    $('#contrasena').prop('required', false);
                    $('#passwordHelp').text('Dejar en blanco para no cambiar la contraseña.');
                    $('#modalAdmin').modal('show');
                } else {
                    showAlert(response.message || "No se encontraron datos.", 'danger');
                }
            });
        });

        $('#tablaAdministradores').on('click', '.btn-eliminar', function() {
            adminAEliminar = $(this).data('id');
            $('#modalEliminarAdmin').modal('show');
        });

        $('#btnGuardarAdmin').click(function() {
            if (!$('#formAdmin')[0].checkValidity()) {
                $('#formAdmin').addClass('was-validated');
                return;
            }
            const formData = {
                admin_id: $('#admin_id').val(), nombre: $('#nombre').val().trim(),
                apellido_paterno: $('#apellido_paterno').val().trim(), apellido_materno: $('#apellido_materno').val().trim(),
                telefono: $('#telefono').val().trim(), correo: $('#correo').val().trim(),
                contrasena: $('#contrasena').val(),
            };
            const method = formData.admin_id ? 'PUT' : 'POST';
            $.ajax({
                url: api_url, method: method, contentType: 'application/json',
                data: JSON.stringify(formData), dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        tablaAdmins.ajax.reload(null, false);
                        $('#modalAdmin').modal('hide');
                        showAlert(response.message, 'success');
                    } else { showAlert(response.message, 'danger'); }
                },
                error: () => showAlert('Error de conexión.', 'danger')
            });
        });

        $('#btnConfirmarEliminar').click(function() {
            if (!adminAEliminar) return;
            $.ajax({
                url: api_url, method: 'DELETE', contentType: 'application/json',
                data: JSON.stringify({ admin_id: adminAEliminar }), dataType: 'json',
                success: function(response) {
                    $('#modalEliminarAdmin').modal('hide');
                    if (response.success) {
                        tablaAdmins.ajax.reload(null, false);
                        showAlert(response.message, 'success');
                    } else { showAlert(response.message, 'danger'); }
                },
                error: () => showAlert('Error de conexión.', 'danger')
            });
        });
    }

    // =================================================================================
    // 4. FUNCIÓN PRINCIPAL DE INICIALIZACIÓN
    // =================================================================================

    async function main() {
        try {
            await gestionarSesionYUI();
            inicializarTabla();
            configurarEventListeners();
        } catch (error) {
            console.error("Inicialización detenida:", error);
        }
    }

    // Iniciar la aplicación
    main();
});

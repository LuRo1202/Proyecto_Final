$(document).ready(function() {

    // =================================================================================
    // 1. LÓGICA DE SESIÓN Y UI GENERAL (BARRA LATERAL)
    // =================================================================================

    function gestionarSesionYUI() {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: '../php/verificar_sesion.php',
                type: 'GET',
                dataType: 'json',
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
    // 2. LÓGICA ESPEĆIFICA DE LA PÁGINA (GESTIÓN DE RESPONSABLES)
    // =================================================================================
    
    let responsableAEliminar = null;
    let tablaResponsables;

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
        
        tablaResponsables = $('#tablaResponsables').DataTable({
            ajax: { url: '../php/adminphp/obtener_responsables.php', dataSrc: 'data' },
            columns: [
                { data: 'responsable_id', visible: false }, { data: 'nombre' }, { data: 'apellido_paterno' },
                { data: 'apellido_materno' }, { data: 'cargo' }, { data: 'departamento' }, { data: 'correo' },
                { data: 'activo', orderable: false, render: (data, type, row) => `<button class="btn btn-sm btn-cambiar-estado ${data == 1 ? 'btn-success' : 'btn-danger'}" data-id="${row.responsable_id}" data-estado-actual="${data}">${data == 1 ? 'Activo' : 'Inactivo'}</button>` },
                { data: null, orderable: false, render: (data, type, row) => `<div class="btn-action-group"><button class="btn btn-outline-primary btn-sm btn-editar" data-id="${row.responsable_id}" title="Editar"><i class="bi bi-pencil"></i></button><button class="btn btn-outline-danger btn-sm btn-eliminar" data-id="${row.responsable_id}" title="Eliminar"><i class="bi bi-trash"></i></button></div>` }
            ],
            language: LENGUAJE_ESPANOL, responsive: true
        });
    }

    // --- Funciones de Ayuda y Modales ---
    function resetForm() {
        $('#formResponsable')[0].reset();
        $('#responsable_id').val('');
        $('#formResponsable').removeClass('was-validated');
    }

    // --- Lógica de Eventos ---
    function configurarEventListeners() {
        $('#btnAgregarResponsable').click(function() {
            resetForm();
            $('#modalResponsableLabel').text('Agregar Responsable');
            $('#contrasena').prop('required', true);
            $('#passwordHelp').text('La contraseña es requerida para nuevos responsables.');
            $('#modalResponsable').modal('show');
        });

        $('#modalResponsable').on('hidden.bs.modal', resetForm);

        $('#tablaResponsables').on('click', '.btn-cambiar-estado', function() {
            const boton = $(this);
            const id = boton.data('id');
            const estadoActual = boton.data('estado-actual');
            const nuevoEstado = estadoActual == 1 ? 0 : 1;
            boton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
            $.ajax({
                url: '../php/adminphp/cambiar_estado_responsable.php', method: 'POST',
                contentType: 'application/json', data: JSON.stringify({ responsable_id: id, activo: nuevoEstado }),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        tablaResponsables.ajax.reload(null, false);
                        showAlert(response.message, 'success');
                    } else {
                        showAlert(response.message, 'danger');
                        boton.prop('disabled', false).text(estadoActual == 1 ? 'Activo' : 'Inactivo');
                    }
                },
                error: function() {
                    showAlert('Error de conexión.', 'danger');
                    boton.prop('disabled', false).text(estadoActual == 1 ? 'Activo' : 'Inactivo');
                }
            });
        });

        $('#tablaResponsables').on('click', '.btn-editar', function() {
            const id = $(this).data('id');
            $.getJSON('../php/adminphp/obtener_responsable.php', { id }, function(response) {
                if (response.success) {
                    const resp = response.data;
                    resetForm();
                    $('#modalResponsableLabel').text('Editar Responsable');
                    $('#responsable_id').val(resp.responsable_id);
                    $('#nombre').val(resp.nombre); $('#apellido_paterno').val(resp.apellido_paterno);
                    $('#apellido_materno').val(resp.apellido_materno); $('#cargo').val(resp.cargo);
                    $('#departamento').val(resp.departamento); $('#telefono').val(resp.telefono);
                    $('#correo').val(resp.correo);
                    $('#contrasena').prop('required', false);
                    $('#passwordHelp').text('Dejar en blanco para no cambiar la contraseña.');
                    $('#modalResponsable').modal('show');
                } else {
                    showAlert(response.message, 'danger');
                }
            });
        });

        $('#tablaResponsables').on('click', '.btn-eliminar', function() {
            responsableAEliminar = $(this).data('id');
            $('#modalEliminarResponsable').modal('show');
        });

        $('#btnGuardarResponsable').click(function() {
            if ($('#formResponsable')[0].checkValidity() === false) {
                $('#formResponsable').addClass('was-validated');
                return;
            }
            const formData = {
                responsable_id: $('#responsable_id').val(), nombre: $('#nombre').val().trim(),
                apellido_paterno: $('#apellido_paterno').val().trim(), apellido_materno: $('#apellido_materno').val().trim(),
                cargo: $('#cargo').val().trim(), departamento: $('#departamento').val().trim(),
                telefono: $('#telefono').val().trim(), correo: $('#correo').val().trim(),
                contrasena: $('#contrasena').val(),
            };
            const url = formData.responsable_id ? '../php/adminphp/editar_responsable.php' : '../php/adminphp/agregar_responsable.php';
            $.ajax({
                url: url, method: 'POST', contentType: 'application/json',
                data: JSON.stringify(formData), dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        tablaResponsables.ajax.reload(null, false);
                        $('#modalResponsable').modal('hide');
                        showAlert(response.message, 'success');
                    } else {
                        showAlert(response.message, 'danger');
                    }
                },
                error: function() { showAlert('Error de conexión.', 'danger'); }
            });
        });

        $('#btnConfirmarEliminar').click(function() {
            if (!responsableAEliminar) return;
            $.ajax({
                url: '../php/adminphp/eliminar_responsable.php', method: 'POST',
                contentType: 'application/json', data: JSON.stringify({ responsable_id: responsableAEliminar }),
                dataType: 'json',
                success: function(response) {
                    $('#modalEliminarResponsable').modal('hide');
                    if (response.success) {
                        tablaResponsables.ajax.reload(null, false);
                        showAlert(response.message, 'success');
                    } else {
                        showAlert(response.message, 'danger');
                    }
                },
                error: function() { showAlert('Error de conexión.', 'danger'); }
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

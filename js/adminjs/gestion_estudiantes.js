$(document).ready(function() {

    // =================================================================================
    // 1. LÓGICA DE SESIÓN Y UI GENERAL (BARRA LATERAL)
    // =================================================================================

    /**
     * Verifica la sesión del admin. Si es válida, actualiza la UI y permite que la página continúe.
     * Si no, muestra una alerta modal (con Swal) y redirige al login.
     * @returns {Promise} Una promesa que se resuelve si la sesión es válida.
     */
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
                        resolve(); // Sesión válida, continuar.
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
    // 2. LÓGICA ESPEĆIFICA DE LA PÁGINA (GESTIÓN DE ESTUDIANTES)
    // =================================================================================
    
    let estudianteAEliminar = null;
    let tablaEstudiantes;

    // --- Función de Alertas Personalizada (RESTAURADA) ---
    function showAlert(message, type = 'success') {
        $('.alert').remove();
        const alert = $(`
            <div class="alert alert-${type} alert-dismissible fade show" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 1050; min-width: 250px;">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `);
        $('body').append(alert);
        setTimeout(() => alert.fadeOut('slow', () => alert.remove()), 5000);
    }

    // --- Inicialización de la Tabla ---
    function inicializarTabla() {
        const LENGUAJE_ESPANOL = { "sProcessing": "Procesando...", "sLengthMenu": "Mostrar _MENU_ registros", "sZeroRecords": "No se encontraron resultados", "sEmptyTable": "Ningún dato disponible", "sInfo": "Mostrando _START_ al _END_ de _TOTAL_", "sInfoEmpty": "Mostrando 0 de 0", "sInfoFiltered": "(filtrado de _MAX_)", "sSearch": "Buscar:", "oPaginate": { "sFirst": "Primero", "sLast": "Último", "sNext": "Siguiente", "sPrevious": "Anterior" } };
        
        tablaEstudiantes = $('#tablaEstudiantes').DataTable({
            ajax: { url: '../php/adminphp/obtener_estudiantes.php', dataSrc: 'data' },
            columns: [
                { data: 'estudiante_id', visible: false }, { data: 'matricula' }, { data: 'nombre' },
                { data: 'apellido_paterno' }, { data: 'apellido_materno' }, { data: 'carrera' },
                { data: 'cuatrimestre', className: 'text-center', render: data => data ? `${data}°` : '' },
                { data: null, className: 'text-center', render: data => `<span class="badge bg-primary">${data.horas_completadas || 0} / ${data.horas_requeridas || 480}</span>` },
                { data: 'activo', className: 'text-center', render: (data, type, row) => `<button class="btn btn-sm btn-cambiar-estado ${data == 1 ? 'btn-success' : 'btn-danger'}" data-id="${row.estudiante_id}" data-estado-actual="${data}">${data == 1 ? 'Activo' : 'Inactivo'}</button>` },
                { data: null, orderable: false, render: (data, type, row) => `<div class="btn-action-group"><button class="btn btn-outline-primary btn-sm btn-editar" data-id="${row.estudiante_id}" title="Editar"><i class="bi bi-pencil"></i></button><button class="btn btn-outline-info btn-sm btn-registros" data-id="${row.estudiante_id}" title="Ver Registros"><i class="bi bi-clock-history"></i></button><button class="btn btn-outline-danger btn-sm btn-eliminar" data-id="${row.estudiante_id}" title="Eliminar"><i class="bi bi-trash"></i></button></div>` }
            ],
            language: LENGUAJE_ESPANOL, responsive: true, autoWidth: false
        });
    }

    // --- Funciones de Ayuda y Modales ---
    function resetForm() {
        $('#formEstudiante')[0].reset();
        $('#estudiante_id').val('');
        $('#contrasena').prop('required', true);
    }
    
    function getBadgeClass(estado) {
        switch((estado || '').toLowerCase()) {
            case 'aprobado': return 'bg-success';
            case 'rechazado': return 'bg-danger';
            case 'pendiente': return 'bg-warning';
            default: return 'bg-secondary';
        }
    }

    // --- Lógica de Eventos ---
    function configurarEventListeners() {
        $('#btnAgregarEstudiante').click(function() {
            resetForm();
            $('#modalEstudianteLabel').text('Agregar Estudiante');
            $('#modalEstudiante').modal('show');
        });
        
        $('#modalEstudiante').on('hidden.bs.modal', resetForm);

        $('#tablaEstudiantes').on('click', '.btn-cambiar-estado', function() {
            const boton = $(this);
            const estudiante_id = boton.data('id');
            const estadoActual = boton.data('estado-actual');
            const nuevoEstado = estadoActual == 1 ? 0 : 1;
            boton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
            $.ajax({
                url: '../php/adminphp/cambiar_estado_estudiante.php', method: 'POST', contentType: 'application/json',
                data: JSON.stringify({ estudiante_id: estudiante_id, activo: nuevoEstado }), dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        tablaEstudiantes.ajax.reload(null, false);
                        showAlert(response.message, 'success');
                    } else {
                        showAlert(response.message, 'danger');
                        boton.prop('disabled', false).text(estadoActual == 1 ? 'Activo' : 'Inactivo');
                    }
                },
                error: function() {
                    showAlert('Error de conexión al cambiar el estado.', 'danger');
                    boton.prop('disabled', false).text(estadoActual == 1 ? 'Activo' : 'Inactivo');
                }
            });
        });

        $('#tablaEstudiantes').on('click', '.btn-editar', function() {
            const estudiante_id = $(this).data('id');
            $.getJSON('../php/adminphp/obtener_estudiante.php', { id: estudiante_id }, function(response) {
                if (response.success) {
                    const est = response.data;
                    $('#modalEstudianteLabel').text('Editar Estudiante');
                    $('#estudiante_id').val(est.estudiante_id);
                    $('#matricula').val(est.matricula); $('#nombre').val(est.nombre);
                    $('#apellido_paterno').val(est.apellido_paterno); $('#apellido_materno').val(est.apellido_materno);
                    $('#carrera').val(est.carrera || ''); $('#cuatrimestre').val(est.cuatrimestre || '');
                    $('#telefono').val(est.telefono || ''); $('#correo').val(est.correo || '');
                    $('#activo').prop('checked', est.activo == 1);
                    $('#contrasena').val('').prop('required', false);
                    $('#modalEstudiante').modal('show');
                }
            });
        });

        $('#tablaEstudiantes').on('click', '.btn-eliminar', function() {
            estudianteAEliminar = $(this).data('id');
            $('#modalEliminarEstudiante').modal('show');
        });

        $('#tablaEstudiantes').on('click', '.btn-registros', function() {
            const estudiante_id = $(this).data('id');
            $.getJSON('../php/adminphp/obtener_registros_estudiante.php', { id: estudiante_id }, function(response) {
                const tbody = $('#tablaRegistros tbody').empty();
                if (response.success && response.data.length > 0) {
                    response.data.forEach(r => {
                        tbody.append(`<tr><td>${r.fecha||'N/A'}</td><td>${r.hora_entrada||'N/A'}</td><td>${r.hora_salida||'N/A'}</td><td>${r.horas_acumuladas||'0'}</td><td><span class="badge ${getBadgeClass(r.estado)}">${r.estado||'pendiente'}</span></td><td>${r.responsable||'N/A'}</td></tr>`);
                    });
                } else {
                    tbody.append('<tr><td colspan="6" class="text-center">Este estudiante no tiene registros.</td></tr>');
                }
                $('#modalRegistros').modal('show');
            });
        });

        $('#btnGuardarEstudiante').click(function() {
            const formData = {
                estudiante_id: $('#estudiante_id').val(), matricula: $('#matricula').val().trim(),
                nombre: $('#nombre').val().trim(), apellido_paterno: $('#apellido_paterno').val().trim(),
                apellido_materno: $('#apellido_materno').val().trim(), carrera: $('#carrera').val(),
                cuatrimestre: $('#cuatrimestre').val(), telefono: $('#telefono').val().trim(),
                correo: $('#correo').val().trim(), contrasena: $('#contrasena').val(),
                activo: $('#activo').is(':checked') ? 1 : 0
            };
            const url = formData.estudiante_id ? '../php/adminphp/editar_estudiante.php' : '../php/adminphp/agregar_estudiante.php';
            $.ajax({
                url: url, method: 'POST', contentType: 'application/json', data: JSON.stringify(formData), dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        tablaEstudiantes.ajax.reload(null, false);
                        $('#modalEstudiante').modal('hide');
                        showAlert(response.message || 'Operación exitosa');
                    } else { showAlert(response.message || 'Error', 'danger'); }
                },
                error: function() { showAlert('Error de conexión.', 'danger'); }
            });
        });

        $('#btnConfirmarEliminar').click(function() {
            if (!estudianteAEliminar) return;
            $.ajax({
                url: '../php/adminphp/eliminar_estudiante.php', method: 'POST', contentType: 'application/json',
                data: JSON.stringify({ estudiante_id: estudianteAEliminar }), dataType: 'json',
                success: function(response) {
                    $('#modalEliminarEstudiante').modal('hide');
                    if (response.success) {
                        tablaEstudiantes.ajax.reload(null, false);
                        showAlert('Estudiante eliminado');
                    } else { showAlert(response.message || 'Error', 'danger'); }
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
            // Si la sesión es válida, se inicializa el resto de la página.
            inicializarTabla();
            configurarEventListeners();
        } catch (error) {
            // El error ya fue manejado (alerta y redirección).
            console.error("Inicialización detenida:", error);
        }
    }

    // Iniciar la aplicación
    main();
});

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
    // 2. LÓGICA ESPEĆIFICA DE LA PÁGINA (REGISTRO DE HORAS)
    // =================================================================================
    
    let tablaEstudiantes, tablaDetalle;
    const modalGestion = new bootstrap.Modal(document.getElementById('modalGestionEstudiante'));
    const modalAgregarEditar = new bootstrap.Modal(document.getElementById('modalAgregarEditarRegistro'));
    let currentStudentId = null;
    const API_URL = '../php/adminphp/registro_horas.php';

    // --- Función de Alertas Personalizada (para notificaciones en esquina) ---
    function showAlert(message, type = 'success') {
        $('.alert').remove();
        const alert = $(`
            <div class="alert alert-${type} alert-dismissible fade show" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 1050;">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `);
        $('body').append(alert);
        setTimeout(() => alert.fadeOut('slow', () => alert.remove()), 5000);
    }

    // --- Funciones Auxiliares ---
    const getBadgeClass = estado => ({ 'aprobado': 'bg-success', 'rechazado': 'bg-danger', 'pendiente': 'bg-warning text-dark' })[estado] || 'bg-secondary';
    const LENGUAJE_ESPANOL_DATATABLES = { "sProcessing": "Procesando...", "sLengthMenu": "Mostrar _MENU_ registros", "sZeroRecords": "No se encontraron resultados", "sEmptyTable": "Ningún dato disponible", "sInfo": "Mostrando _START_ al _END_ de _TOTAL_", "sInfoEmpty": "Mostrando 0 de 0", "sInfoFiltered": "(filtrado de _MAX_)", "sSearch": "Buscar:", "oPaginate": { "sFirst": "Primero", "sLast": "Último", "sNext": "Siguiente", "sPrevious": "Anterior" } };

    // --- Inicialización de DataTables ---
    function inicializarTablas() {
        tablaEstudiantes = $('#tablaEstudiantesHoras').DataTable({
            ajax: { url: `${API_URL}?accion=listar_estudiantes_progreso`, dataSrc: 'data' },
            columns: [
                { data: 'matricula' }, { data: 'nombre' }, { data: 'apellido_paterno' }, { data: 'apellido_materno' }, { data: 'carrera' },
                { data: null, render: data => {
                    const horas = parseFloat(data.horas_completadas || 0), req = parseFloat(data.horas_requeridas || 480);
                    const pct = req > 0 ? Math.min((horas / req) * 100, 100).toFixed(1) : 0;
                    return `${horas.toFixed(2)} / ${req} hrs<div class="progress mt-1"><div class="progress-bar" style="width: ${pct}%">${pct}%</div></div>`;
                }},
                { data: null, render: data => (parseFloat(data.horas_completadas || 0) >= parseFloat(data.horas_requeridas || 480)) ? '<span class="badge bg-success">Liberado</span>' : '<span class="badge bg-info text-dark">En Proceso</span>' },
                { data: null, orderable: false, render: data => `<button class="btn btn-primary btn-sm btn-gestionar" data-id="${data.estudiante_id}"><i class="bi bi-clock"></i> Gestionar</button>` }
            ],
            language: LENGUAJE_ESPANOL_DATATABLES, responsive: true, autoWidth: false
        });

        tablaDetalle = $('#tablaDetalleHoras').DataTable({
            data: [],
            columns: [
                { data: 'fecha', render: data => data ? new Date(data + 'T00:00:00').toLocaleDateString('es-MX', { year: 'numeric', month: 'short', day: 'numeric' }) : 'N/A' },
                { data: 'horas_acumuladas', render: data => data ? `${parseFloat(data).toFixed(2)} hrs` : 'N/A' },
                { data: 'estado', render: data => `<span class="badge ${getBadgeClass(data)}">${data.charAt(0).toUpperCase() + data.slice(1)}</span>` },
                { data: 'responsable_nombre', defaultContent: 'N/A' },
                { data: null, orderable: false, render: data => `<div class="btn-group btn-group-sm"><button class="btn btn-info btn-editar-registro" data-id="${data.registro_id}"><i class="bi bi-pencil"></i></button><button class="btn btn-danger btn-eliminar-registro" data-id="${data.registro_id}"><i class="bi bi-trash"></i></button></div>` }
            ],
            language: LENGUAJE_ESPANOL_DATATABLES, order: [[0, 'desc']], searching: false, lengthChange: false, pageLength: 5, responsive: true
        });
    }

    // --- Manejo de Modales y Eventos ---
    function abrirModalGestion(studentId) {
        currentStudentId = studentId;
        $.getJSON(`${API_URL}?accion=obtener_detalle_estudiante&id=${studentId}`, function(response) {
            if (!response.success) return showAlert(response.message || 'Error al cargar detalles.', 'danger');
            const { estudiante, registros } = response.data;
            $('#nombreEstudianteModal').text(`Gestión: ${estudiante.nombre} ${estudiante.apellido_paterno}`);
            const horas = parseFloat(estudiante.horas_completadas || 0), req = parseFloat(estudiante.horas_requeridas || 480);
            const pct = req > 0 ? Math.min((horas / req) * 100, 100).toFixed(1) : 0;
            $('#progresoContainer').html(`<strong>Progreso:</strong><div class="progress mt-1"><div class="progress-bar fs-6" style="width: ${pct}%">${pct}%</div></div>`);
            $('#btnLiberarServicio').prop('disabled', horas >= req);
            tablaDetalle.clear().rows.add(registros).draw();
            modalGestion.show();
        });
    }

    function cargarResponsables() {
        return $.getJSON(`${API_URL}?accion=listar_responsables`, function(response) {
            const select = $('#responsable_id_form').empty().append('<option value="" disabled selected>Seleccione...</option>');
            if (response.success) response.data.forEach(r => select.append(`<option value="${r.responsable_id}">${r.nombre_completo}</option>`));
        });
    }

    function configurarEventListeners() {
        $('#tablaEstudiantesHoras tbody').on('click', '.btn-gestionar', function() { abrirModalGestion($(this).data('id')); });
        
        // CORRECCIÓN: Se ajusta la lógica de la alerta del botón "Actualizar"
        $('#btnActualizarTabla').on('click', function() {
            Swal.fire({ 
                title: 'Actualizando...', 
                allowOutsideClick: false, 
                didOpen: () => Swal.showLoading() 
            });
            tablaEstudiantes.ajax.reload(function(json) {
                Swal.close();
                if (json && json.success === false) {
                    Swal.fire('Error', 'No se pudieron actualizar los datos.', 'error');
                } else {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Actualizado!',
                        showConfirmButton: false,
                        timer: 1500
                    });
                }
            }, false); // false para mantener la paginación actual
        });

        $('#modalGestionEstudiante').on('click', '#btnAgregarRegistro, .btn-editar-registro', function() {
            const registroId = $(this).data('id') || '';
            $('#formRegistroHoras')[0].reset();
            $('#registro_id').val(registroId);
            $('#estudiante_id_form').val(currentStudentId);
            cargarResponsables().then(() => {
                if (registroId) {
                    $('#modalAgregarEditarLabel').text('Editar Registro');
                    $.getJSON(`${API_URL}?accion=obtener_registro&id=${registroId}`, function(response) {
                        if (response.success) {
                            const reg = response.data;
                            $('#responsable_id_form').val(reg.responsable_id); $('#fecha_form').val(reg.fecha);
                            $('#hora_entrada_form').val(reg.hora_entrada); $('#hora_salida_form').val(reg.hora_salida);
                            $('#horas_acumuladas_form').val(reg.horas_acumuladas); $('#estado_form').val(reg.estado);
                            $('#observaciones_form').val(reg.observaciones);
                            modalAgregarEditar.show();
                        } else { showAlert(response.message, 'danger'); }
                    });
                } else {
                    $('#modalAgregarEditarLabel').text('Agregar Nuevo Registro');
                    const hoy = new Date();
                    const anio = hoy.getFullYear();
                    const mes = (hoy.getMonth() + 1).toString().padStart(2, '0');
                    const dia = hoy.getDate().toString().padStart(2, '0');
                    $('#fecha_form').val(`${anio}-${mes}-${dia}`);
                    modalAgregarEditar.show();
                }
            });
        });

        $('#hora_entrada_form, #hora_salida_form').on('change', function() {
            const entrada = $('#hora_entrada_form').val(), salida = $('#hora_salida_form').val();
            if (entrada && salida) {
                let fechaEntrada = new Date(`1970-01-01T${entrada}`);
                let fechaSalida = new Date(`1970-01-01T${salida}`);
                if (fechaSalida < fechaEntrada) fechaSalida.setDate(fechaSalida.getDate() + 1);
                const diffMs = fechaSalida - fechaEntrada;
                $('#horas_acumuladas_form').val(diffMs > 0 ? (diffMs / 3600000).toFixed(2) : '0.00');
            }
        });

        $('#btnGuardarRegistroHoras').on('click', function() {
            if (!$('#formRegistroHoras')[0].checkValidity()) return showAlert('Por favor, complete todos los campos requeridos (*).', 'warning');
            const data = {
                accion: $('#registro_id').val() ? 'editar_registro' : 'agregar_registro',
                registro_id: $('#registro_id').val(), estudiante_id: $('#estudiante_id_form').val(),
                responsable_id: $('#responsable_id_form').val(), fecha: $('#fecha_form').val(),
                hora_entrada: $('#hora_entrada_form').val(), hora_salida: $('#hora_salida_form').val(),
                horas_acumuladas: $('#horas_acumuladas_form').val(), estado: $('#estado_form').val(),
                observaciones: $('#observaciones_form').val()
            };
            $.post(API_URL, data, response => {
                if (response.success) {
                    showAlert(response.message, 'success');
                    modalAgregarEditar.hide();
                    tablaEstudiantes.ajax.reload(null, false);
                    abrirModalGestion(currentStudentId);
                } else { showAlert(response.message || 'Error al guardar.', 'danger'); }
            }, 'json').fail(() => showAlert('Error de comunicación.', 'danger'));
        });

        $('#tablaDetalleHoras tbody').on('click', '.btn-eliminar-registro', function() {
            const registroId = $(this).data('id');
            Swal.fire({
                title: '¿Estás seguro?', text: "Esta acción no se puede deshacer.", icon: 'warning',
                showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Sí, ¡eliminar!', cancelButtonText: 'Cancelar'
            }).then(result => {
                if (result.isConfirmed) {
                    $.post(API_URL, { accion: 'eliminar_registro', registro_id: registroId }, response => {
                        if (response.success) {
                            showAlert(response.message, 'success');
                            tablaEstudiantes.ajax.reload(null, false);
                            abrirModalGestion(currentStudentId);
                        } else { showAlert(response.message, 'danger'); }
                    }, 'json').fail(() => showAlert('Error de comunicación.', 'danger'));
                }
            });
        });

        $('#btnLiberarServicio').on('click', function() {
            Swal.fire({
                title: '¿Liberar Servicio Social?', text: "El estado del estudiante se marcará como 'Liberado'.", icon: 'question',
                showCancelButton: true, confirmButtonText: 'Sí, liberar', cancelButtonText: 'Cancelar'
            }).then(result => {
                if (result.isConfirmed) {
                    $.post(API_URL, { accion: 'liberar_servicio', estudiante_id: currentStudentId }, response => {
                        if (response.success) {
                            showAlert(response.message, 'success');
                            modalGestion.hide();
                            tablaEstudiantes.ajax.reload(null, false);
                        } else { showAlert(response.message, 'danger'); }
                    }, 'json').fail(() => showAlert('Error de comunicación.', 'danger'));
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
            inicializarTablas();
            configurarEventListeners();
        } catch (error) {
            console.error("Inicialización detenida:", error);
        }
    }

    // Iniciar la aplicación
    main();
});
$(document).ready(function() {
    // Configuración anti-caché global
    $.ajaxSetup({ 
        cache: false,
        headers: {
            'Cache-Control': 'no-cache, no-store, must-revalidate',
            'Pragma': 'no-cache',
            'Expires': '0'
        }
    });

    // Función global para cerrar sesión
    window.cerrarSesion = function() {
        fetch('../php/logout.php')
            .then(() => {
                if ($.fn.DataTable.isDataTable('#tablaRegistros')) {
                    $('#tablaRegistros').DataTable().destroy();
                }
                window.location.href = '../index.html?logout=1';
            })
            .catch(() => {
                window.location.href = '../index.html';
            });
    };

    // Verificar sesión antes de cargar contenido
    verificarSesion().then(() => {
        const urlParams = new URLSearchParams(window.location.search);
        const estudianteId = urlParams.get('id');
        
        if (!estudianteId) {
            mostrarError('No se especificó un estudiante');
            return;
        }

        cargarInformacionEstudiante(estudianteId);
    }).catch(error => {
        console.error('Error de sesión:', error);
        window.location.href = '../index.html?session_expired=1';
    });

    // Función para verificar sesión
    function verificarSesion() {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: '../php/verificar_sesion.php',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.activa && response.rol === 'encargado') {
                        // **ÚNICA MODIFICACIÓN: Actualizar nombre en la barra de navegación**
                        const userName = response.nombre_completo || response.correo || 'Usuario';
                        $('#user-name').text(userName);
                        
                        if (performance.navigation.type === 2) {
                            window.location.reload(true);
                        }
                        resolve();
                    } else {
                        reject('Sesión no válida');
                    }
                },
                error: function() {
                    reject('Error al verificar sesión');
                }
            });
        });
    }

    // Cargar información completa del estudiante
    function cargarInformacionEstudiante(estudianteId) {
        $.ajax({
            url: '../php/encargadophp/estudiante_detalle_operaciones.php',
            method: 'GET',
            data: {
                accion: 'obtenerDetalleCompleto',
                estudiante_id: estudianteId
            },
            success: function(response) {
                if (response.success && response.data) {
                    const estudiante = response.data;
                    const porcentaje = (estudiante.horas_completadas / estudiante.horas_requeridas * 100).toFixed(1);
                    
                    $('#nombreEstudiante').text(`${estudiante.nombre} ${estudiante.apellido_paterno || ''} ${estudiante.apellido_materno || ''}`);
                    $('#matriculaEstudiante').text(estudiante.matricula);
                    $('#carreraEstudiante').text(estudiante.carrera);
                    $('#cuatrimestreEstudiante').text(`${estudiante.cuatrimestre}°`);
                    $('#telefonoEstudiante').text(estudiante.telefono || 'No especificado');
                    $('#horasCompletadas').text(estudiante.horas_completadas);
                    $('#horasRequeridas').text(estudiante.horas_requeridas);
                    
                    const progresoBar = $('#progresoHoras');
                    progresoBar.css('width', `${Math.min(porcentaje, 100)}%`);
                    progresoBar.text(`${porcentaje}%`);
                    progresoBar.removeClass('bg-success bg-info').addClass(porcentaje >= 100 ? 'bg-success' : 'bg-info');
                    
                    $('#estudianteId, #nuevoEstudianteId').val(estudianteId);
                    $('#responsableId, #nuevoResponsableId').val(1); // Esto debe venir de la sesión en producción
                    
                    cargarRegistrosEstudiante(estudianteId);
                } else {
                    mostrarError(response.message || 'Error al cargar información del estudiante');
                }
            },
            error: function() {
                mostrarError('Error al conectar con el servidor');
            }
        });
    }

    // Cargar registros del estudiante en la tabla
    function cargarRegistrosEstudiante(estudianteId) {
        const tablaRegistros = $('#tablaRegistros').DataTable({
            destroy: true,
            ajax: {
                url: '../php/encargadophp/estudiante_detalle_operaciones.php',
                data: { accion: 'listarRegistrosEstudiante', estudiante_id: estudianteId },
                dataSrc: json => (json.success ? json.data || [] : [])
            },
            columns: [
                { data: 'registro_id' },
                { data: 'fecha' },
                { data: 'hora_entrada', defaultContent: '--' },
                { data: 'hora_salida', defaultContent: '--' },
                { data: 'horas_acumuladas', render: data => data ? data : '--' },
                { data: 'estado', render: function(data) {
                    let badgeClass = 'bg-warning text-dark';
                    if (data === 'aprobado') badgeClass = 'bg-success';
                    if (data === 'rechazado') badgeClass = 'bg-danger';
                    return `<span class="badge ${badgeClass}">${data.charAt(0).toUpperCase() + data.slice(1)}</span>`;
                }},
                { data: null, orderable: false, render: function(data) {
                    return `<div class="btn-group"><button class="btn btn-sm ${data.estado === 'pendiente' ? 'btn-primary' : 'btn-warning'} btnValidar" data-id="${data.registro_id}" title="${data.estado === 'pendiente' ? 'Validar' : 'Editar'}"><i class="bi ${data.estado === 'pendiente' ? 'bi-check-circle' : 'bi-pencil'}"></i></button></div>`;
                }}
            ],
            language: { url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-MX.json' }
        });

        // Abrir modal para validar/editar
        $('#tablaRegistros tbody').off('click', '.btnValidar').on('click', '.btnValidar', function() {
            const rowData = tablaRegistros.row($(this).parents('tr')).data();
            $('#registroId').val(rowData.registro_id);
            $('#estadoValidacion').val(rowData.estado);
            $('#observaciones').val(rowData.observaciones || '');
            $('#modalValidacion').modal('show');
        });
    }

    // Guardar validación
    $('#btnGuardarValidacion').click(function() {
        const estudianteId = new URLSearchParams(window.location.search).get('id');
        $.ajax({
            url: '../php/encargadophp/estudiante_detalle_operaciones.php',
            method: 'POST',
            data: {
                accion: 'validarRegistroEstudiante',
                registro_id: $('#registroId').val(),
                estudiante_id: estudianteId,
                responsable_id: $('#responsableId').val(),
                estado: $('#estadoValidacion').val(),
                observaciones: $('#observaciones').val()
            },
            success: function(response) {
                if(response.success) {
                    Swal.fire({ icon: 'success', title: '¡Éxito!', text: response.message, timer: 2000, showConfirmButton: false })
                    .then(() => {
                        $('#modalValidacion').modal('hide');
                        cargarInformacionEstudiante(estudianteId);
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: response.message });
                }
            },
            error: () => Swal.fire({ icon: 'error', title: 'Error', text: 'Error al conectar con el servidor' })
        });
    });

    // Guardar nuevo registro
    $('#btnGuardarRegistro').click(function() {
        const estudianteId = $('#nuevoEstudianteId').val();
        const fecha = $('#fechaRegistro').val();
        const horaEntrada = $('#horaEntrada').val();
        const horaSalida = $('#horaSalida').val();
        const diffMs = new Date(`${fecha}T${horaSalida}`) - new Date(`${fecha}T${horaEntrada}`);
        
        if (!fecha || !horaEntrada || !horaSalida || !$('#observacionesRegistro').val()) {
            Swal.fire({ icon: 'warning', title: 'Advertencia', text: 'Por favor complete todos los campos' });
            return;
        }
        if (diffMs <= 0) {
            Swal.fire({ icon: 'warning', title: 'Advertencia', text: 'La hora de salida debe ser posterior a la de entrada' });
            return;
        }
        
        $.ajax({
            url: '../php/encargadophp/estudiante_detalle_operaciones.php',
            method: 'POST',
            data: {
                accion: 'crearRegistroEstudiante',
                estudiante_id: estudianteId,
                responsable_id: $('#nuevoResponsableId').val(),
                fecha: fecha,
                hora_entrada: horaEntrada,
                hora_salida: horaSalida,
                horas_acumuladas: (diffMs / 3600000).toFixed(2),
                observaciones: $('#observacionesRegistro').val()
            },
            success: function(response) {
                if(response.success) {
                    Swal.fire({ icon: 'success', title: '¡Éxito!', text: response.message, timer: 2000, showConfirmButton: false })
                    .then(() => {
                        $('#modalNuevoRegistro').modal('hide');
                        cargarInformacionEstudiante(estudianteId);
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: response.message });
                }
            },
            error: () => Swal.fire({ icon: 'error', title: 'Error', text: 'Error al conectar con el servidor' })
        });
    });

    // Función para mostrar errores y redirigir
    function mostrarError(mensaje) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: mensaje
        }).then(() => {
            window.location.href = 'estudiantes.html';
        });
    }
});
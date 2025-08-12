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

    // Función para cerrar sesión
    window.cerrarSesion = function() {
        fetch('../php/cerrar_sesion.php')
            .then(() => {
                if (typeof tablaRegistros !== 'undefined') {
                    tablaRegistros.destroy();
                }
                window.location.href = '../index.html?logout=1';
            })
            .catch(() => {
                window.location.href = '../index.html';
            });
    };

    // Función para verificar sesión y obtener datos del usuario
    function verificarSesion() {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: '../php/verificar_sesion.php',
                type: 'GET',
                dataType: 'json',
                headers: {
                    'Cache-Control': 'no-cache'
                },
                success: function(response) {
                    if (response.activa && response.rol === 'encargado') {
                        // **CAMBIO REALIZADO AQUÍ**
                        // Se actualiza el nombre del usuario en la barra lateral.
                        // Prioriza 'nombre_completo', si no existe, usa 'correo'.
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

    // Inicialización después de verificar sesión
    verificarSesion().then(() => {
        // Inicializar DataTable
        const tablaRegistros = $('#tablaRegistros').DataTable({
            ajax: {
                url: '../php/encargadophp/encargado_operaciones.php?accion=listarRegistros',
                dataSrc: '',
                error: function(xhr, error, thrown) {
                    console.error('Error en la solicitud AJAX:', error, thrown);
                    mostrarError('Error al conectar con el servidor. Por favor intente nuevamente.');
                }
            },
            columns: [
                { data: 'registro_id' },
                { 
                    data: 'estudiante', 
                    render: function(data) {
                        return data.nombre + ' ' + (data.apellido || '');
                    }
                },
                { data: 'estudiante.matricula' },
                { data: 'fecha' },
                { 
                    data: 'hora_entrada', 
                    render: function(data) {
                        return data ? data.split(' ')[1] : '--';
                    }
                },
                { 
                    data: 'hora_salida', 
                    render: function(data) {
                        return data ? data.split(' ')[1] : '--';
                    }
                },
                { data: 'horas_acumuladas' },
                { 
                    data: 'estado', 
                    render: function(data) {
                        let badgeClass = 'badge ';
                        switch(data) {
                            case 'aprobado': badgeClass += 'bg-success'; break;
                            case 'rechazado': badgeClass += 'bg-danger'; break;
                            default: badgeClass += 'bg-warning text-dark';
                        }
                        return `<span class="${badgeClass}">${data.charAt(0).toUpperCase() + data.slice(1)}</span>`;
                    }
                },
                { 
                    data: null,
                    render: function(data) {
                        let buttons = `
                            <button class="btn btn-sm btn-info btnVerDetalle" data-id="${data.registro_id}" title="Ver detalles">
                                <i class="bi bi-eye"></i>
                            </button>
                        `;
                        
                        buttons += `
                            <button class="btn btn-sm ${data.estado === 'pendiente' ? 'btn-primary' : 'btn-warning'} 
                                    ${data.estado === 'pendiente' ? 'btnValidar' : 'btnEditar'}" 
                                    data-id="${data.registro_id}" 
                                    title="${data.estado === 'pendiente' ? 'Validar' : 'Editar'}">
                                <i class="bi ${data.estado === 'pendiente' ? 'bi-check-circle' : 'bi-pencil'}"></i>
                            </button>
                        `;
                        
                        return `<div class="btn-group">${buttons}</div>`;
                    },
                    orderable: false
                }
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-MX.json'
            }
        });

        // Filtrar registros
        $('#btnFiltrar').click(function() {
            const estado = $('#filtroEstado').val();
            const fecha = $('#filtroFecha').val();
            const estudiante = $('#filtroEstudiante').val().toLowerCase();
            
            if (estado) tablaRegistros.column(7).search(estado).draw();
            else tablaRegistros.column(7).search('').draw(); // Limpia filtro si está vacío
            
            if (fecha) tablaRegistros.column(3).search(fecha).draw();
            else tablaRegistros.column(3).search('').draw(); // Limpia filtro si está vacío

            if (estudiante) tablaRegistros.column(1).search(estudiante).draw();
            else tablaRegistros.column(1).search('').draw(); // Limpia filtro si está vacío
        });

        // Limpiar filtros
        $('#btnLimpiar').click(function() {
            $('#filtroForm')[0].reset();
            tablaRegistros.search('').columns().search('').draw();
        });

        // Abrir modal para validar/editar
        $('#tablaRegistros tbody').on('click', '.btnValidar, .btnEditar', function() {
            const registroId = $(this).data('id');
            const button = this; // Guardar referencia al botón
            
            $.ajax({
                url: '../php/encargadophp/encargado_operaciones.php',
                method: 'GET',
                data: {
                    accion: 'obtenerDetalleRegistro',
                    registro_id: registroId
                },
                success: function(response) {
                    if(response.success) {
                        const registro = response.data;
                        $('#registroId').val(registro.registro_id);
                        $('#estadoValidacion').val(registro.estado);
                        $('#observaciones').val(registro.observaciones || '');
                        
                        const esValidacion = $(button).hasClass('btnValidar');
                        $('#modalValidacionLabel').text(esValidacion ? 'Validar Registro de Horas' : 'Editar Validación de Horas');
                        $('#modalValidacion').modal('show');
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al conectar con el servidor'
                    });
                }
            });
        });

        // Guardar validación/edición
        $('#btnGuardarValidacion').click(function() {
            const registroId = $('#registroId').val();
            const estado = $('#estadoValidacion').val();
            const observaciones = $('#observaciones').val();
            
            if (!registroId || !estado) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Advertencia',
                    text: 'Por favor complete todos los campos requeridos'
                });
                return;
            }
            
            $.ajax({
                url: '../php/encargadophp/encargado_operaciones.php',
                method: 'POST',
                data: {
                    accion: 'validarRegistro',
                    registro_id: registroId,
                    estado: estado,
                    observaciones: observaciones
                },
                success: function(response) {
                    if(response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        $('#modalValidacion').modal('hide');
                        tablaRegistros.ajax.reload();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al conectar con el servidor'
                    });
                }
            });
        });

        // Ver detalle del registro
        $('#tablaRegistros tbody').on('click', '.btnVerDetalle', function() {
            const registroId = $(this).data('id');
            
            $.ajax({
                url: '../php/encargadophp/encargado_operaciones.php',
                method: 'GET',
                data: {
                    accion: 'obtenerDetalleRegistro',
                    registro_id: registroId
                },
                success: function(response) {
                    if(response.success) {
                        const registro = response.data;
                        
                        let contenido = `
                            <div class="mb-3">
                                <h5>Detalle del Registro #${registro.registro_id}</h5>
                                <hr>
                                <p><strong>Estudiante:</strong> ${registro.estudiante.nombre} ${registro.estudiante.apellido || ''}</p>
                                <p><strong>Matrícula:</strong> ${registro.estudiante.matricula}</p>
                                <p><strong>Responsable:</strong> ${registro.responsable.nombre} ${registro.responsable.apellido || ''}</p>
                                <hr>
                                <p><strong>Fecha:</strong> ${registro.fecha}</p>
                                <p><strong>Hora Entrada:</strong> ${registro.hora_entrada ? registro.hora_entrada.split(' ')[1] : '--'}</p>
                                <p><strong>Hora Salida:</strong> ${registro.hora_salida ? registro.hora_salida.split(' ')[1] : '--'}</p>
                                <p><strong>Horas Acumuladas:</strong> ${registro.horas_acumuladas || '0.00'}</p>
                                <hr>
                                <p><strong>Estado:</strong> <span class="badge ${registro.estado === 'aprobado' ? 'bg-success' : registro.estado === 'rechazado' ? 'bg-danger' : 'bg-warning text-dark'}">${registro.estado.charAt(0).toUpperCase() + registro.estado.slice(1)}</span></p>
                        `;
                        
                        if(registro.observaciones) {
                            contenido += `<p><strong>Observaciones:</strong><br>${registro.observaciones}</p>`;
                        }
                        
                        if(registro.fecha_validacion) {
                            contenido += `<p><strong>Última validación:</strong> ${registro.fecha_validacion}</p>`;
                        }
                        
                        contenido += `</div>`;
                        
                        Swal.fire({
                            title: 'Detalles del Registro',
                            html: contenido,
                            confirmButtonText: 'Cerrar',
                            width: '600px'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al conectar con el servidor'
                    });
                }
            });
        });

        function mostrarError(mensaje) {
            $('#tablaRegistros').DataTable().clear().draw();
            $('#tablaRegistros tbody').html(
                '<tr><td colspan="9" class="text-center text-danger">' + mensaje + '</td></tr>'
            );
        }
    }).catch(error => {
        console.error('Error de sesión:', error);
        window.location.href = '../index.html?session_expired=1';
    });
});
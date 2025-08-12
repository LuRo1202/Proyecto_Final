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

    let estudianteSeleccionadoId = null;

    window.cerrarSesion = function() {
        fetch('../php/logout.php')
            .then(() => {
                if (typeof tablaEstudiantes !== 'undefined' && $.fn.DataTable.isDataTable('#tablaEstudiantes')) {
                    $('#tablaEstudiantes').DataTable().destroy();
                }
                window.location.href = '../index.html?logout=1';
            })
            .catch(() => window.location.href = '../index.html');
    };

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
                error: () => reject('Error al verificar sesión')
            });
        });
    }

    const spanishLanguage = {
        "search": "Buscar:", "lengthMenu": "Mostrar _MENU_ registros", "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
        "infoEmpty": "Mostrando 0 a 0 de 0 registros", "infoFiltered": "(filtrado de _MAX_ registros totales)", "zeroRecords": "No se encontraron registros coincidentes",
        "loadingRecords": "Cargando...", "processing": "Procesando...", "paginate": { "first": "Primero", "last": "Último", "next": "Siguiente", "previous": "Anterior" }
    };

    verificarSesion().then(() => {
        const tablaEstudiantes = $('#tablaEstudiantes').DataTable({
            ajax: {
                url: '../php/encargadophp/estudiantes_operaciones.php?accion=listarEstudiantes',
                dataSrc: function(json) {
                    if (json.success) return json.data || [];
                    mostrarError('Error al cargar datos: ' + (json.message || 'Error desconocido'));
                    return [];
                },
                error: () => mostrarError('Error al conectar con el servidor. Por favor intente nuevamente.')
            },
            columns: [
                { data: 'matricula' },
                { data: null, render: data => `${data.nombre} ${data.apellido_paterno || ''} ${data.apellido_materno || ''}`.trim() },
                { data: 'carrera' },
                { data: 'cuatrimestre', render: data => data ? `${data}°` : 'N/A' },
                { data: null, render: data => `${parseFloat(data.horas_completadas || 0).toFixed(2)} / ${data.horas_requeridas || 480}` },
                { data: null, render: function(data) {
                    const completadas = parseFloat(data.horas_completadas || 0);
                    const requeridas = parseFloat(data.horas_requeridas || 480);
                    const porcentaje = requeridas > 0 ? (completadas / requeridas * 100).toFixed(1) : 0;
                    return `<div class="progress"><div class="progress-bar ${porcentaje >= 100 ? 'bg-success' : ''}" role="progressbar" style="width: ${Math.min(porcentaje, 100)}%" title="${porcentaje}%">${porcentaje}%</div></div>`;
                }},
                { data: 'estudiante_id', render: data => `
                    <div class="btn-group">
                        <button class="btn btn-sm btn-info btnVerDetalle" data-id="${data}" title="Ver detalles"><i class="bi bi-eye"></i></button>
                        <button class="btn btn-sm btn-primary btnVerRegistros" data-id="${data}" title="Ver registros completos"><i class="bi bi-clock-history"></i></button>
                    </div>`,
                  orderable: false
                }
            ],
            language: spanishLanguage,
            initComplete: function() {
                const carreras = new Set();
                this.api().column(2).data().each(value => { if (value) carreras.add(value); });
                const selectCarrera = $('#filtroCarrera');
                selectCarrera.find('option:not(:first)').remove();
                carreras.forEach(carrera => selectCarrera.append(new Option(carrera, carrera)));
            }
        });

        function mostrarError(mensaje) {
            $('#tablaEstudiantes').DataTable().clear().draw().tbody().html(`<tr><td colspan="7" class="text-center text-danger">${mensaje}</td></tr>`);
        }

        $('#btnBuscar').click(() => tablaEstudiantes.search($('#filtroEstudiante').val()).draw());
        $('#filtroEstudiante').keyup(function() { if (event.key === 'Enter') tablaEstudiantes.search(this.value).draw(); });
        $('#filtroCarrera, #filtroCuatrimestre').change(() => tablaEstudiantes.column(2).search($('#filtroCarrera').val()).column(3).search($('#filtroCuatrimestre').val() ? `^${$('#filtroCuatrimestre').val()}°$` : '', true, false).draw());

        $('#tablaEstudiantes tbody').on('click', '.btnVerDetalle', function() {
            estudianteSeleccionadoId = $(this).data('id');
            cargarDetalleEstudiante(estudianteSeleccionadoId);
        });

        // ===== RUTA ORIGINAL RESTAURADA =====
        $('#tablaEstudiantes tbody').on('click', '.btnVerRegistros', function() {
            const estudianteId = $(this).data('id');
            window.location.href = `estudiante_detalle.html?id=${estudianteId}`;
        });

        // ===== RUTA ORIGINAL RESTAURADA =====
        $('#btnVerTodosRegistros').click(function() {
            if (estudianteSeleccionadoId) {
                window.location.href = `estudiante_detalle.html?id=${estudianteSeleccionadoId}`;
            }
        });

        function cargarDetalleEstudiante(estudianteId) {
            $.ajax({
                url: '../php/encargadophp/estudiantes_operaciones.php',
                method: 'GET',
                data: { accion: 'obtenerDetalleEstudiante', estudiante_id: estudianteId },
                success: response => response.success ? mostrarModalDetalle(response.data) : Swal.fire('Error', response.message || 'Error al obtener detalles', 'error'),
                error: () => Swal.fire('Error', 'Error al conectar con el servidor', 'error')
            });
        }

        function mostrarModalDetalle(estudiante) {
            const completadas = parseFloat(estudiante.horas_completadas || 0);
            const requeridas = parseFloat(estudiante.horas_requeridas || 480);
            const porcentaje = requeridas > 0 ? (completadas / requeridas * 100).toFixed(1) : 0;

            $('#detalleNombre').text(`${estudiante.nombre} ${estudiante.apellido_paterno || ''} ${estudiante.apellido_materno || ''}`.trim());
            $('#detalleMatricula').text(estudiante.matricula);
            $('#detalleCarrera').text(estudiante.carrera);
            $('#detalleCuatrimestre').text(`${estudiante.cuatrimestre}°`);
            $('#detalleTelefono').text(estudiante.telefono || 'No especificado');
            $('#detalleHorasRequeridas').text(requeridas.toFixed(2));
            $('#detalleHorasCompletadas').text(completadas.toFixed(2));
            $('#detalleHorasRestantes').text((requeridas - completadas).toFixed(2));
            $('#detalleProgresoBar').css('width', `${Math.min(porcentaje, 100)}%`).text(`${porcentaje}%`).removeClass('bg-success').addClass(porcentaje >= 100 ? 'bg-success' : '');
            
            cargarRegistrosEstudiante(estudiante.estudiante_id);
            $('#modalDetalleEstudiante').modal('show');
        }

        function cargarRegistrosEstudiante(estudianteId) {
            $.ajax({
                url: '../php/encargadophp/estudiantes_operaciones.php',
                method: 'GET',
                data: { accion: 'obtenerRegistrosEstudiante', estudiante_id: estudianteId, limit: 5 },
                success: response => {
                    const tbody = $('#tablaRegistrosEstudiante tbody').empty();
                    if (!response.success || !response.data || response.data.length === 0) {
                        tbody.append('<tr><td colspan="5" class="text-center">No hay registros recientes.</td></tr>');
                        return;
                    }
                    response.data.forEach(reg => {
                        let badgeClass = reg.estado === 'aprobado' ? 'bg-success' : reg.estado === 'rechazado' ? 'bg-danger' : 'bg-warning text-dark';
                        tbody.append(`<tr>
                            <td>${reg.fecha}</td>
                            <td>${reg.hora_entrada ? reg.hora_entrada.substring(0, 5) : '--'}</td>
                            <td>${reg.hora_salida ? reg.hora_salida.substring(0, 5) : '--'}</td>
                            <td>${parseFloat(reg.horas_acumuladas || 0).toFixed(2)}</td>
                            <td><span class="badge ${badgeClass}">${reg.estado}</span></td>
                        </tr>`);
                    });
                },
                error: () => $('#tablaRegistrosEstudiante tbody').html('<tr><td colspan="5" class="text-center text-danger">Error al cargar registros.</td></tr>')
            });
        }
        
        $('#btnExportarExcel').click(function() {
            const data = tablaEstudiantes.rows({ search: 'applied' }).data().toArray();
            if (data.length === 0) {
                Swal.fire('Sin datos', 'No hay datos para exportar con los filtros actuales', 'warning');
                return;
            }
            const excelData = data.map(est => ({
                'Matrícula': est.matricula, 'Nombre': `${est.nombre} ${est.apellido_paterno || ''} ${est.apellido_materno || ''}`.trim(),
                'Carrera': est.carrera, 'Cuatrimestre': `${est.cuatrimestre}°`, 'Horas Completadas': parseFloat(est.horas_completadas || 0),
                'Horas Requeridas': parseInt(est.horas_requeridas || 480), 'Progreso (%)': ((parseFloat(est.horas_completadas || 0) / (est.horas_requeridas || 480)) * 100).toFixed(1)
            }));
            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.json_to_sheet(excelData);
            XLSX.utils.book_append_sheet(wb, ws, "Estudiantes");
            XLSX.writeFile(wb, `Estudiantes_a_Cargo_${new Date().toISOString().slice(0, 10)}.xlsx`);
        });

    }).catch(error => {
        console.error('Error de sesión:', error);
        window.location.href = '../index.html?session_expired=1';
    });
});
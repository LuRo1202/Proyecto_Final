$(document).ready(function() {
    // --- FUNCIÓN DE VERIFICACIÓN DE SESIÓN (AÑADIDA) ---
    function verificarSesion() {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: '../php/verificar_sesion.php',
                type: 'GET',
                dataType: 'json',
                cache: false,
                success: function(response) {
                    if (response.activa && response.rol === 'encargado') {
                        const userName = response.nombre_completo || response.correo || 'Usuario';
                        $('#user-name').text(userName);
                        resolve();
                    } else {
                        reject('Sesión no válida');
                    }
                },
                error: () => reject('Error de conexión')
            });
        });
    }

    // --- CÓDIGO PRINCIPAL EJECUTADO TRAS VERIFICAR SESIÓN ---
    verificarSesion().then(() => {
        // ELEMENTOS DEL DOM
        const vistaTabla = $('#vista-tabla');
        const vistaDetalle = $('#vista-detalle');
        const btnVolver = $('#btn-volver');
        const btnLogout = $('#btn-logout');

        // INSTANCIAS DE GRÁFICOS
        let charts = { semanal: null, mensual: null, trimestral: null };

        // INICIALIZACIÓN DE LA TABLA
        const tabla = $('#tabla-progreso').DataTable({
            "language": { "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" },
            "ajax": {
                "url": "../php/encargadophp/encargado_generar_reporte.php?action=get_list",
                "type": "POST",
                "dataSrc": "data"
            },
            "columns": [
                { "data": "matricula" },
                { "data": "nombre_completo" },
                { 
                    "data": null, "className": "text-nowrap",
                    "render": function(data, type, row) {
                        const completadas = parseFloat(row.horas_completadas) || 0;
                        const requeridas = parseInt(row.horas_requeridas) || 480;
                        const porcentaje = requeridas > 0 ? (completadas / requeridas) * 100 : 0;
                        return `
                            <div class="d-flex align-items-center">
                                <span class="me-2">${completadas.toFixed(1)} / ${requeridas}</span>
                                <div class="progress flex-grow-1" style="height: 20px;">
                                    <div class="progress-bar" style="width: ${porcentaje.toFixed(2)}%; background-color: #4CAF50;" role="progressbar">${porcentaje.toFixed(0)}%</div>
                                </div>
                            </div>`;
                    }
                },
                { 
                    "data": "estado",
                    "render": data => `<span class="badge ${data === 'Liberado' ? 'bg-liberado' : 'bg-en-proceso'}">${data}</span>`
                },
                { 
                    "data": null, "orderable": false, "className": "text-center",
                    "render": (data, type, row) => `<button class="btn btn-info btn-sm btn-ver-reporte" data-id="${row.estudiante_id}"><i class="bi bi-graph-up me-1"></i>Reporte</button>`
                }
            ]
        });

        // MANEJO DE EVENTOS
        $('#tabla-progreso tbody').on('click', '.btn-ver-reporte', function() {
            const studentId = $(this).data('id');
            cargarReporteDetallado(studentId);
        });

        btnVolver.on('click', () => mostrarVista('tabla'));
        
        btnLogout.on('click', function(e) {
            e.preventDefault();
            fetch('../php/cerrar_sesion.php').then(() => window.location.href = '../index.html');
        });

        // FUNCIONES
        function mostrarVista(vista) {
            if (vista === 'tabla') {
                vistaDetalle.hide();
                vistaTabla.fadeIn();
            } else {
                vistaTabla.hide();
                vistaDetalle.fadeIn();
            }
        }

        function cargarReporteDetallado(studentId) {
            Swal.fire({ title: 'Cargando Reporte...', didOpen: () => { Swal.showLoading() } });
            $.ajax({
                url: `../php/encargadophp/encargado_generar_reporte.php?action=get_report&id=${studentId}`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const data = response.data;
                        actualizarCabecera(data.detalleEstudiante);
                        actualizarGraficos(data);
                        actualizarRegistrosRecientes(data.registrosRecientes);
                        mostrarVista('detalle');
                        Swal.close();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: () => Swal.fire('Error de Conexión', 'No se pudo cargar el reporte.', 'error')
            });
        }

        function actualizarCabecera(student) {
            const completadas = parseFloat(student.horas_completadas) || 0;
            const requeridas = parseInt(student.horas_requeridas) || 480;
            $('#student-info-header').html(`
                <div class="row align-items-center gy-3">
                    <div class="col-lg-4"><h5 class="fw-bold mb-1">${student.nombre_completo}</h5><p class="mb-1 text-muted"><strong>Matrícula:</strong> ${student.matricula}</p><p class="mb-0 text-muted"><strong>Carrera:</strong> ${student.carrera}</p></div>
                    <div class="col-lg-4"><p class="mb-1 text-muted"><strong>Correo:</strong> ${student.correo}</p><p class="mb-0 text-muted"><strong>Teléfono:</strong> ${student.telefono || 'N/A'}</p></div>
                    <div class="col-lg-4 text-center"><div class="d-flex justify-content-around">
                        <div><span class="badge bg-success badge-horas">${completadas.toFixed(2)}</span><div class="small mt-1">Completadas</div></div>
                        <div><span class="badge bg-primary badge-horas">${requeridas}</span><div class="small mt-1">Requeridas</div></div>
                        <div><span class="badge bg-warning text-dark badge-horas">${(requeridas - completadas).toFixed(2)}</span><div class="small mt-1">Restantes</div></div>
                    </div></div>
                </div>`);
        }

        function actualizarGraficos(data) {
            Object.values(charts).forEach(chart => chart?.destroy());
            charts.semanal = crearGrafico('horasSemanalesChart', 'bar', data.horasSemanales);
            charts.mensual = crearGrafico('horasMensualesChart', 'line', data.horasMensuales);
            charts.trimestral = crearGrafico('horasTrimestralesChart', 'doughnut', data.horasTrimestrales);
        }
        
        function crearGrafico(canvasId, type, data) {
            const canvas = document.getElementById(canvasId);
            const container = canvas.parentElement;
            container.innerHTML = `<canvas id="${canvasId}"></canvas>`; // Reset canvas
            if (!data || data.length === 0) {
                container.innerHTML = '<div class="d-flex align-items-center justify-content-center h-100 text-muted">No hay datos para esta gráfica.</div>';
                return null;
            }

            return new Chart(document.getElementById(canvasId).getContext('2d'), {
                type: type,
                data: {
                    labels: data.map(d => d.label),
                    datasets: [{
                        label: 'Horas',
                        data: data.map(d => d.horas),
                        backgroundColor: ['#8856dd', '#3F51B5', '#2196F3', '#00BCD4', '#4CAF50', '#FF9800'],
                        borderColor: '#8856dd',
                        tension: 0.1
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: type === 'doughnut' } } }
            });
        }

        function actualizarRegistrosRecientes(registros) {
            const tbody = $('#recent-registros-table').empty();
            if (!registros || registros.length === 0) {
                tbody.append('<tr><td colspan="3" class="text-center p-3 text-muted">No hay registros recientes.</td></tr>');
                return;
            }
            registros.forEach(r => {
                const badgeClass = r.estado === 'aprobado' ? 'bg-success' : 'bg-warning text-dark';
                tbody.append(`<tr><td>${r.fecha}</td><td>${r.horas_acumuladas}</td><td><span class="badge ${badgeClass}">${r.estado}</span></td></tr>`);
            });
        }
    }).catch(error => {
        console.error('Error de sesión:', error);
        document.body.innerHTML = '<div class="alert alert-danger m-5">Error de sesión. Serás redirigido al inicio de sesión.</div>';
        setTimeout(() => { window.location.href = '../index.html'; }, 3000);
    });
});
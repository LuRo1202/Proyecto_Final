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
    // 2. LÓGICA ESPEĆIFICA DE LA PÁGINA (REPORTES)
    // =================================================================================
    
    let tablaEstudiantes;
    let horasSemanalesChart, horasMensualesChart;
    const API_URL = '../php/adminphp/reportes.php';
    const studentListContainer = $('#student-list-container');
    const studentReportContainer = $('#student-report-container');

    // --- Funciones de Alerta (Unificadas con SweetAlert2) ---
    const showSuccess = (title, text) => Swal.fire({ icon: 'success', title: title, text: text, showConfirmButton: false, timer: 1500 });
    const showError = message => Swal.fire({ icon: 'error', title: 'Error', text: message });
    const showUpdatingAlert = title => Swal.fire({ title: title, imageUrl: '../imagenes/loading-spinner.gif', imageWidth: 80, showConfirmButton: false, allowOutsideClick: false });

    // --- Funciones Auxiliares ---
    const LENGUAJE_ESPANOL_DATATABLES = { "sProcessing": "Procesando...", "sLengthMenu": "Mostrar _MENU_ registros", "sZeroRecords": "No se encontraron resultados", "sEmptyTable": "Ningún dato disponible", "sInfo": "Mostrando _START_ al _END_ de _TOTAL_", "sInfoEmpty": "Mostrando 0 de 0", "sInfoFiltered": "(filtrado de _MAX_)", "sSearch": "Buscar:", "oPaginate": { "sFirst": "Primero", "sLast": "Último", "sNext": "Siguiente", "sPrevious": "Anterior" } };

    // --- Inicialización de DataTables ---
    function inicializarTabla() {
        tablaEstudiantes = $('#tablaEstudiantesReportes').DataTable({
            ajax: {
                url: `${API_URL}?accion=listar_estudiantes`, dataSrc: 'data',
                error: (xhr, error, thrown) => showError(`No se pudieron cargar los estudiantes: ${thrown}`)
            },
            columns: [
                { data: 'matricula' }, { data: 'nombre_completo' }, { data: 'carrera' },
                { data: null, render: data => {
                    const horas = parseFloat(data.horas_completadas || 0), req = parseFloat(data.horas_requeridas || 480);
                    const pct = req > 0 ? ((horas / req) * 100).toFixed(1) : 0;
                    return `<div class="progress" title="${horas.toFixed(2)} de ${req} horas"><div class="progress-bar" style="width: ${pct}%;">${pct}%</div></div>`;
                }},
                { data: 'estudiante_id', orderable: false, render: data => `<button class="btn btn-primary btn-sm btn-ver-reporte" data-id="${data}"><i class="bi bi-search me-1"></i>Ver Reporte</button>` }
            ],
            language: LENGUAJE_ESPANOL_DATATABLES, responsive: true, autoWidth: false
        });
    }

    // --- Carga de Datos y Visualización ---
    function loadStudentReport(estudianteId) {
        showUpdatingAlert('Cargando reporte...');
        $.getJSON(`${API_URL}?accion=obtener_reporte_completo&id=${estudianteId}`, function(result) {
            Swal.close();
            if (!result.success) return showError(result.message || 'Error en los datos recibidos');
            
            displayStudentInfo(result.data.detalleEstudiante);
            createOrUpdateChart('horasSemanalesChart', 'bar', result.data.horasSemanales, 'semana', 'Horas por Semana');
            createOrUpdateChart('horasMensualesChart', 'line', result.data.horasMensuales, 'mes', 'Horas por Mes');
            fillRecentRegistros(result.data.registrosRecientes);

            studentListContainer.hide();
            studentReportContainer.show();
        }).fail(() => showError('No se pudo cargar el reporte del estudiante.'));
    }

    function displayStudentInfo(estudiante) {
        $('#student-name').text(estudiante.nombre_completo);
        $('#student-matricula').text(estudiante.matricula);
        $('#student-carrera').text(estudiante.carrera || 'N/A');
        $('#student-responsable').text(estudiante.responsable_nombre || 'No asignado');

        const horas = parseFloat(estudiante.horas_completadas || 0);
        const req = parseFloat(estudiante.horas_requeridas || 480);
        const pct = req > 0 ? Math.min((horas / req) * 100, 100) : 0;
        
        const progressBar = $('#student-progress-bar');
        progressBar.css('width', `${pct.toFixed(1)}%`).attr('aria-valuenow', pct.toFixed(1)).text(`${pct.toFixed(1)}%`);
        $('#student-progress-text').text(`${horas.toFixed(2)} de ${req} horas`);
    }

    function fillRecentRegistros(registros) {
        const tbody = $('#recent-registros-table').empty();
        if (registros.length === 0) {
            tbody.html('<tr><td colspan="4" class="text-center">No hay registros recientes.</td></tr>');
            return;
        }
        registros.forEach(r => {
            tbody.append(`<tr><td>${new Date(r.fecha + 'T00:00:00').toLocaleDateString()}</td><td>${parseFloat(r.horas_acumuladas).toFixed(2)}</td><td><span class="badge bg-success">${r.estado}</span></td><td>${r.responsable_nombre || 'N/A'}</td></tr>`);
        });
    }

    // --- Gráficos ---
    function createOrUpdateChart(canvasId, type, data, labelKey, title) {
        const ctx = document.getElementById(canvasId).getContext('2d');
        // Destruir instancia de gráfico anterior si existe
        if (window[canvasId + '_chart']) window[canvasId + '_chart'].destroy();
        
        const labels = (labelKey === 'semana') ? data.map(item => `Sem ${String(item.semana).slice(-2)}`) : data.map(item => new Date(item.mes + '-02').toLocaleString('es-MX', { month: 'short', year: '2-digit' }));
        const chartData = data.map(item => parseFloat(item.total_horas));

        window[canvasId + '_chart'] = new Chart(ctx, {
            type: type,
            data: {
                labels: labels,
                datasets: [{
                    label: 'Horas Aprobadas', data: chartData,
                    backgroundColor: type === 'bar' ? 'rgba(136, 86, 221, 0.7)' : 'rgba(136, 86, 221, 0.2)',
                    borderColor: 'rgba(136, 86, 221, 1)',
                    borderWidth: 2, tension: 0.3, fill: type === 'line'
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { title: { display: true, text: title, font: { size: 16 } }, legend: { display: false } },
                scales: { y: { beginAtZero: true, grid: { color: '#eee' } }, x: { grid: { display: false } } }
            }
        });
    }

    // --- Manejo de Eventos ---
    function configurarEventListeners() {
        $('#tablaEstudiantesReportes tbody').on('click', '.btn-ver-reporte', function() {
            loadStudentReport($(this).data('id'));
        });

        $('#btn-back-to-list').on('click', function() {
            studentReportContainer.hide();
            studentListContainer.show();
            tablaEstudiantes.columns.adjust().responsive.recalc();
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
document.addEventListener('DOMContentLoaded', function() {
    // Variables globales
    let carreraChart = null; 

    // Constantes para las URLs de las APIs
    const API_URL = '../php/adminphp/dashboard.php';
    const LOGOUT_URL = '../php/cerrar_sesion.php';
    const SESSION_API = '../php/verificar_sesion.php'; 

    // Elementos del DOM
    const loadingOverlay = document.querySelector('.loading-overlay');
    const btnRefresh = document.getElementById('btn-refresh');
    const btnLogout = document.getElementById('btn-logout');
    const userEmailElement = document.getElementById('user-email'); // Este elemento mostrará el nombre o correo
    const userRoleElement = document.getElementById('user-role'); 

    // --- Inicialización ---
    checkSessionAndLoadData();

    // --- Event Listeners ---
    btnRefresh.addEventListener('click', loadDashboardData); 
    btnLogout.addEventListener('click', confirmLogout); 

    // --- Funciones Principales ---

    async function checkSessionAndLoadData() {
        showLoading(true); 
        try {
            const sessionResponse = await fetch(SESSION_API);
            if (!sessionResponse.ok) {
                throw new Error(`Error al verificar sesión: HTTP status ${sessionResponse.status}`);
            }
            const sessionData = await sessionResponse.json();

            console.log('Datos de sesión recibidos:', sessionData); // Para depuración

            if (sessionData.activa && sessionData.rol === 'admin') {
                updateUserInfo(sessionData); // Actualiza la información del usuario en la sidebar
                await loadDashboardData(false); 
            } else {
                showError('Sesión no válida o rol no autorizado. Redirigiendo al login.');
                setTimeout(redirectToLogin, 2000); 
            }
        } catch (error) {
            console.error('Error al verificar la sesión:', error);
            showError('Error al verificar la sesión. Por favor, inténtalo de nuevo.');
            setTimeout(redirectToLogin, 2000);
        } finally {
            showLoading(false); 
        }
    }

    async function loadDashboardData(showUpdateAlert = true) { 
        if (showUpdateAlert) {
            showUpdatingAlert(); 
        }

        try {
            const response = await fetch(API_URL);

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || `Error HTTP: ${response.status}`);
            }

            const result = await response.json();

            if (!result.success) {
                throw new Error(result.message || 'Error en los datos recibidos');
            }

            updateSummaryCards(result.data);
            updateStudentsTable(result.data.estudiantesHorasPendientes);
            createCarreraChart(result.data.estudiantesPorCarrera);
            
            if (showUpdateAlert) {
                Swal.close(); 
                Swal.fire({
                    icon: 'success',
                    title: '¡Actualizado!',
                    text: 'Los datos se han cargado correctamente.',
                    showConfirmButton: false,
                    timer: 1500 
                });
            }

        } catch (error) {
            console.error('Error al cargar datos del dashboard:', error);
            if (showUpdateAlert) {
                Swal.close(); 
            }
            showError(error.message || 'Error al cargar los datos del dashboard. Verifique la conexión o el servidor.');
        }
    }

    function updateSummaryCards(data) {
        document.getElementById('total-estudiantes').textContent = data.totalEstudiantes || 0;
        document.getElementById('total-responsables').textContent = data.totalResponsables || 0;
        document.getElementById('horas-pendientes').textContent = (data.horasPendientes || 0).toFixed(2);
        document.getElementById('total-asignaciones').textContent = data.totalAsignaciones || 0;
    }

    function updateStudentsTable(students) {
        const tbody = document.getElementById('estudiantes-table-body');
        tbody.innerHTML = ''; 

        if (!students || students.length === 0) {
            tbody.innerHTML = '<tr><td colspan="3" class="text-center py-4 text-muted">No hay estudiantes con horas pendientes o datos disponibles.</td></tr>';
            return;
        }

        students.forEach(student => {
            const completed = parseFloat(student.horas_completadas) || 0;
            const horasRequeridas = parseInt(student.horas_requeridas) || 480; 
            const remaining = Math.max(0, horasRequeridas - completed).toFixed(2); 
            const percentage = ((completed / horasRequeridas) * 100).toFixed(1); 

            const fullName = [student.nombre, student.apellido_paterno, student.apellido_materno].filter(Boolean).join(' ');

            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${fullName || 'N/A'}</td>
                <td>${completed.toFixed(2)} / ${horasRequeridas} (${remaining} restantes)</td>
                <td>
                    <div class="progress progress-thin">
                        <div class="progress-bar" 
                             role="progressbar" 
                             style="width: ${percentage}%" 
                             aria-valuenow="${percentage}" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                             ${percentage}%
                        </div>
                    </div>
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    function createCarreraChart(carrerasData) {
        const ctx = document.getElementById('carreraChart');

        if (carreraChart) {
            carreraChart.destroy();
        }

        if (!carrerasData || carrerasData.length === 0) {
            const chartContainer = ctx.closest('.card-body');
            chartContainer.innerHTML = `
                <div class="d-flex flex-column align-items-center justify-content-center" style="min-height: 250px; color: #6c757d;">
                    <i class="bi bi-pie-chart-fill" style="font-size: 3rem; margin-bottom: 15px;"></i>
                    <p class="h6">No hay datos de carreras disponibles para el gráfico.</p>
                </div>
            `;
            if (!document.getElementById('carreraChart')) {
                const newCanvas = document.createElement('canvas');
                newCanvas.id = 'carreraChart';
                newCanvas.height = 250; 
                chartContainer.appendChild(newCanvas);
            }
            return;
        }

        const labels = carrerasData.map(item =>
            item.carrera.length > 25 ? item.carrera.substring(0, 22) + '...' : item.carrera
        );
        const data = carrerasData.map(item => item.cantidad);

        const baseColors = [
            '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b',
            '#858796', '#5a5c69', '#2e59d9', '#17a673', '#d94d2e'
        ];
        const backgroundColors = labels.map((_, i) => baseColors[i % baseColors.length]);


        carreraChart = new Chart(ctx.getContext('2d'), {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Estudiantes',
                    data: data,
                    backgroundColor: backgroundColors,
                    borderColor: '#fff', 
                    borderWidth: 1,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.parsed;
                                const label = context.label || '';
                                return `${label}: ${value} estudiantes`;
                            },
                            afterLabel: function(context) {
                                const originalName = carrerasData[context.dataIndex].carrera;
                                return originalName.length > 25 ? `(${originalName})` : '';
                            }
                        },
                        backgroundColor: 'rgba(0,0,0,0.7)', 
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: 'rgba(255,255,255,0.2)',
                        borderWidth: 1
                    },
                    legend: {
                        position: 'right', 
                        labels: {
                            usePointStyle: true, 
                            font: {
                                size: 12
                            },
                            color: '#343a40' 
                        }
                    },
                    title: {
                        display: false 
                    }
                }
            }
        });
    }

    /**
     * Actualiza la información del usuario en la barra lateral con nombre completo o correo.
     * @param {object} userInfo - Objeto con nombre_completo, correo, y rol del usuario.
     */
    function updateUserInfo(userInfo) {
        // Prioriza el nombre_completo si está disponible, de lo contrario usa el correo
        userEmailElement.textContent = userInfo.nombre_completo || userInfo.correo || 'Usuario no identificado';
        // El rol ya está hardcodeado en el HTML, pero puedes hacerlo dinámico si sessionData.rol es confiable
        // userRoleElement.textContent = userInfo.rol.charAt(0).toUpperCase() + userInfo.rol.slice(1);
    }

    // --- UI Helpers ---

    function showLoading(show) {
        loadingOverlay.style.display = show ? 'flex' : 'none';
    }

    function showUpdatingAlert(title = 'Cargando datos...', text = 'Por favor, espera un momento.') {
        Swal.fire({
            title: title,
            text: text,
            imageUrl: '../imagenes/loading-spinner.gif', 
            imageWidth: 80,
            imageHeight: 80,
            imageAlt: 'Cargando...',
            showConfirmButton: false, 
            allowOutsideClick: false, 
            allowEscapeKey: false 
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
        }).then(async (result) => {
            if (result.isConfirmed) {
                showLoading(true); 
                try {
                    const response = await fetch(LOGOUT_URL, { method: 'POST' });
                    if (!response.ok) throw new Error('Error al cerrar sesión en el servidor.');
                    
                    const logoutResult = await response.json();
                    if (!logoutResult.success) throw new Error(logoutResult.message || 'Error desconocido al cerrar sesión.');

                    redirectToLogin();
                } catch (error) {
                    showError(error.message || 'No se pudo cerrar la sesión correctamente.');
                } finally {
                    showLoading(false); 
                }
            }
        });
    }
});
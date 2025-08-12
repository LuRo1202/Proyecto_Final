document.addEventListener('DOMContentLoaded', function() {
    // AÑADIDO: Variables globales para paginación
    let todosLosRegistros = [];
    let paginaActual = 1;
    const registrosPorPagina = 10;

    // Load initial data
    cargarDatosEstudiante();
    
    // Set up event listeners
    document.getElementById('btnRegistrarEntrada').addEventListener('click', registrarEntrada);
    document.getElementById('btnRegistrarSalida').addEventListener('click', registrarSalida);
    document.getElementById('btnActualizarRegistros').addEventListener('click', function() {
        showLoadingSpinner('recordsTableSpinner', true); // Show spinner on manual update
        showAlert('Actualizando registros, por favor espera...', 'info'); // Indicate update is in progress
        cargarDatosEstudiante();
    });

    // AÑADIDO: Event listeners para los botones de paginación
    document.getElementById('btnAnterior').addEventListener('click', () => {
        if (paginaActual > 1) {
            paginaActual--;
            mostrarPagina();
        }
    });

    document.getElementById('btnSiguiente').addEventListener('click', () => {
        const totalPaginas = Math.ceil(todosLosRegistros.length / registrosPorPagina);
        if (paginaActual < totalPaginas) {
            paginaActual++;
            mostrarPagina();
        }
    });


    /**
     * Manages the visibility of loading spinners and their associated content.
     * @param {string} elementId - The ID of the spinner container element.
     * @param {boolean} show - True to show the spinner, false to hide it.
     */
    function showLoadingSpinner(elementId, show) {
        const spinnerContainer = document.getElementById(elementId);
        if (spinnerContainer) {
            spinnerContainer.style.display = show ? 'flex' : 'none';
            // Toggle visibility of actual content
            const parent = spinnerContainer.closest('.card-body');
            if (parent) {
                // Select all direct children of the parent except the spinner container itself
                const contentElements = parent.querySelectorAll(':scope > *:not(.spinner-container)');
                contentElements.forEach(el => {
                    if (el !== spinnerContainer) {
                        el.style.display = show ? 'none' : ''; // Hide if showing spinner, show if hiding spinner
                    }
                });
            }
        }
    }

    /**
     * Fetches and displays student data, progress, and historical records.
     */
    function cargarDatosEstudiante() {
        // Show spinners when data loading begins
        showLoadingSpinner('studentInfoSpinner', true);
        showLoadingSpinner('recordsTableSpinner', true);
        
        // Hide 'no records' message and pagination while loading
        document.getElementById('noRecordsMessage').style.display = 'none';
        document.getElementById('paginationContainer').style.display = 'none';

        fetch('../php/estudiantephp/estudiante.php?accion=obtenerDatos')
            .then(response => {
                if (!response.ok) {
                    return response.json().then(errorData => { throw new Error(errorData.error || response.statusText); });
                }
                return response.json();
            })
            .then(data => {
                showLoadingSpinner('studentInfoSpinner', false);
                showLoadingSpinner('recordsTableSpinner', false);

                if (data.error) {
                    showAlert(data.error, 'danger');
                    document.getElementById('infoEstudiante').innerHTML = `<p class="text-danger"><i class="bi bi-exclamation-circle me-1"></i> ${data.error}</p>`;
                    document.querySelector('#tablaRegistros tbody').innerHTML = `<tr><td colspan="5" class="text-danger text-center py-3"><i class="bi bi-exclamation-circle me-1"></i> ${data.error}</td></tr>`;
                    document.getElementById('noRecordsMessage').style.display = 'none';
                    return;
                }
                
                // Display student information in the navbar
                const nombreUsuarioTextElement = document.getElementById('nombreUsuarioText');
                if (nombreUsuarioTextElement) {
                    let nombreCompletoEstudiante = data.estudiante.nombre;
                    if (data.estudiante.apellido_paterno) nombreCompletoEstudiante += ' ' + data.estudiante.apellido_paterno;
                    if (data.estudiante.apellido_materno) nombreCompletoEstudiante += ' ' + data.estudiante.apellido_materno;
                    nombreUsuarioTextElement.textContent = nombreCompletoEstudiante;
                }

                // Populate student info card
                let infoEstudianteHtml = `
                    <p><strong>Matrícula:</strong> ${data.estudiante.matricula}</p>
                    <p><strong>Nombre:</strong> ${data.estudiante.nombre} ${data.estudiante.apellido_paterno || ''} ${data.estudiante.apellido_materno || ''}</p>
                    <p><strong>Carrera:</strong> ${data.estudiante.carrera}</p>
                    <p><strong>Cuatrimestre:</strong> ${data.estudiante.cuatrimestre}</p>
                    <p><strong>Correo:</strong> ${data.estudiante.correo}</p>
                `;
                
                // Display responsible information if available
                if (data.responsable) {
                    let nombreCompletoResponsable = data.responsable.nombre;
                    if (data.responsable.apellido_paterno) nombreCompletoResponsable += ' ' + data.responsable.apellido_paterno;
                    if (data.responsable.apellido_materno) nombreCompletoResponsable += ' ' + data.responsable.apellido_materno;
                    infoEstudianteHtml += `<p><strong>Encargado:</strong> ${nombreCompletoResponsable}</p>`;
                    document.getElementById('btnRegistrarEntrada').disabled = false;
                    document.getElementById('btnRegistrarSalida').disabled = false;
                } else {
                    infoEstudianteHtml += `<p class="text-danger"><strong>Encargado:</strong> No asignado</p>`;
                    document.getElementById('btnRegistrarEntrada').disabled = true;
                    document.getElementById('btnRegistrarSalida').disabled = true;
                    showAlert('No tienes un encargado asignado. No puedes registrar horas hasta que se te asigne uno.', 'warning');
                }
                
                document.getElementById('infoEstudiante').innerHTML = infoEstudianteHtml;
                
                actualizarProgresoHoras(data.estudiante);
                
                // MODIFICADO: Manejo de datos para paginación
                todosLosRegistros = data.registros || [];
                paginaActual = 1; // Reset to first page on data load
                mostrarPagina(); // Display the first page
            })
            .catch(error => {
                console.error('Error al cargar los datos del estudiante:', error);
                showAlert(`Error al cargar los datos: ${error.message}. Intenta recargar la página.`, 'danger');
                showLoadingSpinner('studentInfoSpinner', false);
                showLoadingSpinner('recordsTableSpinner', false);
                document.getElementById('infoEstudiante').innerHTML = '<p class="text-danger"><i class="bi bi-exclamation-circle me-1"></i> No se pudo cargar la información del estudiante.</p>';
                document.querySelector('#tablaRegistros tbody').innerHTML = `<tr><td colspan="5" class="text-danger text-center py-3"><i class="bi bi-exclamation-circle me-1"></i> Error al cargar los registros.</td></tr>`;
                document.getElementById('noRecordsMessage').style.display = 'none';
            });
    }

    /**
     * AÑADIDO: Muestra la página actual de registros y actualiza los controles.
     */
    function mostrarPagina() {
        const tbody = document.querySelector('#tablaRegistros tbody');
        const noRecordsMessage = document.getElementById('noRecordsMessage');
        const paginationContainer = document.getElementById('paginationContainer');
        tbody.innerHTML = '';
        noRecordsMessage.style.display = 'none';
        paginationContainer.style.display = 'none';

        if (!todosLosRegistros || todosLosRegistros.length === 0) {
            noRecordsMessage.style.display = 'block';
            return;
        }

        paginationContainer.style.display = 'flex';

        const inicio = (paginaActual - 1) * registrosPorPagina;
        const fin = inicio + registrosPorPagina;
        const registrosDePagina = todosLosRegistros.slice(inicio, fin);
        
        llenarTablaRegistros(registrosDePagina);
        actualizarControlesPaginacion();
    }

    /**
     * AÑADIDO: Actualiza el estado y texto de los botones de paginación.
     */
    function actualizarControlesPaginacion() {
        const totalPaginas = Math.ceil(todosLosRegistros.length / registrosPorPagina);
        const infoPagina = document.getElementById('infoPagina');
        const btnAnterior = document.getElementById('btnAnterior');
        const btnSiguiente = document.getElementById('btnSiguiente');

        if (totalPaginas <= 1) {
             document.getElementById('paginationContainer').style.display = 'none';
             return;
        }

        infoPagina.textContent = `Página ${paginaActual} de ${totalPaginas}`;
        btnAnterior.disabled = paginaActual === 1;
        btnSiguiente.disabled = paginaActual === totalPaginas;
    }


    /**
     * Updates the progress bar and hour display based on student data.
     * @param {object} estudiante - Student data object.
     */
    function actualizarProgresoHoras(estudiante) {
        const horasCompletadas = parseFloat(estudiante.horas_completadas) || 0;
        const horasRequeridas = parseFloat(estudiante.horas_requeridas) || 480;

        const porcentaje = Math.min((horasCompletadas / horasRequeridas) * 100, 100);
        const progressBar = document.getElementById('progressBar');
        
        progressBar.style.width = porcentaje + '%';
        progressBar.setAttribute('aria-valuenow', horasCompletadas);
        progressBar.setAttribute('aria-valuemax', horasRequeridas);
        progressBar.textContent = `${porcentaje.toFixed(2)}% (${horasCompletadas}/${horasRequeridas} hrs)`;
        
        document.getElementById('horasCompletadas').textContent = horasCompletadas;
        document.getElementById('horasRequeridas').textContent = horasRequeridas;
        document.getElementById('horasRestantes').textContent = Math.max(0, horasRequeridas - horasCompletadas);
    }

    /**
     * Populates the historical records table.
     * @param {Array<object>} registros - Array of record objects for the current page.
     */
    function llenarTablaRegistros(registros) {
        // MODIFICADO: La lógica de 'no hay registros' se movió a mostrarPagina()
        const tbody = document.querySelector('#tablaRegistros tbody');
        tbody.innerHTML = '';
        
        registros.forEach(registro => {
            const row = document.createElement('tr');
            
            let estadoClass = '';
            switch(registro.estado) {
                case 'aprobado': estadoClass = 'bg-success'; break;
                case 'rechazado': estadoClass = 'bg-danger'; break;
                case 'pendiente': estadoClass = 'bg-warning text-dark'; break;
                default: estadoClass = 'bg-secondary'; break;
            }
            
            row.innerHTML = `
                <td>${formatDate(registro.fecha)}</td>
                <td>${formatTime(registro.hora_entrada)}</td>
                <td>${registro.hora_salida ? formatTime(registro.hora_salida) : '--'}</td>
                <td>${(registro.horas_acumuladas !== null && registro.horas_acumuladas !== undefined) ? parseFloat(registro.horas_acumuladas).toFixed(2) : '--'}</td>
                <td><span class="badge ${estadoClass}">${capitalizeFirstLetter(registro.estado)}</span></td>
            `;
            
            tbody.appendChild(row);
        });
    }

    /**
     * Handles the registration of student's entry time.
     */
    function registrarEntrada() {
        if (!window.confirm('¿Deseas registrar tu entrada ahora?')) return; 
        
        const now = new Date();
        const fechaActual = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
        const horaActual = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}:${String(now.getSeconds()).padStart(2, '0')}`;

        const bodyData = new URLSearchParams();
        bodyData.append('accion', 'registrarEntrada');
        bodyData.append('fecha_entrada', fechaActual);
        bodyData.append('hora_entrada', horaActual); 
        
        fetch('../php/estudiantephp/estudiante.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: bodyData.toString()
        })
        .then(response => {
            if (!response.ok) return response.json().then(errorData => { throw new Error(errorData.error || response.statusText); });
            return response.json();
        })
        .then(data => {
            if (data.error) {
                showAlert(data.error, 'danger');
            } else {
                showAlert(data.mensaje, 'success');
                cargarDatosEstudiante(); // Reload data to see the new record
            }
        })
        .catch(error => {
            console.error('Error al registrar la entrada:', error);
            showAlert(`Error al registrar la entrada: ${error.message}. Intenta de nuevo.`, 'danger');
        });
    }

    /**
     * Handles the registration of student's exit time and calculates hours.
     */
    function registrarSalida() {
        if (!window.confirm('¿Deseas registrar tu salida ahora?')) return; 
        
        const now = new Date();
        const horaActual = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}:${String(now.getSeconds()).padStart(2, '0')}`;

        const bodyData = new URLSearchParams();
        bodyData.append('accion', 'registrarSalida');
        bodyData.append('hora_salida', horaActual); 
        
        fetch('../php/estudiantephp/estudiante.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: bodyData.toString()
        })
        .then(response => {
            if (!response.ok) return response.json().then(errorData => { throw new Error(errorData.error || response.statusText); });
            return response.json();
        })
        .then(data => {
            if (data.error) {
                showAlert(data.error, 'danger');
            } else {
                showAlert(data.mensaje, 'success');
                cargarDatosEstudiante(); // Reload data to see the updated record
            }
        })
        .catch(error => {
            console.error('Error al registrar la salida:', error);
            showAlert(`Error al registrar la salida: ${error.message}. Intenta de nuevo.`, 'danger');
        });
    }

    /**
     * Displays a Bootstrap alert message.
     */
    function showAlert(message, type) {
        const alertContainer = document.getElementById('alertContainer');
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show`;
        alert.setAttribute('role', 'alert');
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        alertContainer.appendChild(alert);
        
        setTimeout(() => {
            if (alert && alert.parentNode) {
                const bsAlert = new bootstrap.Alert(alert);
                if (bsAlert) bsAlert.close();
            }
        }, 5000);
    }

    /**
     * Formats a date string (YYYY-MM-DD) to DD/MM/YYYY.
     */
    function formatDate(dateString) {
        if (!dateString) return '--';
        const date = new Date(dateString + 'T00:00:00'); // Asegura que se interprete como fecha local
        if (isNaN(date.getTime())) return dateString;
        return date.toLocaleDateString('es-MX', { year: 'numeric', month: '2-digit', day: '2-digit' });
    }

    /**
     * Formats a time string to HH:MM:SS (24-hour format).
     */
    function formatTime(timeString) {
        if (!timeString) return '--';
        const [hours, minutes, seconds] = timeString.split(':');
        if (hours === undefined || minutes === undefined || seconds === undefined) return timeString;
        return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
    }

    /**
     * Capitalizes the first letter of a string.
     */
    function capitalizeFirstLetter(string) {
        if (!string) return '';
        return string.charAt(0).toUpperCase() + string.slice(1);
    }
});
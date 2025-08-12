document.addEventListener('DOMContentLoaded', function() {
    // =================================================================================
    // 1. DECLARACIÓN DE VARIABLES Y CONSTANTES
    // =================================================================================

    let tablaEstudiantes, tablaResponsables, tablaAsignaciones;
    let selectedStudentIds = new Set();
    let selectedResponsableId = null;

    const loadingOverlay = document.getElementById('loadingOverlay');
    const btnAsignar = document.getElementById('btnAsignar');
    const contadorEstudiantes = document.getElementById('contadorEstudiantes');
    const responsableSeleccionado = document.getElementById('responsableSeleccionado');
    const selectAllCheckbox = document.getElementById('selectAllEstudiantes');

    const API_URLS = {
        obtenerAsignaciones: '../php/adminphp/obtener_asignaciones.php',
        obtenerEstudiantesSinAsignar: '../php/adminphp/obtener_estudiantes_sin_asignar.php',
        obtenerResponsablesDisponibles: '../php/adminphp/obtener_responsables_disponibles.php',
        asignarEstudiante: '../php/adminphp/asignar_estudiante.php',
        eliminarAsignacion: '../php/adminphp/eliminar_asignacion.php',
        verificarSesion: '../php/verificar_sesion.php'
        // Se elimina la URL de cerrarSesion ya que no se usará
    };

    const LENGUAJE_ESPANOL_DATATABLES = { "sProcessing": "Procesando...", "sLengthMenu": "Mostrar _MENU_ registros", "sZeroRecords": "No se encontraron resultados", "sEmptyTable": "Ningún dato disponible", "sInfo": "Mostrando _START_ al _END_ de _TOTAL_", "sInfoEmpty": "Mostrando 0 al 0 de 0", "sInfoFiltered": "(filtrado de _MAX_ registros)", "sSearch": "Buscar:", "oPaginate": { "sFirst": "Primero", "sLast": "Último", "sNext": "Siguiente", "sPrevious": "Anterior" } };

    // =================================================================================
    // 2. LÓGICA DE SESIÓN Y UI GENERAL (BARRA LATERAL)
    // =================================================================================

    async function gestionarSesionYUI() {
        const userEmailElement = document.getElementById('user-email');
        // La variable y lógica de btnLogout han sido eliminadas.

        try {
            const response = await fetch(API_URLS.verificarSesion);
            const session = await response.json();

            if (!session.activa || session.rol !== 'admin') {
                Swal.fire({ icon: 'error', title: 'Acceso Denegado', text: 'Sesión expirada o sin permisos.' })
                   .then(() => window.location.href = '../login.html');
                return Promise.reject('Sesión no válida');
            }
            
            userEmailElement.textContent = session.nombre_completo || session.correo || 'Usuario';
            // Toda la lógica del botón de cerrar sesión ha sido eliminada.

        } catch (error) {
            Swal.fire({ icon: 'error', title: 'Error de Conexión', text: 'No se pudo verificar la sesión.' })
                .then(() => window.location.href = '../login.html');
            return Promise.reject('Error de conexión');
        }
    }

    // =================================================================================
    // 3. LÓGICA ESPEĆIFICA DE LA PÁGINA (TABLAS Y ASIGNACIONES)
    // =================================================================================

    const showLoading = (show) => loadingOverlay.style.display = show ? 'flex' : 'none';
    const showAlert = (icon, title, text) => Swal.fire({ icon, title, text });

    function initializeTablas() {
        const dataTableConfig = { language: LENGUAJE_ESPANOL_DATATABLES, pageLength: 5, lengthMenu: [5, 10, 25], responsive: true, autoWidth: false };
        tablaEstudiantes = $('#tablaEstudiantes').DataTable({ ...dataTableConfig, ajax: { url: API_URLS.obtenerEstudiantesSinAsignar, dataSrc: 'data' }, columns: [{ data: 'estudiante_id', orderable: false, render: (data) => `<input type="checkbox" class="student-checkbox" value="${data}">` }, { data: 'matricula' }, { data: 'nombre_completo' }] });
        tablaResponsables = $('#tablaResponsables').DataTable({ ...dataTableConfig, ajax: { url: API_URLS.obtenerResponsablesDisponibles, dataSrc: 'data' }, columns: [{ data: 'nombre_completo' }, { data: 'cargo' }] });
        tablaAsignaciones = $('#tablaAsignaciones').DataTable({ ...dataTableConfig, ajax: { url: API_URLS.obtenerAsignaciones, dataSrc: 'data' }, columns: [{ data: 'estudiante' }, { data: 'responsable' }, { data: 'fecha_asignacion', render: data => data ? new Date(data).toLocaleDateString('es-MX') : 'N/A' }, { data: 'asignacion_id', orderable: false, render: data => `<button class="btn btn-sm btn-outline-danger btn-eliminar" data-id="${data}" title="Eliminar"><i class="bi bi-trash"></i></button>` }] });
    }

    function setupEventListeners() {
        selectAllCheckbox.addEventListener('change', function() { $('#tablaEstudiantes').find('.student-checkbox').prop('checked', this.checked).trigger('change'); });
        $('#tablaEstudiantes tbody').on('change', '.student-checkbox', function() { const id = parseInt(this.value, 10); this.checked ? selectedStudentIds.add(id) : selectedStudentIds.delete(id); updateAsignarButtonState(); });
        $('#tablaResponsables tbody').on('click', 'tr', function() { const data = tablaResponsables.row(this).data(); if (!data) return; if ($(this).hasClass('selected')) { $(this).removeClass('selected'); selectedResponsableId = null; responsableSeleccionado.textContent = ''; } else { tablaResponsables.$('tr.selected').removeClass('selected'); $(this).addClass('selected'); selectedResponsableId = data.responsable_id; responsableSeleccionado.textContent = data.nombre_completo; } updateAsignarButtonState(); });
        btnAsignar.addEventListener('click', handleAsignacion);
        $('#tablaAsignaciones').on('click', '.btn-eliminar', function() { handleEliminacion($(this).data('id')); });
    }
    
    function updateAsignarButtonState() {
        btnAsignar.disabled = !(selectedStudentIds.size > 0 && selectedResponsableId !== null);
        contadorEstudiantes.textContent = selectedStudentIds.size;
    }

    async function handleAsignacion() {
        Swal.fire({ title: 'Asignando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        const peticiones = Array.from(selectedStudentIds).map(estudianteId => fetch(API_URLS.asignarEstudiante, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ estudiante_id: estudianteId, responsable_id: selectedResponsableId }) }).then(res => res.ok ? res.json() : Promise.reject(res.json())));
        try {
            await Promise.all(peticiones);
            Swal.fire('¡Éxito!', `${peticiones.length} asignación(es) creada(s).`, 'success');
            selectedStudentIds.clear(); selectedResponsableId = null; responsableSeleccionado.textContent = ''; tablaResponsables.$('tr.selected').removeClass('selected'); selectAllCheckbox.checked = false; updateAsignarButtonState();
            tablaEstudiantes.ajax.reload(); tablaAsignaciones.ajax.reload();
        } catch (error) {
            const err = await error;
            Swal.fire('Error', `No se pudo asignar: ${err.message || 'Error desconocido'}`, 'error');
        }
    }

    function handleEliminacion(id) {
        Swal.fire({ title: '¿Estás seguro?', text: "No podrás revertir esto.", icon: 'warning', showCancelButton: true, confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar' }).then(async (result) => { if (result.isConfirmed) { Swal.fire({ title: 'Eliminando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() }); try { const response = await fetch(API_URLS.eliminarAsignacion, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ asignacion_id: id }) }); const res = await response.json(); if (!res.success) throw new Error(res.message); Swal.fire('Eliminada', 'La asignación fue eliminada.', 'success'); tablaEstudiantes.ajax.reload(); tablaAsignaciones.ajax.reload(); } catch (error) { Swal.fire('Error', `No se pudo eliminar: ${error.message}`, 'error'); } } });
    }
    
    // =================================================================================
    // 4. FUNCIÓN PRINCIPAL DE INICIALIZACIÓN
    // =================================================================================
    
    async function main() {
        showLoading(true);
        try {
            await gestionarSesionYUI();
            initializeTablas();
            setupEventListeners();
        } catch (error) {
            console.error("Inicialización detenida por sesión no válida.");
        } finally {
            showLoading(false);
        }
    }

    // Iniciar la aplicación
    main();
});

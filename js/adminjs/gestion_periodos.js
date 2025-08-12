document.addEventListener('DOMContentLoaded', function() {
    // --- Elementos del DOM ---
    const formCrear = document.getElementById('form-crear-periodo');
    const tablaBody = document.getElementById('tabla-periodos-body');
    const userEmailElement = document.getElementById('user-email');
    const btnLogout = document.getElementById('btn-logout');

    // --- Endpoints de la API ---
    const SESSION_API = '../php/verificar_sesion.php';
    const PERIODOS_API = '../php/adminphp/gestionar_periodos.php';

    // --- Función Principal de Arranque ---
    async function checkSessionAndLoadData() {
        try {
            const response = await fetch(SESSION_API);
            if (!response.ok) throw new Error('Error de red al verificar sesión.');
            
            const sessionData = await response.json();
            if (sessionData.activa && sessionData.rol === 'admin') {
                userEmailElement.textContent = sessionData.nombre_completo || sessionData.correo;
                await cargarPeriodos();
            } else {
                window.location.href = '../index.html';
            }
        } catch (error) {
            console.error('Error de sesión:', error);
            Swal.fire('Error de Sesión', 'No se pudo verificar la sesión. Redirigiendo al login.', 'error')
                .then(() => window.location.href = '../index.html');
        }
    }
    
    // --- Lógica de la Página: Gestión de Períodos con DataTables ---
    async function cargarPeriodos() {
        try {
            const response = await fetch(PERIODOS_API);
            const data = await response.json();

            if (!data.success) throw new Error(data.message);

            if ($.fn.DataTable.isDataTable('#tablaPeriodos')) {
                $('#tablaPeriodos').DataTable().destroy();
            }
            
            tablaBody.innerHTML = ''; 
            
            data.periodos.forEach(p => {
                const estadoBadge = p.estado === 'activo'
                    ? `<span class="badge bg-success">Activo</span>`
                    : `<span class="badge bg-secondary">Inactivo</span>`;
                
                const accionBtn = p.estado === 'activo'
                    ? `<button class="btn btn-warning btn-sm" onclick="manejarAccion('desactivar', ${p.periodo_id})">Desactivar</button>`
                    : `<button class="btn btn-success btn-sm" onclick="manejarAccion('activar', ${p.periodo_id})">Activar</button>`;

                const fila = `<tr>
                        <td>${p.nombre}</td>
                        <td>${p.fecha_inicio}</td>
                        <td>${p.fecha_fin}</td>
                        <td>${estadoBadge}</td>
                        <td class="text-center">${accionBtn}</td>
                    </tr>`;
                tablaBody.innerHTML += fila;
            });
            
            // Inicializar DataTables
            $('#tablaPeriodos').DataTable({
                "pageLength": 5,
                "lengthMenu": [5, 10, 25, 50],
                "language": {
                    "sProcessing": "Procesando...", "sLengthMenu": "Mostrar _MENU_ registros", "sZeroRecords": "No se encontraron resultados",
                    "sEmptyTable": "Ningún dato disponible en esta tabla", "sInfo": "Mostrando _START_ al _END_ de _TOTAL_ registros",
                    "sInfoEmpty": "Mostrando 0 al 0 de 0 registros", "sInfoFiltered": "(filtrado de _MAX_ registros)",
                    "sSearch": "Buscar:", "oPaginate": { "sFirst": "Primero", "sLast": "Último", "sNext": "Siguiente", "sPrevious": "Anterior" }
                },
                "responsive": true, "autoWidth": false
            });

        } catch (error) {
            Swal.fire('Error', 'No se pudieron cargar los períodos: ' + error.message, 'error');
        }
    }

    // --- Función Global para Acciones (Activar/Desactivar) ---
    window.manejarAccion = function(action, periodo_id) {
        const textMap = {
            'activar': 'Esto desactivará cualquier otro período activo. ¿Deseas continuar?',
            'desactivar': '¿Estás seguro de que deseas desactivar este período?'
        };

        Swal.fire({
            title: '¿Estás seguro?', text: textMap[action], icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#8856dd', cancelButtonColor: '#6c757d',
            confirmButtonText: `Sí, ${action}`, cancelButtonText: 'Cancelar'
        }).then(async (result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('action', action);
                formData.append('periodo_id', periodo_id);

                try {
                    const response = await fetch(PERIODOS_API, { method: 'POST', body: formData });
                    const data = await response.json();
                    if (data.success) {
                        Swal.fire('¡Éxito!', 'El período ha sido actualizado.', 'success');
                        cargarPeriodos();
                    } else { throw new Error(data.message); }
                } catch (error) {
                    Swal.fire('Error', 'No se pudo actualizar el período: ' + error.message, 'error');
                }
            }
        });
    }

    // --- Manejadores de Eventos ---
    formCrear.addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(formCrear);
        try {
            const response = await fetch(PERIODOS_API, { method: 'POST', body: formData });
            const data = await response.json();

            if (data.success) {
                Swal.fire('¡Creado!', 'El nuevo período ha sido registrado.', 'success');
                formCrear.reset();
                cargarPeriodos();
            } else { throw new Error(data.message); }
        } catch (error) {
            Swal.fire('Error', 'No se pudo crear el período: ' + error.message, 'error');
        }
    });

    btnLogout.addEventListener('click', function(e) {
        e.preventDefault();
        Swal.fire({
            title: '¿Cerrar sesión?', icon: 'question',
            showCancelButton: true, confirmButtonText: 'Sí, cerrar sesión', cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('../php/cerrar_sesion.php', { method: 'POST' })
                    .then(() => window.location.href = '../index.html');
            }
        });
    });

    // --- Arranque inicial ---
    checkSessionAndLoadData();
});
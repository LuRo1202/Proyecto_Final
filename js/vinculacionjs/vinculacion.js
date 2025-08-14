$(document).ready(function() { 

    // --- 1. VERIFICACIÓN DE SESIÓN AL CARGAR LA PÁGINA ---
    function verificarSesionYActualizarUI() {
        $.ajax({
            url: '../php/verificar_sesion.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success && response.rol === 'vinculacion') {
                    // Si la sesión es válida y el rol es correcto, actualizamos la UI.
                    $('#userName').text(response.nombre_completo || 'Usuario Vinculación');
                    $('#userEmail').text(response.correo || 'rol@uptex.edu.mx');
                    
                    // Una vez verificado, se inicializa el resto de la página.
                    inicializarComponentes();
                } else {
                    // Si no es válido, mostramos error y redirigimos al login.
                    Swal.fire({
                        icon: 'error',
                        title: 'Acceso Denegado',
                        text: 'No tienes permiso para ver esta página. Serás redirigido.',
                        timer: 2500,
                        showConfirmButton: false,
                        allowOutsideClick: false
                    }).then(() => {
                        window.location.href = '../login.html';
                    });
                }
            },
            error: function() {
                // Error de conexión con el servidor.
                Swal.fire({
                    icon: 'error',
                    title: 'Error de Conexión',
                    text: 'No se pudo verificar tu sesión. Inténtalo más tarde.',
                    allowOutsideClick: false
                }).then(() => {
                    window.location.href = '../login.html';
                });
            }
        });
    }

    // --- 2. INICIALIZACIÓN DE COMPONENTES (SE LLAMA DESPUÉS DE VERIFICAR LA SESIÓN) ---
    function inicializarComponentes() {
        cargarCarreras();
        cargarPeriodos();
        inicializarDataTable();

        // Asignar eventos a los botones de la interfaz
        $('#btnGenerarPorCarrera').on('click', handleGenerarPorCarrera);
        $('#btnFiltrar').on('click', () => $('#tablaVinculacion').DataTable().ajax.reload());
        
        $('#btnRefrescar').on('click', () => {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Tabla actualizada',
                showConfirmButton: false,
                timer: 1500
            });
            $('#tablaVinculacion').DataTable().ajax.reload(null, false);
        });

        $('#btnLimpiar').on('click', () => {
            $('#filtroForm')[0].reset();
            $('#tablaVinculacion').DataTable().ajax.reload();
        });
    }

    // Funciones para cargar datos en los <select> de los filtros
    function cargarCarreras() {
        $.ajax({
            url: '../php/vinculacionphp/obtener_carreras.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                const select = $('#filtroCarrera').html('<option value="">-- Selecciona una carrera --</option>');
                response.forEach(c => select.append(new Option(c.carrera, c.carrera)));
            }
        });
    }

    function cargarPeriodos() {
        $.ajax({
            url: '../php/vinculacionphp/obtener_periodos.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                const select = $('#filtroPeriodo').html('<option value="">Todos los periodos</option>');
                response.forEach(p => select.append(new Option(p.nombre, p.periodo_id)));
            }
        });
    }

    // Lógica para el botón de generación masiva
    function handleGenerarPorCarrera() {
        const carrera = $('#filtroCarrera').val();
        if (!carrera) {
            Swal.fire('Atención', 'Por favor, selecciona una carrera para continuar.', 'warning');
            return;
        }
        Swal.fire({
            title: '¿Generar cartas en lote?',
            text: `Se generarán las cartas de Presentación y Aceptación para todos los alumnos de "${carrera}". Esta acción abrirá una nueva pestaña.`,
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, generar',
            cancelButtonText: 'Cancelar'
        }).then(result => {
            if (result.isConfirmed) {
                window.open(`../php/vinculacionphp/generar_cartas_carrera.php?carrera=${encodeURIComponent(carrera)}`, '_blank');
            }
        });
    }

    // --- 3. CONFIGURACIÓN DE DATATABLE Y FUNCIONES DE RENDERIZADO ---
    function inicializarDataTable() {
        $('#tablaVinculacion').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "../php/vinculacionphp/obtener_vinculaciones.php",
                "type": "POST",
                "data": d => {
                    d.periodo = $('#filtroPeriodo').val();
                    d.estadoCarta = $('#filtroCarta').val();
                    d.alumno = $('#filtroAlumno').val();
                },
                "error": (jqXHR) => {
                    console.error("Error en AJAX de DataTables:", jqXHR.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error Crítico al Cargar Datos',
                        html: `No se pudieron cargar los datos de los alumnos. Revisa la consola del navegador (F12) para más detalles.`
                    });
                }
            },
            "columns": [
                // ===== CAMBIO PRINCIPAL AQUÍ =====
                // La columna ID se mantiene para acceder al dato, pero se oculta de la vista.
                { "data": "solicitud_id", "visible": false, "searchable": false }, 
                { "data": "nombre_alumno" },
                { "data": "matricula" },
                { "data": "nombre_entidad" },
                { "data": "documentos_subidos", "render": renderizarIconosDocumentos, "orderable": false, "searchable": false },
                { "data": "estado_carta_presentacion", "render": (data, type, row) => renderizarBotonEstado(data, row, 'presentacion') },
                { "data": "estado_carta_aceptacion", "render": (data, type, row) => renderizarBotonEstado(data, row, 'aceptacion') },
                { "data": "estado_primer_informe", "render": (data, type, row) => renderizarBotonEstado(data, row, 'primer_informe') },
                { "data": "estado_segundo_informe", "render": (data, type, row) => renderizarBotonEstado(data, row, 'segundo_informe') },
                { "data": "estado_comprobante_pago", "render": (data, type, row) => renderizarBotonEstado(data, row, 'pago') },
                { "data": null, "render": renderizarAccionesGenerar, "orderable": false, "searchable": false }
            ],
            "pageLength": 10,
            "lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "Todos"] ],
            "columnDefs": [{ "className": "text-center align-middle", "targets": [3, 4, 10] }], // Los índices se mantienen gracias a "visible:false"
            "language": { "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" },
            "responsive": true,
            "bFilter": false, // Desactiva el filtro nativo de DataTables
            "drawCallback": function(settings) {
                // Inicializa los tooltips de Bootstrap después de que la tabla se dibuje/redibuje
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }
        });
    }

    function renderizarIconosDocumentos(data) {
        const documentos = {};
        if (data) {
            data.split('||').forEach(docInfo => {
                const parts = docInfo.split('::');
                if (parts.length === 3) documentos[parts[0]] = parts[2];
            });
        }
        const icons = {
            presentacion: { id: documentos['1'], icon: 'bi-file-earmark-text-fill', title: 'Carta de Presentación' },
            aceptacion:   { id: documentos['6'], icon: 'bi-file-earmark-check-fill', title: 'Carta de Aceptación' },
            informe1:     { id: documentos['2'], icon: 'bi-file-earmark-bar-graph-fill', title: 'Primer Informe' },
            informe2:     { id: documentos['3'], icon: 'bi-file-earmark-bar-graph-fill', title: 'Segundo Informe' },
            pago:         { id: documentos['5'], icon: 'bi-receipt-cutoff', title: 'Comprobante de Pago' }
        };
        const html = Object.values(icons).map(doc => doc.id
            ? `<a href="../php/vinculacionphp/ver_documento.php?id=${doc.id}" target="_blank" class="text-success me-2" title="Ver ${doc.title}"><i class="bi ${doc.icon}"></i></a>`
            : `<i class="bi ${doc.icon} text-muted me-2" title="${doc.title} (Faltante)"></i>`
        ).join(' ');
        
        return `<div class="fs-5" style="white-space: nowrap;">${html}</div>`;
    }

    function renderizarAccionesGenerar(data, type, row) {
        return `
        <div class="btn-group">
            <button type="button" class="btn btn-info btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-printer"></i> Generar
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#" onclick="generarDocumento('presentacion', ${row.solicitud_id})">Carta de Presentación</a></li>
                <li><a class="dropdown-item" href="#" onclick="generarDocumento('aceptacion', ${row.solicitud_id})">Constancia de Aceptación</a></li>
                <li><a class="dropdown-item" href="#" onclick="generarDocumento('termino', ${row.solicitud_id})">Carta de Término</a></li>
            </ul>
        </div>`;
    }

    function renderizarBotonEstado(status, row, tipoDoc) {
        const currentStatus = status || 'Pendiente';
        const acciones = ['Pendiente', 'Aprobada', 'Rechazada'];
        let claseBoton = 'secondary';
        if (currentStatus.toLowerCase() === 'aprobada') claseBoton = 'success';
        if (currentStatus.toLowerCase() === 'rechazada') claseBoton = 'danger';
        if (currentStatus.toLowerCase() === 'pendiente') claseBoton = 'warning';
        
        const opcionesDropdown = acciones
            .filter(estado => estado.toLowerCase() !== currentStatus.toLowerCase())
            .map(estado => `<li><a class="dropdown-item" href="#" onclick="actualizarEstado(${row.solicitud_id}, '${tipoDoc}', '${estado}')">${estado}</a></li>`)
            .join('');

        return `<div class="btn-group d-flex"><button type="button" class="btn btn-${claseBoton} btn-sm dropdown-toggle w-100" data-bs-toggle="dropdown" aria-expanded="false">${currentStatus}</button><ul class="dropdown-menu">${opcionesDropdown}</ul></div>`;
    }
    
    // --- INICIAR EL PROCESO DE CARGA DE LA PÁGINA ---
    verificarSesionYActualizarUI();
});

// --- FUNCIONES GLOBALES (necesitan estar fuera de document.ready para ser accesibles desde el HTML con onclick) ---

function generarDocumento(tipo, solicitudId) {
    window.open(`../php/vinculacionphp/generar_carta.php?tipo=${tipo}&id=${solicitudId}`, '_blank');
}

function actualizarEstado(solicitudId, tipoDoc, nuevoEstado) {
    const nombreDoc = tipoDoc.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    Swal.fire({
        title: `¿Confirmar acción?`,
        text: `Se cambiará el estado del documento "${nombreDoc}" a "${nuevoEstado}".`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#8856dd',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, confirmar',
        cancelButtonText: 'Cancelar'
    }).then(result => {
        if (result.isConfirmed) {
            $.ajax({
                url: '../php/vinculacionphp/actualizar_estado_carta.php',
                type: 'POST',
                data: {
                    solicitud_id: solicitudId,
                    tipo_documento: tipoDoc,
                    nuevo_estado: nuevoEstado
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('¡Éxito!', response.message, 'success');
                        $('#tablaVinculacion').DataTable().ajax.reload(null, false);
                    } else {
                        Swal.fire('Error', response.message || 'Ocurrió un error desconocido.', 'error');
                    }
                },
                error: (jqXHR) => {
                    console.error("Error al actualizar estado:", jqXHR.responseText);
                    Swal.fire('Error de Conexión', 'No se pudo comunicar con el servidor para actualizar el estado.', 'error');
                }
            });
        }
    });
}
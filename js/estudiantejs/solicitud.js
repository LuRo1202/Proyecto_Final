document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formSolicitud');
    const spinner = document.getElementById('spinnerContainer');
    const alertContainer = document.getElementById('alertContainer');
    const btnGuardar = document.getElementById('btnGuardar');
    const btnGenerar = document.getElementById('btnGenerar');

    function showAlert(message, type = 'danger') {
        const wrapper = document.createElement('div');
        wrapper.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>`;
        alertContainer.append(wrapper);
        setTimeout(() => wrapper.remove(), 5000);
    }

    async function cargarDatosSolicitud() {
        try {
            const response = await fetch('../php/estudiantephp/gestionar_solicitud.php', {
                method: 'GET',
                headers: { 'Content-Type': 'application/json' }
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.error || `Error HTTP: ${response.status}`);
            }

            const data = await response.json();
            
            // Rellenar el formulario
            document.getElementById('solicitud_id').value = data.solicitud_id || '';
            document.getElementById('entidad_id').value = data.entidad_id || '';
            document.getElementById('programa_id').value = data.programa_id || '';
            
            // Datos personales
            document.getElementById('nombre').value = data.nombre || '';
            document.getElementById('apellido_paterno').value = data.apellido_paterno || '';
            document.getElementById('apellido_materno').value = data.apellido_materno || '';
            document.getElementById('matricula').value = data.matricula || '';
            document.getElementById('curp').value = data.curp || '';
            document.getElementById('sexo').value = data.sexo || '';
            document.getElementById('correo').value = data.correo || '';
            document.getElementById('edad').value = data.edad || '';
            document.getElementById('telefono').value = data.telefono || '';
            document.getElementById('domicilio').value = data.domicilio || '';
            document.getElementById('facebook').value = data.facebook || '';
            document.getElementById('carrera').value = data.carrera || '';
            document.getElementById('cuatrimestre').value = data.cuatrimestre || '';
            document.getElementById('porcentaje_creditos').value = data.porcentaje_creditos || '';
            document.getElementById('promedio').value = data.promedio || '';

            // Datos de la entidad
            document.getElementById('entidad_nombre').value = data.entidad_nombre || '';
            document.getElementById('tipo_entidad').value = data.tipo_entidad || '';
            document.getElementById('unidad_administrativa').value = data.unidad_administrativa || '';
            document.getElementById('entidad_domicilio').value = data.entidad_domicilio || '';
            document.getElementById('entidad_municipio').value = data.entidad_municipio || '';
            document.getElementById('entidad_telefono').value = data.entidad_telefono || '';
            document.getElementById('funcionario_responsable').value = data.funcionario_responsable || '';
            document.getElementById('cargo_funcionario').value = data.cargo_funcionario || '';
            
            // Datos del servicio
            document.getElementById('programa_nombre').value = data.programa_nombre || '';
            document.getElementById('actividades').value = data.actividades || '';
            document.getElementById('fecha_solicitud').value = data.fecha_solicitud || '';
            document.getElementById('periodo_nombre').value = data.periodo_nombre || '';
            document.getElementById('periodo_inicio').value = data.periodo_inicio || '';
            document.getElementById('periodo_fin').value = data.periodo_fin || '';
            
            // Horarios
            document.getElementById('horario_lv_inicio').value = data.horario_lv_inicio || '';
            document.getElementById('horario_lv_fin').value = data.horario_lv_fin || '';
            document.getElementById('horario_sd_inicio').value = data.horario_sd_inicio || '';
            document.getElementById('horario_sd_fin').value = data.horario_sd_fin || '';

            spinner.style.display = 'none';
            form.style.display = 'flex';
        } catch (error) {
            spinner.innerHTML = `<p class="text-danger">Error al cargar los datos: ${error.message}</p>`;
            showAlert(`No se pudieron cargar los datos de la solicitud. ${error.message}`, 'danger');
        }
    }

    btnGuardar.addEventListener('click', async () => {
        if (!form.checkValidity()) {
            form.reportValidity();
            showAlert('Por favor, completa todos los campos requeridos.', 'warning');
            return;
        }

        const formData = {
            solicitud_id: document.getElementById('solicitud_id').value,
            entidad_id: document.getElementById('entidad_id').value,
            programa_id: document.getElementById('programa_id').value,
            
            // Datos personales
            nombre: document.getElementById('nombre').value,
            apellido_paterno: document.getElementById('apellido_paterno').value,
            apellido_materno: document.getElementById('apellido_materno').value,
            matricula: document.getElementById('matricula').value,
            curp: document.getElementById('curp').value.toUpperCase(),
            sexo: document.getElementById('sexo').value,
            edad: document.getElementById('edad').value,
            telefono: document.getElementById('telefono').value,
            domicilio: document.getElementById('domicilio').value,
            facebook: document.getElementById('facebook').value,
            carrera: document.getElementById('carrera').value,
            cuatrimestre: document.getElementById('cuatrimestre').value,
            porcentaje_creditos: document.getElementById('porcentaje_creditos').value,
            promedio: document.getElementById('promedio').value,
            
            // Datos de la entidad
            entidad_nombre: document.getElementById('entidad_nombre').value,
            tipo_entidad: document.getElementById('tipo_entidad').value,
            unidad_administrativa: document.getElementById('unidad_administrativa').value,
            entidad_domicilio: document.getElementById('entidad_domicilio').value,
            entidad_municipio: document.getElementById('entidad_municipio').value,
            entidad_telefono: document.getElementById('entidad_telefono').value,
            funcionario_responsable: document.getElementById('funcionario_responsable').value,
            cargo_funcionario: document.getElementById('cargo_funcionario').value,
            
            // Datos del servicio
            programa_nombre: document.getElementById('programa_nombre').value,
            actividades: document.getElementById('actividades').value,
            periodo_inicio: document.getElementById('periodo_inicio').value,
            periodo_fin: document.getElementById('periodo_fin').value,
            horario_lv_inicio: document.getElementById('horario_lv_inicio').value,
            horario_lv_fin: document.getElementById('horario_lv_fin').value,
            horario_sd_inicio: document.getElementById('horario_sd_inicio').value,
            horario_sd_fin: document.getElementById('horario_sd_fin').value
        };
        
        btnGuardar.disabled = true;
        btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...';

        try {
            const response = await fetch('../php/estudiantephp/gestionar_solicitud.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });

            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.error || `Error del servidor: ${response.status}`);
            }

            showAlert(result.success, 'success');
        } catch (error) {
            showAlert(`Error al guardar los cambios: ${error.message}`, 'danger');
        } finally {
            btnGuardar.disabled = false;
            btnGuardar.innerHTML = '<i class="bi bi-save-fill me-2"></i>Guardar Cambios';
        }
    });

    btnGenerar.addEventListener('click', () => {
        const solicitudId = document.getElementById('solicitud_id').value;
        if (solicitudId) {
            window.open(`imprimir_solicitud.html?id=${solicitudId}`, '_blank');
        } else {
            showAlert('No se puede generar la solicitud porque no se ha cargado ninguna.', 'warning');
        }
    });

    cargarDatosSolicitud();
});
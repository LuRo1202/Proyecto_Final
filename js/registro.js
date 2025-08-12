document.addEventListener('DOMContentLoaded', () => {
    // 1. Obtener referencias
    const form = document.getElementById('formSolicitud');
    const steps = Array.from(document.querySelectorAll('.form-step'));
    const progressSteps = Array.from(document.querySelectorAll('.progress-bar-steps .step'));
    const formStepTitle = document.getElementById('form-step-title');
    const alertContainer = document.getElementById('alertContainer');
    const btnPrev = document.getElementById('btn-prev');
    const btnNext = document.getElementById('btn-next');
    const btnSubmit = document.getElementById('btn-submit');
    const emailInput = document.getElementById('correo');
    const passwordInput = document.getElementById('contrasena');
    const togglePassword = document.getElementById('togglePassword');
    const periodoSelect = document.getElementById('periodo_registro_id');
    const entidadSelect = document.getElementById('entidad_nombre');
    const otraEntidadContainer = document.getElementById('otra-entidad-container');
    const otraEntidadInput = document.getElementById('otra_entidad_nombre');
    const programaSelect = document.getElementById('programa_nombre');
    const otroProgramaContainer = document.getElementById('otro-programa-container');
    const otroProgramaInput = document.getElementById('otro_programa_nombre');

    const entidadFields = {
        tipo: document.getElementById('tipo_entidad'),
        domicilio: document.getElementById('entidad_domicilio'),
        municipio: document.getElementById('municipio'),
        telefono: document.getElementById('entidad_telefono'),
        unidad: document.getElementById('unidad_administrativa'),
        funcionario: document.getElementById('funcionario_responsable'),
        cargo: document.getElementById('cargo_funcionario'),
    };

    let currentStep = 0;
    let emailTimeout = null;
    const titles = [
        "Paso 1: Datos de Acceso",
        "Paso 2: Datos del Estudiante",
        "Paso 3: Datos de la Entidad Receptora",
        "Paso 4: Detalles del Programa"
    ];

    // 2. Funciones auxiliares
    const showAlert = (msg, type='info') => {
        alertContainer.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${msg}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>`;
        setTimeout(() => {
            alertContainer.innerHTML = '';
        }, 5000);
    };

    const updateProgress = (step) => {
        progressSteps.forEach((el, i) => {
            el.classList.toggle('active', i === step);
            el.classList.toggle('completed', i < step);
        });
    };

    const showStep = (step) => {
        steps.forEach((el, i) => el.classList.toggle('active', i === step));
        formStepTitle.textContent = titles[step];
        btnPrev.style.display = step === 0 ? 'none' : 'inline-block';
        btnNext.style.display = step === steps.length - 1 ? 'none' : 'inline-block';
        btnSubmit.style.display = step === steps.length - 1 ? 'inline-block' : 'none';
        updateProgress(step);
    };

    const validateStep = (step) => {
        let valid = true;
        const inputs = steps[step].querySelectorAll('input[required]:not([readonly]), select[required], textarea[required]');
        inputs.forEach(input => {
            if (!input.checkValidity()) {
                input.classList.add('is-invalid');
                valid = false;
            } else {
                input.classList.remove('is-invalid');
                input.classList.add('is-valid');
            }
        });
        if (step === 0 && !emailInput.classList.contains('is-valid')) {
            showAlert('Valida tu correo primero.', 'warning');
            valid = false;
        }
        return valid;
    };

    // 3. Carga dinámica de períodos
    const loadPeriodos = async () => {
        try {
            const res = await fetch('php/get_periodos.php');
            if (!res.ok) throw new Error(res.statusText);
            const data = await res.json();
            periodoSelect.innerHTML = '';
            if (!data.length) {
                periodoSelect.innerHTML = '<option value="">Sin períodos</option>';
                periodoSelect.disabled = true;
                return;
            }
            data.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.periodo_id;
                opt.textContent = p.nombre;
                opt.selected = p.estado === 'activo';
                periodoSelect.append(opt);
            });
            periodoSelect.disabled = false;
        } catch (e) {
            showAlert('No se pudieron cargar períodos.', 'danger');
            periodoSelect.innerHTML = '<option value="">Error</option>';
            periodoSelect.disabled = true;
        }
    };

    // 4. Manejar cambio de entidad
    const onEntidadChange = () => {
        const isUP = entidadSelect.value === 'Universidad Politécnica de Texcoco';
        const isOther = entidadSelect.value === 'Otra';
        // reset
        Object.values(entidadFields).forEach(f => {
            f.readOnly = false; f.required = true; f.classList.remove('is-valid','is-invalid'); f.value = '';
        });
        otraEntidadContainer.style.display = 'none';
        otraEntidadInput.required = false; otraEntidadInput.value = '';

        if (isUP) {
            entidadFields.tipo.value = 'I.E.';
            entidadFields.domicilio.value = 'Carretera Federal Texcoco-Lechería Km. 36.5, San Joaquín Coapango';
            entidadFields.municipio.value = 'Texcoco';
            entidadFields.telefono.value = '5559521000';
            ['tipo','domicilio','municipio','telefono'].forEach(k => {
                entidadFields[k].readOnly = true;
                entidadFields[k].classList.add('is-valid');
                entidadFields[k].required = false;
            });
        } else if (isOther) {
            otraEntidadContainer.style.display = 'block';
            otraEntidadInput.required = true;
        }
    };

    // 5. Validación de email asíncrona
    emailInput.addEventListener('input', () => {
        clearTimeout(emailTimeout);
        emailInput.classList.remove('is-valid','is-invalid');
        if (!emailInput.checkValidity()) return;
        emailTimeout = setTimeout(async () => {
            try {
                const resp = await fetch('php/check_email.php', {
                    method:'POST',
                    headers:{'Content-Type':'application/json'},
                    body: JSON.stringify({ correo: emailInput.value.trim() })
                });
                const { exists } = await resp.json();
                emailInput.classList.toggle('is-invalid', exists);
                emailInput.classList.toggle('is-valid', !exists);
            } catch {
                emailInput.classList.add('is-invalid');
            }
        }, 500);
    });

    // 6. Toggle contraseña
    togglePassword.addEventListener('click', () => {
        const type = passwordInput.type === 'password' ? 'text' : 'password';
        passwordInput.type = type;
        togglePassword.querySelector('i').classList.toggle('bi-eye', type === 'password');
        togglePassword.querySelector('i').classList.toggle('bi-eye-slash', type === 'text');
    });

    // 7. Siguiente/anterior
    btnNext.addEventListener('click', () => {
        if (validateStep(currentStep)) {
            currentStep++;
            showStep(currentStep);
        }
    });
    btnPrev.addEventListener('click', () => {
        currentStep--;
        showStep(currentStep);
    });

    // 8. Otro programa
    programaSelect.addEventListener('change', () => {
        const isOther = programaSelect.value === 'Otro';
        otroProgramaContainer.style.display = isOther ? 'block' : 'none';
        otroProgramaInput.required = isOther;
        if (!isOther) otroProgramaInput.value = '';
    });

    // 9. Cambios entidad
    entidadSelect.addEventListener('change', onEntidadChange);

    // 10. Envío del formulario
    form.addEventListener('submit', async e => {
        e.preventDefault();
        if (!validateStep(currentStep)) return;
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Enviando...';

        const raw = Object.fromEntries(new FormData(form).entries());
        if (raw.entidad_nombre === 'Otra') raw.entidad_nombre = raw.otra_entidad_nombre;
        if (raw.programa_nombre === 'Otro') raw.programa_nombre = raw.otro_programa_nombre;
        delete raw.otra_entidad_nombre;
        delete raw.otro_programa_nombre;

        try {
            const resp = await fetch('php/registrar_solicitud.php', {
                method:'POST',
                headers:{ 'Content-Type':'application/json' },
                body: JSON.stringify(raw)
            });
            const json = await resp.json();
            if (json.success) {
                showAlert('¡Registro exitoso!', 'success');
                setTimeout(() => window.location.href = 'index.html', 2000);
            } else {
                showAlert(json.message || 'Error al enviar.', 'danger');
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = '<i class="bi bi-check-circle"></i> Enviar Solicitud';
            }
        } catch {
            showAlert('Error de conexión.', 'danger');
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="bi bi-check-circle"></i> Enviar Solicitud';
        }
    });

    // 11. Inicializar
    loadPeriodos();
    showStep(0);
});

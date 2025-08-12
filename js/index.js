document.addEventListener('DOMContentLoaded', () => {
    // --- REFERENCIAS A ELEMENTOS DEL DOM ---
    const formLogin = document.getElementById('formLogin');
    const linkRegistro = document.getElementById('link-registro');
    const alertContainer = document.getElementById('alertContainer');
    
    if (!formLogin || !linkRegistro || !alertContainer) {
        console.error("Faltan elementos esenciales en el HTML: formLogin, link-registro o alertContainer.");
        return;
    }

    // --- FUNCIONES ---

    /** Muestra una alerta de Bootstrap que se cierra automáticamente. */
    const showAlert = (message, type = 'info') => {
        const wrapper = document.createElement('div');
        wrapper.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>`;
        alertContainer.innerHTML = ''; // Limpiar alertas anteriores
        alertContainer.append(wrapper.firstElementChild);
        
        setTimeout(() => {
            wrapper.querySelector('.alert')?.remove();
        }, 5000);
    };

    /** Verifica si hay un período de registro activo en el servidor. */
    const verificarPeriodoActivo = async () => {
        try {
            // La opción 'no-store' es crucial para evitar el caché del navegador
            const response = await fetch('php/verificar_periodo_activo.php', { cache: 'no-store' });
            if (!response.ok) {
                throw new Error(`Error del servidor: ${response.statusText}`);
            }
            const data = await response.json();

            if (data.activo) {
                linkRegistro.classList.remove('link-disabled');
                linkRegistro.href = 'registrar.html';
            } else {
                linkRegistro.classList.add('link-disabled');
                linkRegistro.removeAttribute('href');
            }
        } catch (error) {
            console.error('Error al verificar el período:', error);
            linkRegistro.classList.add('link-disabled');
            linkRegistro.removeAttribute('href');
            // Opcional: notificar al usuario que la verificación falló.
            // showAlert('No se pudo verificar el estado del registro.', 'warning');
        }
    };

    /** Maneja el envío del formulario de login. */
    const handleLogin = async (event) => {
        event.preventDefault();
        const btn = formLogin.querySelector('button[type="submit"]');
        const originalBtnHTML = btn.innerHTML;

        btn.disabled = true;
        btn.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Validando...`;

        try {
            const formData = new FormData(formLogin);
            const response = await fetch('php/autenticacion.php', {
                method: 'POST',
                body: formData
            });

            // Si la autenticación es exitosa, el PHP redirigirá.
            // La propiedad 'redirected' nos dice si eso ocurrió.
            if (response.redirected) {
                window.location.href = response.url;
            } else {
                // Si no hay redirección, es un error de login.
                showAlert('Correo o contraseña incorrectos.', 'danger');
            }
        } catch (error) {
            console.error('Error en la solicitud de login:', error);
            showAlert('Error de conexión. Inténtalo de nuevo.', 'danger');
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalBtnHTML;
        }
    };

    /** Procesa los mensajes de la URL (ej. logout, error). */
    const procesarUrlParams = () => {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has("logout")) {
            showAlert("Has cerrado sesión correctamente.", "success");
        } else if (urlParams.has("session_expired")) {
            showAlert("Tu sesión ha expirado. Por favor, inicia sesión de nuevo.", "warning");
        } else if (urlParams.has("error")) {
            showAlert("Correo o contraseña incorrectos.", "danger");
        }
        // Limpia la URL para que los mensajes no aparezcan si el usuario recarga.
        history.replaceState(null, '', window.location.pathname);
    };

    // --- INICIALIZACIÓN ---
    procesarUrlParams();
    verificarPeriodoActivo();
    formLogin.addEventListener("submit", handleLogin);
});
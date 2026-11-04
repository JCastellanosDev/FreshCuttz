(() => {
    const bienvenida = document.getElementById('bienvenida-perfil');
    const abrir = document.getElementById('abrir-perfil');
    const cerrarSesion = document.getElementById('cerrar-sesion');
    function actualizarPerfil() {
        if (abrir) abrir.hidden = false;
        if (cerrarSesion) cerrarSesion.hidden = true;
        if (bienvenida) {
            bienvenida.hidden = true;
            bienvenida.textContent = '';
        }
        try {
            const nombreGuardado = sessionStorage.getItem('freshcuttz-nombre') || '';
            if (nombreGuardado.length >= 2 && nombreGuardado.length <= 100 &&
                /^[A-Za-zÁÉÍÓÚÜÑáéíóúüñ ]+$/.test(nombreGuardado)) {
                if (abrir) abrir.hidden = true;
                if (cerrarSesion) cerrarSesion.hidden = false;
                if (bienvenida) {
                    bienvenida.textContent = `Bienvenido, ${nombreGuardado}`;
                    bienvenida.hidden = false;
                }
            }
        } catch {
            // La página sigue funcionando si el navegador bloquea el almacenamiento.
        }
    }
    const API_CUENTA = 'api/cuenta.php';

    function guardarNombre(usuario) {
        try {
            if (usuario?.nombre) sessionStorage.setItem('freshcuttz-nombre', usuario.nombre);
            else sessionStorage.removeItem('freshcuttz-nombre');
        } catch {
            // Sin almacenamiento solo se pierde el saludo; la sesión vive en el servidor.
        }
    }

    // La sesión real está en PHP; sincronizamos el saludo con lo que diga el servidor.
    async function sincronizarSesion() {
        try {
            const respuesta = await fetch(API_CUENTA, { credentials: 'same-origin' });
            if (respuesta.ok) guardarNombre((await respuesta.json()).usuario);
        } catch {
            // Si el servidor no responde, mostramos el último estado conocido.
        }
        actualizarPerfil();
    }

    actualizarPerfil();
    sincronizarSesion();
    window.addEventListener('pageshow', actualizarPerfil);
    cerrarSesion?.addEventListener('click', async () => {
        cerrarSesion.disabled = true;
        try {
            await fetch(API_CUENTA, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ accion: 'salir' })
            });
        } catch {
            // Aunque falle la red, limpiamos el saludo local.
        }
        guardarNombre(null);
        actualizarPerfil();
        window.location.href = 'index.php';
    });
    const modal = document.getElementById('modal-login');
    const formulario = document.getElementById('formulario-login');
    const nombre = document.getElementById('login-nombre');
    const telefono = document.getElementById('login-telefono');
    const mostrar = document.getElementById('mostrar-clave');
    const estado = document.getElementById('login-estado');
    const tengoCuenta = document.getElementById('ya-tengo-cuenta');
    const descripcion = document.getElementById('descripcion-login');
    if (!abrir || !modal || !formulario) return;
    const descripcionInicial = descripcion.textContent;
    const titulo = document.getElementById('titulo-login');
    const enviar = formulario.querySelector('button[type="submit"]');
    let modo = 'registro';

    function cambiarModo(nuevoModo) {
        modo = nuevoModo;
        const esLogin = modo === 'login';
        titulo.textContent = esLogin ? 'Iniciar sesión' : 'Crear cuenta';
        enviar.textContent = esLogin ? 'Iniciar sesión' : 'Crear cuenta';
        descripcion.textContent = esLogin
            ? 'Ingresa el nombre y el teléfono de tu cuenta para continuar.'
            : descripcionInicial;
        tengoCuenta.hidden = esLogin;
    }

    tengoCuenta.addEventListener('click', () => {
        cambiarModo('login');
        estado.textContent = '';
        nombre.focus();
    });

    function validar(campo) {
        const valor = campo.value.normalize('NFC');
        let mensaje = '';
        if (campo === nombre) {
            if (!valor.trim()) mensaje = 'Escribe tu nombre.';
            else if (!/^[A-Za-zÁÉÍÓÚÜÑáéíóúüñ ]+$/.test(valor)) {
                mensaje = 'Solo se permiten letras, acentos, ñ y espacios. No uses números ni símbolos.';
            } else if (valor.trim().length < 2 || valor.trim().length > 100) {
                mensaje = 'El nombre debe tener entre 2 y 100 caracteres.';
            }
        } else if (!valor) mensaje = 'Escribe tu número de teléfono como contraseña.';
        else if (!/^[0-9]+$/.test(valor)) {
            mensaje = 'Solo se permiten números del 0 al 9, sin espacios, letras ni signos.';
        } else if (valor.length !== 10) mensaje = 'Ingresa exactamente 10 números, sin lada internacional.';

        campo.setCustomValidity(mensaje);
        campo.setAttribute('aria-invalid', String(Boolean(mensaje)));
        document.getElementById(campo === nombre ? 'login-error-nombre' : 'login-error-telefono').textContent = mensaje;
        return !mensaje;
    }

    function ocultarClave() {
        telefono.type = 'password';
        mostrar.textContent = 'Mostrar';
        mostrar.setAttribute('aria-pressed', 'false');
        mostrar.setAttribute('aria-label', 'Mostrar contraseña');
    }

    formulario.noValidate = true;
    [nombre, telefono].forEach(campo => {
        campo.addEventListener('input', () => {
            estado.textContent = '';
            validar(campo);
        });
        campo.addEventListener('blur', () => {
            if (campo === nombre) campo.value = campo.value.normalize('NFC').trim();
            validar(campo);
        });
    });
    abrir.addEventListener('click', () => {
        modal.showModal();
        abrir.setAttribute('aria-expanded', 'true');
        document.documentElement.classList.add('login-abierto');
        nombre.focus();
    });
    document.getElementById('cerrar-login').addEventListener('click', () => modal.close());
    modal.addEventListener('click', event => {
        const borde = modal.getBoundingClientRect();
        if (event.target === modal && (event.clientX < borde.left || event.clientX > borde.right ||
            event.clientY < borde.top || event.clientY > borde.bottom)) modal.close();
    });
    modal.addEventListener('close', () => {
        cambiarModo('registro');
        formulario.reset();
        [nombre, telefono].forEach(campo => {
            campo.setCustomValidity('');
            campo.removeAttribute('aria-invalid');
        });
        document.getElementById('login-error-nombre').textContent = '';
        document.getElementById('login-error-telefono').textContent = '';
        estado.textContent = '';
        ocultarClave();
        abrir.setAttribute('aria-expanded', 'false');
        document.documentElement.classList.remove('login-abierto');
        abrir.focus();
    });
    mostrar.addEventListener('click', () => {
        if (telefono.type === 'text') ocultarClave();
        else {
            telefono.type = 'text';
            mostrar.textContent = 'Ocultar';
            mostrar.setAttribute('aria-pressed', 'true');
            mostrar.setAttribute('aria-label', 'Ocultar contraseña');
        }
    });
    formulario.addEventListener('submit', async event => {
        event.preventDefault();
        if (enviar.disabled) return;
        nombre.value = nombre.value.normalize('NFC').trim();
        const nombreValido = validar(nombre);
        const telefonoValido = validar(telefono);
        estado.textContent = '';
        if (!nombreValido || !telefonoValido) {
            const campo = nombreValido ? telefono : nombre;
            campo.focus();
            campo.reportValidity();
            return;
        }
        ocultarClave();
        const textoBoton = enviar.textContent;
        enviar.disabled = true;
        enviar.textContent = modo === 'login' ? 'Entrando…' : 'Creando cuenta…';
        try {
            const respuesta = await fetch(API_CUENTA, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ accion: modo, nombre: nombre.value, telefono: telefono.value })
            });
            const datos = await respuesta.json().catch(() => ({}));
            if (!respuesta.ok) {
                estado.textContent = datos.error || 'No se pudo completar. Intenta de nuevo.';
                const campo = datos.campo === 'nombre' ? nombre : datos.campo === 'telefono' ? telefono : null;
                campo?.focus();
                return;
            }
            guardarNombre(datos.usuario);
            telefono.value = '';
            // Recargamos la misma página para no perder lo que estaba haciendo (por ejemplo, pagar el carrito).
            window.location.reload();
        } catch {
            estado.textContent = 'No hay conexión con el servidor. Revisa que XAMPP esté encendido.';
        } finally {
            enviar.disabled = false;
            enviar.textContent = textoBoton;
        }
    });
})();

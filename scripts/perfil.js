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
    actualizarPerfil();
    window.addEventListener('pageshow', actualizarPerfil);
    cerrarSesion?.addEventListener('click', () => {
        sessionStorage.removeItem('freshcuttz-nombre');
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
    tengoCuenta.addEventListener('click', () => {
        descripcion.textContent = 'Ingresa el nombre y el teléfono de tu cuenta para continuar.';
        tengoCuenta.hidden = true;
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
        descripcion.textContent = descripcionInicial;
        tengoCuenta.hidden = false;
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
    formulario.addEventListener('submit', event => {
        event.preventDefault();
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
        try {
            // Guardamos solo el nombre para el saludo, no credenciales ni una sesión autenticada.
            sessionStorage.setItem('freshcuttz-nombre', nombre.value);
            telefono.value = '';
            window.location.href = 'index.php';
        } catch {
            estado.textContent = 'Permite el almacenamiento de este sitio en tu navegador para continuar.';
        }
    });
})();

(() => {
    const formulario = document.getElementById('formulario-contacto');
    if (!formulario) return;
    const nombre = document.getElementById('contacto-nombre');
    const telefono = document.getElementById('contacto-telefono');
    const servicio = document.getElementById('contacto-servicio');
    const servicios = new Map([
        ['fade', 'Corte Fade Navaja'],
        ['clasico', 'Clásico Tradicional'],
        ['barba', 'Diseño de Barba']
    ]);

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
        } else if (campo === telefono) {
            if (!valor) mensaje = 'Escribe tu celular.';
            else if (!/^[0-9]+$/.test(valor)) mensaje = 'Solo se permiten números del 0 al 9, sin letras, espacios ni signos.';
            else if (valor.length !== 10) mensaje = 'El celular debe tener exactamente 10 números, sin lada internacional.';
        } else if (!servicios.has(valor)) mensaje = 'Elige uno de los servicios disponibles.';
        campo.setCustomValidity(mensaje);
        campo.setAttribute('aria-invalid', String(Boolean(mensaje)));
        document.getElementById(`contacto-error-${campo.name}`).textContent = mensaje;
        return !mensaje;
    }

    formulario.noValidate = true;
    [nombre, telefono, servicio].forEach(campo => {
        campo.addEventListener('input', () => validar(campo));
        campo.addEventListener('change', () => validar(campo));
        campo.addEventListener('blur', () => {
            if (campo === nombre) campo.value = campo.value.normalize('NFC').trim();
            validar(campo);
        });
    });
    formulario.addEventListener('submit', event => {
        event.preventDefault();
        nombre.value = nombre.value.normalize('NFC').trim();
        const invalidos = [nombre, telefono, servicio].filter(campo => !validar(campo));
        if (invalidos.length) {
            invalidos[0].focus();
            invalidos[0].reportValidity();
            return;
        }
        const mensaje = `Hola, soy ${nombre.value}. Mi celular es ${telefono.value}. Quisiera consultar disponibilidad para ${servicios.get(servicio.value)}.`;
        window.location.href = `https://wa.me/526143633515?text=${encodeURIComponent(mensaje)}`;
    });
})();

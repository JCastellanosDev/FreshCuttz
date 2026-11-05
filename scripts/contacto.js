(() => {
    'use strict';

    /* ---------- Estado "Abierto ahora / Cerrado" (hora de Chihuahua) ---------- */
    const estado = document.getElementById('contacto-estado');
    const ICONO_ESTADO = { exito: 'reloj', error: 'reloj' };

    function horaChihuahua() {
        const partes = new Intl.DateTimeFormat('en-US', {
            timeZone: 'America/Chihuahua',
            weekday: 'short',
            hour: 'numeric',
            hourCycle: 'h23'
        }).formatToParts(new Date());
        const valor = tipo => partes.find(p => p.type === tipo)?.value;
        return { dia: valor('weekday'), hora: Number(valor('hour')) };
    }

    function pintarEstado() {
        if (!estado) return;
        let abierto;
        try {
            const { dia, hora } = horaChihuahua();
            abierto = dia !== 'Sun' && hora >= 10 && hora < 20;
        } catch {
            return; // Sin soporte de zonas horarias: no mostramos la insignia.
        }
        const tipo = abierto ? 'exito' : 'error';
        estado.className = `insignia insignia-${tipo} dato-estado`;
        estado.replaceChildren(
            window.ui ? window.ui.icono(ICONO_ESTADO[tipo], 'icono-sm') : '',
            abierto ? 'Abierto ahora' : 'Cerrado ahora'
        );
        estado.hidden = false;
    }

    pintarEstado();
    setInterval(pintarEstado, 60000);

    /* ---------- Formulario "Agenda tu corte" ---------- */
    const formulario = document.getElementById('formulario-contacto');
    if (!formulario) return;
    const nombre = document.getElementById('contacto-nombre');
    const telefono = document.getElementById('contacto-telefono');
    const servicio = document.getElementById('contacto-servicio');
    const boton = formulario.querySelector('button[type="submit"]');
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
                mensaje = 'Usa solo letras, acentos, ñ y espacios; sin números ni símbolos.';
            } else if (valor.trim().length < 2 || valor.trim().length > 100) {
                mensaje = 'El nombre debe tener entre 2 y 100 caracteres.';
            }
        } else if (campo === telefono) {
            if (!valor) mensaje = 'Escribe tu celular.';
            else if (!/^[0-9]+$/.test(valor)) mensaje = 'Usa solo números del 0 al 9, sin letras, espacios ni signos.';
            else if (valor.length !== 10) mensaje = 'El celular debe tener exactamente 10 números, sin lada internacional.';
        } else if (!servicios.has(valor)) mensaje = 'Elige uno de los servicios disponibles.';

        campo.setCustomValidity(mensaje);
        if (mensaje) campo.setAttribute('aria-invalid', 'true');
        else campo.removeAttribute('aria-invalid');

        const error = document.getElementById(`contacto-error-${campo.name}`);
        error.replaceChildren();
        if (mensaje) {
            if (window.ui) error.append(window.ui.icono('error', 'icono-sm'));
            error.append(mensaje);
        }
        return !mensaje;
    }

    formulario.noValidate = true;
    [nombre, telefono, servicio].forEach(campo => {
        // Solo revalidamos al escribir si el campo ya mostró un error, para no regañar antes de tiempo.
        campo.addEventListener('input', () => {
            if (campo.hasAttribute('aria-invalid')) validar(campo);
        });
        campo.addEventListener('change', () => validar(campo));
        campo.addEventListener('blur', () => {
            if (campo === nombre) campo.value = campo.value.normalize('NFC').trim();
            if (campo.value || campo.hasAttribute('aria-invalid')) validar(campo);
        });
    });

    formulario.addEventListener('submit', event => {
        event.preventDefault();
        nombre.value = nombre.value.normalize('NFC').trim();
        const invalidos = [nombre, telefono, servicio].filter(campo => !validar(campo));
        if (invalidos.length) {
            invalidos[0].focus();
            window.ui?.toast('Revisa los campos marcados.', 'error');
            return;
        }
        const mensaje = `Hola, soy ${nombre.value}. Mi celular es ${telefono.value}. Quisiera consultar disponibilidad para ${servicios.get(servicio.value)}.`;
        window.ui?.cargando(boton, true);
        window.ui?.toast('Abriendo WhatsApp con tu mensaje…', 'exito');
        setTimeout(() => {
            window.location.href = `https://wa.me/526143633515?text=${encodeURIComponent(mensaje)}`;
        }, 450);
    });

    // Al volver con el botón "atrás" (bfcache), el botón no debe quedar cargando.
    window.addEventListener('pageshow', () => window.ui?.cargando(boton, false));
})();

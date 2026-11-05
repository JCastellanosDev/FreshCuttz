// Fresh Cuttz · utilidades de interfaz compartidas (sitio y panel).
// Expone window.ui: icono(), toast(), escalonar(), cerrarDialogo(), cargando().
(function () {
    'use strict';

    const SPRITE = new URL('../image/iconos.svg', document.currentScript?.src || location.href).pathname;
    const NS = 'http://www.w3.org/2000/svg';

    /** Devuelve un <svg class="icono"> que usa el símbolo del sprite. */
    function icono(nombre, clases = '') {
        const svg = document.createElementNS(NS, 'svg');
        svg.setAttribute('class', `icono ${clases}`.trim());
        svg.setAttribute('aria-hidden', 'true');
        svg.setAttribute('focusable', 'false');
        const uso = document.createElementNS(NS, 'use');
        uso.setAttribute('href', `${SPRITE}#${nombre}`);
        svg.append(uso);
        return svg;
    }

    let region = null;
    function regionToasts() {
        if (!region) {
            region = document.createElement('div');
            region.className = 'toasts';
            region.setAttribute('role', 'status');
            region.setAttribute('aria-live', 'polite');
        }
        // Dentro de un diálogo modal abierto, el toast debe vivir en la capa superior.
        const modal = [...document.querySelectorAll('dialog[open]')].pop();
        const destino = modal || document.body;
        if (region.parentElement !== destino) destino.append(region);
        return region;
    }

    const ICONOS_TOAST = { exito: 'listo', error: 'error', advertencia: 'alerta', info: 'info' };

    /** Notificación breve. tipo: 'exito' | 'error' | 'advertencia' | 'info'. */
    function toast(mensaje, tipo = 'info', duracion = 3200) {
        const contenedor = regionToasts();
        const elemento = document.createElement('div');
        elemento.className = `toast toast-${tipo}`;
        const texto = document.createElement('span');
        texto.textContent = mensaje;
        elemento.append(icono(ICONOS_TOAST[tipo] || 'info'), texto);
        contenedor.append(elemento);

        setTimeout(() => {
            elemento.classList.add('esta-cerrando');
            elemento.addEventListener('animationend', () => elemento.remove(), { once: true });
            setTimeout(() => elemento.remove(), 400);
        }, duracion);
        return elemento;
    }

    /** Asigna --i a cada hijo para la aparición escalonada (.escalonado). */
    function escalonar(contenedor) {
        if (!contenedor) return;
        [...contenedor.children].forEach((hijo, i) => hijo.style.setProperty('--i', Math.min(i, 12)));
        contenedor.classList.remove('escalonado');
        void contenedor.offsetWidth; // reinicia la animación
        contenedor.classList.add('escalonado');
    }

    const reducirMovimiento = window.matchMedia('(prefers-reduced-motion: reduce)');

    /** Cierra un <dialog> con su animación de salida (.esta-cerrando). */
    function cerrarDialogo(dialogo) {
        if (!dialogo?.open) return Promise.resolve();
        if (reducirMovimiento.matches) {
            dialogo.close();
            return Promise.resolve();
        }
        return new Promise(resolver => {
            let terminado = false;
            const terminar = () => {
                if (terminado) return;
                terminado = true;
                dialogo.classList.remove('esta-cerrando');
                dialogo.close();
                resolver();
            };
            dialogo.classList.add('esta-cerrando');
            dialogo.addEventListener('animationend', terminar, { once: true });
            setTimeout(terminar, 320);
        });
    }

    /** Activa o quita el estado de carga de un botón .btn. */
    function cargando(boton, activo) {
        if (!boton) return;
        boton.classList.toggle('esta-cargando', activo);
        boton.disabled = activo;
        if (activo) boton.setAttribute('aria-busy', 'true');
        else boton.removeAttribute('aria-busy');
    }

    window.ui = { icono, toast, escalonar, cerrarDialogo, cargando, sprite: SPRITE };
})();

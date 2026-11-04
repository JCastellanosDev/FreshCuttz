(() => {
    const boton = document.querySelector('.boton-carrito');
    const panel = document.getElementById('panel-carrito');
    if (!boton || !panel) return;

    const contenido = panel.querySelector('.carrito-contenido');
    const lista = document.getElementById('carrito-productos');
    const total = document.getElementById('carrito-total');
    const contador = document.getElementById('carrito-contador');
    const estado = document.getElementById('carrito-estado');
    const pagar = document.getElementById('carrito-pagar');
    const modalPago = document.getElementById('modal-pago');
    const formularioPago = document.getElementById('formulario-pago');
    const totalPago = document.getElementById('pago-total');
    const resumenPago = document.getElementById('pago-resumen');
    const formatoPrecio = new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' });
    const claveAlmacenamiento = 'freshcuttz-carrito-v1';
    let articulos = [];
    const movimientoReducido = window.matchMedia('(prefers-reduced-motion: reduce)');
    let animacion;
    let cerrando = false;
    let contextoPago = null;

    const deslizar = (desde, hasta) => contenido.animate(
        [{ transform: desde }, { transform: hasta }],
        { duration: movimientoReducido.matches ? 0 : 280, easing: 'ease-out', fill: 'forwards' }
    );

    const abrir = () => {
        if (panel.open) return;
        panel.showModal();
        boton.setAttribute('aria-expanded', 'true');
        document.documentElement.classList.add('carrito-abierto');
        animacion = deslizar('translateX(100%)', 'translateX(0)');
    };
    boton.addEventListener('click', abrir);

    const normalizarProducto = (producto) => {
        if (!producto || !Number.isSafeInteger(producto.id) || producto.id < 1 ||
            typeof producto.nombre !== 'string' || !producto.nombre.trim() ||
            !Number.isFinite(producto.precio) || producto.precio < 0 ||
            !Number.isSafeInteger(Math.round(producto.precio * 100))) return null;
        return {
            id: producto.id,
            nombre: producto.nombre,
            precio: producto.precio,
            foto: typeof producto.foto === 'string' && /^\.\/image\/productos\/[^/]+\.(png|webp|jpe?g)$/i.test(producto.foto)
                ? producto.foto : '',
            cantidadDisponible: Number.isFinite(producto.cantidadDisponible)
                ? Math.max(0, Math.floor(producto.cantidadDisponible)) : null
        };
    };

    try {
        const guardados = JSON.parse(localStorage.getItem(claveAlmacenamiento) || '[]');
        if (Array.isArray(guardados)) {
            guardados.forEach((guardado) => {
                const producto = normalizarProducto(guardado);
                if (!producto || !Number.isSafeInteger(guardado.cantidad) || guardado.cantidad < 1 ||
                    articulos.some(articulo => articulo.id === producto.id)) return;
                const cantidad = Math.min(guardado.cantidad, producto.cantidadDisponible ?? guardado.cantidad);
                if (cantidad > 0) articulos.push({ ...producto, cantidad });
            });
        }
    } catch {
    }

    const actualizar = () => {
        lista.replaceChildren();
        let unidades = 0;
        let totalCentavos = 0;
        articulos.forEach((articulo) => {
            unidades += articulo.cantidad;
            const subtotalCentavos = Math.round(articulo.precio * 100) * articulo.cantidad;
            totalCentavos += subtotalCentavos;
            const fila = document.createElement('article');
            fila.className = 'carrito-articulo';
            const foto = document.createElement('img');
            foto.className = 'carrito-foto';
            foto.alt = articulo.nombre;
            if (articulo.foto) foto.src = articulo.foto;
            else foto.hidden = true;
            const detalle = document.createElement('div');
            detalle.className = 'carrito-detalle';
            const nombre = document.createElement('h3');
            nombre.textContent = articulo.nombre;
            const cantidad = document.createElement('p');
            cantidad.textContent = `${articulo.cantidad} × ${formatoPrecio.format(articulo.precio)}`;
            const subtotal = document.createElement('strong');
            subtotal.textContent = formatoPrecio.format(subtotalCentavos / 100);
            const controles = document.createElement('div');
            controles.className = 'carrito-cantidad';
            const unidadesProducto = document.createElement('span');
            unidadesProducto.textContent = articulo.cantidad;
            unidadesProducto.setAttribute('aria-label', `Cantidad: ${articulo.cantidad}`);
            const disminuir = document.createElement('button');
            disminuir.type = 'button';
            disminuir.textContent = '−';
            disminuir.disabled = articulo.cantidad <= 1;
            disminuir.setAttribute('aria-label', `Disminuir cantidad de ${articulo.nombre}`);
            disminuir.id = `carrito-disminuir-${articulo.id}`;
            const aumentar = document.createElement('button');
            aumentar.type = 'button';
            aumentar.textContent = '+';
            aumentar.disabled = articulo.cantidadDisponible !== null && articulo.cantidad >= articulo.cantidadDisponible;
            aumentar.setAttribute('aria-label', `Aumentar cantidad de ${articulo.nombre}`);
            aumentar.id = `carrito-aumentar-${articulo.id}`;
            const restaurarFoco = (id) => {
                const control = document.getElementById(id);
                if (control && !control.disabled) control.focus();
                else document.getElementById(`carrito-quitar-${articulo.id}`)?.focus();
            };
            aumentar.addEventListener('click', () => {
                window.carritoCompras.agregar(articulo);
                restaurarFoco(aumentar.id);
            });
            disminuir.addEventListener('click', () => {
                if (articulo.cantidad <= 1) return;
                articulo.cantidad -= 1;
                actualizar();
                estado.textContent = `Cantidad de ${articulo.nombre}: ${articulo.cantidad}. Total: ${total.textContent}.`;
                restaurarFoco(disminuir.id);
            });
            controles.append(disminuir, unidadesProducto, aumentar);
            const quitar = document.createElement('button');
            quitar.id = `carrito-quitar-${articulo.id}`;
            quitar.type = 'button';
            quitar.className = 'carrito-quitar';
            quitar.textContent = 'Quitar';
            quitar.setAttribute('aria-label', `Quitar ${articulo.nombre} del carrito`);
            quitar.addEventListener('click', () => {
                articulos = articulos.filter(producto => producto.id !== articulo.id);
                actualizar();
                estado.textContent = `${articulo.nombre} se quitó del carrito.`;
                (lista.querySelector('button') || panel.querySelector('[data-cerrar-carrito]')).focus();
            });
            detalle.append(nombre, cantidad, controles, subtotal, quitar);
            fila.append(foto, detalle);
            lista.append(fila);
        });
        if (!articulos.length) {
            const vacio = document.createElement('p');
            vacio.className = 'carrito-vacio';
            vacio.textContent = 'Tu carrito está vacío.';
            lista.append(vacio);
        }
        total.textContent = formatoPrecio.format(totalCentavos / 100);
        contador.textContent = unidades;
        contador.hidden = unidades === 0;
        pagar.disabled = unidades === 0;
        boton.setAttribute('aria-label', `Abrir carrito de compras, ${unidades} ${unidades === 1 ? 'producto' : 'productos'}`);
        try {
            localStorage.setItem(claveAlmacenamiento, JSON.stringify(articulos));
        } catch {
        
        }
        document.dispatchEvent(new CustomEvent('carrito:actualizado'));
    };

    window.carritoCompras = {
        cantidad: id => articulos.find(articulo => articulo.id === id)?.cantidad ?? 0,
        actualizarCatalogo: (catalogo) => {
            articulos = articulos.flatMap((articulo) => {
                const producto = normalizarProducto(catalogo.find(producto => producto.id === articulo.id));
                if (!producto) return [];
                const cantidad = Math.min(articulo.cantidad, producto.cantidadDisponible ?? articulo.cantidad);
                return cantidad > 0 ? [{ ...producto, cantidad }] : [];
            });
            actualizar();
        },
        agregar: (datos) => {
            const producto = normalizarProducto(datos);
            if (!producto) return;
            const existente = articulos.find(articulo => articulo.id === producto.id);
            const cantidad = (existente?.cantidad ?? 0) + 1;
            if (producto.cantidadDisponible !== null && cantidad > producto.cantidadDisponible) {
                estado.textContent = `Ya agregaste todas las unidades disponibles de ${producto.nombre}.`;
                abrir();
                return;
            }
            if (existente) Object.assign(existente, producto, { cantidad });
            else articulos.push({ ...producto, cantidad });
            actualizar();
            estado.textContent = `${producto.nombre} agregado. Total: ${total.textContent}.`;
            abrir();
        },
        abrirPago: (datos) => {
            if (!datos || !Number.isFinite(datos.total) || datos.total < 0) return;
            contextoPago = datos;
            totalPago.textContent = formatoPrecio.format(datos.total);
            resumenPago.replaceChildren();
            Object.entries(datos.resumen || {}).forEach(([etiqueta, valor]) => {
                const linea = document.createElement('p');
                const titulo = document.createElement('strong');
                titulo.textContent = `${etiqueta}: `;
                linea.append(titulo, document.createTextNode(String(valor)));
                resumenPago.append(linea);
            });
            resumenPago.hidden = false;
            formularioPago.reset();
            formularioPago.querySelectorAll('details').forEach(detalle => { detalle.open = false; });
            modalPago.showModal();
            formularioPago.querySelector('input')?.focus();
        }
    };
    actualizar();

    pagar.addEventListener('click', () => {
        if (!articulos.length) return;
        contextoPago = null;
        resumenPago.hidden = true;
        totalPago.textContent = total.textContent;
        formularioPago.reset();
        modalPago.showModal();
        formularioPago.querySelector('input')?.focus();
    });
    formularioPago.querySelectorAll('input[name="metodo-pago"]').forEach((opcion) => {
        opcion.addEventListener('change', () => {
            formularioPago.querySelectorAll('details').forEach((detalle) => {
                detalle.open = opcion.value === 'transferencia' && opcion.checked;
            });
        });
    });
    document.getElementById('cerrar-pago').addEventListener('click', () => modalPago.close());
    document.getElementById('volver-carrito').addEventListener('click', () => modalPago.close());
    const estadoPago = document.getElementById('pago-estado');
    const confirmacionPago = document.getElementById('pago-confirmacion');
    const textoConfirmacion = document.getElementById('pago-confirmacion-texto');
    const botonConfirmar = formularioPago.querySelector('button[type="submit"]');
    const instrucciones = {
        efectivo: 'Paga en la barbería.',
        transferencia: 'Envía tu comprobante de transferencia a la barbería indicando tu número de pedido.',
        'mercado-pago': 'Te contactaremos para enviarte el enlace de Mercado Pago.'
    };

    modalPago.addEventListener('close', () => {
        formularioPago.hidden = false;
        confirmacionPago.hidden = true;
        estadoPago.textContent = '';
    });
    document.getElementById('pago-listo').addEventListener('click', () => {
        modalPago.close();
        window.location.href = 'index.php';
    });

    formularioPago.addEventListener('submit', async (evento) => {
        evento.preventDefault();
        if (botonConfirmar.disabled) return;
        if ((!articulos.length && !contextoPago) || !formularioPago.reportValidity()) return;
        const opcion = formularioPago.querySelector('input[name="metodo-pago"]:checked');
        if (!opcion) return;
        const metodos = { efectivo: 'Efectivo', transferencia: 'Transferencia', 'mercado-pago': 'Mercado Pago' };
        const metodo = metodos[opcion.value];
        const esCita = Boolean(contextoPago?.cita);
        const cuerpo = esCita
            ? { tipo: 'cita', metodo: opcion.value, ...contextoPago.cita }
            : { tipo: 'productos', metodo: opcion.value, articulos: articulos.map(({ id, cantidad }) => ({ id, cantidad })) };

        estadoPago.textContent = '';
        botonConfirmar.disabled = true;
        botonConfirmar.textContent = 'Registrando…';
        try {
            const respuesta = await fetch('api/pedido.php', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(cuerpo)
            });
            const datos = await respuesta.json().catch(() => ({}));
            if (!respuesta.ok) {
                estadoPago.textContent = datos.error || 'No se pudo registrar tu pedido. Intenta de nuevo.';
                if (datos.requiereLogin) {
                    // El carrito se conserva; al iniciar sesión la página se recarga y puede pagar.
                    modalPago.close();
                    // Cerramos el carrito para que el login no quede detrás de otro diálogo modal.
                    animacion?.cancel();
                    panel.close();
                    estado.textContent = datos.error;
                    document.getElementById('abrir-perfil')?.click();
                    document.getElementById('login-estado').textContent = datos.error;
                }
                return;
            }
            if (esCita) {
                window.dispatchEvent(new CustomEvent('cita:pago-confirmado', { detail: { ...contextoPago.resumen, metodo } }));
            } else {
                articulos = [];
                actualizar();
            }
            contextoPago = null;
            textoConfirmacion.textContent = `${esCita ? 'Tu cita quedó reservada' : 'Tu pedido quedó registrado'} con el folio #${datos.pedido.id}. ` +
                `Total: ${formatoPrecio.format(datos.pedido.total)} · ${datos.pedido.metodo}. ${instrucciones[opcion.value]}`;
            formularioPago.hidden = true;
            confirmacionPago.hidden = false;
            document.getElementById('pago-listo').focus();
        } catch {
            estadoPago.textContent = 'No hay conexión con el servidor. Revisa que XAMPP esté encendido.';
        } finally {
            botonConfirmar.disabled = false;
            botonConfirmar.textContent = 'Confirmar pago';
        }
    });

    const cerrar = async () => {
        if (!panel.open || cerrando) return;
        cerrando = true;
        const posicion = getComputedStyle(contenido).transform;
        animacion?.cancel();
        animacion = deslizar(posicion, 'translateX(100%)');
        try {
            await animacion.finished;
        } catch {
        
        }
        panel.close();
    };

    panel.querySelectorAll('[data-cerrar-carrito]').forEach((control) => {
        control.addEventListener('click', cerrar);
    });
    panel.addEventListener('click', (evento) => {
        if (evento.target === panel) cerrar();
    });
    panel.addEventListener('cancel', (evento) => {
        evento.preventDefault();
        cerrar();
    });
    panel.addEventListener('close', () => {
        animacion?.cancel();
        cerrando = false;
        boton.setAttribute('aria-expanded', 'false');
        document.documentElement.classList.remove('carrito-abierto');
        boton.focus();
    });
})();

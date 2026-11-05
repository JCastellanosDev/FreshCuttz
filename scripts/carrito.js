(() => {
    const boton = document.querySelector('.boton-carrito');
    const panel = document.getElementById('panel-carrito');
    if (!boton || !panel) return;

    const lista = document.getElementById('carrito-productos');
    const total = document.getElementById('carrito-total');
    const contador = document.getElementById('carrito-contador');
    const estado = document.getElementById('carrito-estado');
    const pagar = document.getElementById('carrito-pagar');
    const modalPago = document.getElementById('modal-pago');
    const formularioPago = document.getElementById('formulario-pago');
    const totalPago = document.getElementById('pago-total');
    const resumenPago = document.getElementById('pago-resumen');
    const volverCarrito = document.getElementById('volver-carrito');
    const formatoPrecio = new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' });
    const claveAlmacenamiento = 'freshcuttz-carrito-v1';
    let articulos = [];
    let contextoPago = null;

    // Utilidades de ui.js con respaldo por si no cargara.
    const cerrarDialogo = (dialogo) => window.ui?.cerrarDialogo
        ? window.ui.cerrarDialogo(dialogo)
        : (dialogo.open && dialogo.close(), Promise.resolve());
    const avisar = (mensaje, tipo) => window.ui?.toast(mensaje, tipo);
    const icono = (nombre, clases) => window.ui?.icono(nombre, clases) ?? document.createTextNode('');
    const cargando = (control, activo) => {
        if (window.ui?.cargando) window.ui.cargando(control, activo);
        else control.disabled = activo;
    };

    const abrir = () => {
        if (panel.open) return;
        panel.showModal();
        boton.setAttribute('aria-expanded', 'true');
        document.documentElement.classList.add('carrito-abierto');
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
        // Sin almacenamiento el carrito funciona solo durante esta visita.
    }

    const botonIcono = (nombreIcono, etiqueta, id, clases = '') => {
        const control = document.createElement('button');
        control.type = 'button';
        control.className = `btn btn-fantasma btn-icono btn-sm ${clases}`.trim();
        control.id = id;
        control.setAttribute('aria-label', etiqueta);
        control.append(icono(nombreIcono, 'icono-sm'));
        return control;
    };

    const estadoVacio = () => {
        const vacio = document.createElement('div');
        vacio.className = 'vacio carrito-vacio';
        const titulo = document.createElement('strong');
        titulo.textContent = 'Tu carrito está vacío';
        const texto = document.createElement('p');
        texto.textContent = 'Agrega productos para el cuidado de tu cabello y barba, y págalos al recogerlos en la barbería.';
        const enlace = document.createElement('a');
        enlace.className = 'btn btn-contorno';
        enlace.href = 'productos.php';
        enlace.append('Ver productos', icono('flecha-derecha', 'icono-sm'));
        // Si ya estamos en productos, basta con cerrar el panel.
        enlace.addEventListener('click', (evento) => {
            if (/\/productos\.php$/.test(location.pathname)) {
                evento.preventDefault();
                cerrar();
            }
        });
        vacio.append(icono('carrito'), titulo, texto, enlace);
        return vacio;
    };

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

            let foto;
            if (articulo.foto) {
                foto = document.createElement('img');
                foto.className = 'carrito-foto';
                foto.alt = articulo.nombre;
                foto.width = 72;
                foto.height = 72;
                foto.src = articulo.foto;
            } else {
                foto = document.createElement('span');
                foto.className = 'carrito-foto-vacia';
                foto.append(icono('productos'));
            }

            const detalle = document.createElement('div');
            detalle.className = 'carrito-detalle';
            const nombre = document.createElement('h3');
            nombre.textContent = articulo.nombre;
            const precio = document.createElement('p');
            precio.className = 'carrito-precio';
            precio.textContent = `${formatoPrecio.format(articulo.precio)} c/u`;

            const quitar = botonIcono('borrar', `Quitar ${articulo.nombre} del carrito`,
                `carrito-quitar-${articulo.id}`, 'carrito-quitar');

            const controles = document.createElement('div');
            controles.className = 'carrito-cantidad';
            const disminuir = botonIcono('menos', `Disminuir cantidad de ${articulo.nombre}`,
                `carrito-disminuir-${articulo.id}`);
            disminuir.disabled = articulo.cantidad <= 1;
            const unidadesProducto = document.createElement('span');
            unidadesProducto.textContent = articulo.cantidad;
            unidadesProducto.setAttribute('aria-label', `Cantidad: ${articulo.cantidad}`);
            const aumentar = botonIcono('mas', `Aumentar cantidad de ${articulo.nombre}`,
                `carrito-aumentar-${articulo.id}`);
            aumentar.disabled = articulo.cantidadDisponible !== null && articulo.cantidad >= articulo.cantidadDisponible;
            controles.append(disminuir, unidadesProducto, aumentar);

            const subtotal = document.createElement('strong');
            subtotal.className = 'carrito-subtotal';
            subtotal.textContent = formatoPrecio.format(subtotalCentavos / 100);

            const inferior = document.createElement('div');
            inferior.className = 'carrito-fila-inferior';
            inferior.append(controles, subtotal);

            const restaurarFoco = (id) => {
                const control = document.getElementById(id);
                if (control && !control.disabled) control.focus();
                else document.getElementById(`carrito-quitar-${articulo.id}`)?.focus();
            };
            aumentar.addEventListener('click', () => {
                window.carritoCompras.agregar(articulo, { silencioso: true });
                restaurarFoco(aumentar.id);
            });
            disminuir.addEventListener('click', () => {
                if (articulo.cantidad <= 1) return;
                articulo.cantidad -= 1;
                actualizar();
                estado.textContent = `Cantidad de ${articulo.nombre}: ${articulo.cantidad}. Total: ${total.textContent}.`;
                restaurarFoco(disminuir.id);
            });
            quitar.addEventListener('click', () => {
                articulos = articulos.filter(producto => producto.id !== articulo.id);
                actualizar();
                estado.textContent = `${articulo.nombre} se quitó del carrito.`;
                (lista.querySelector('button, a') || panel.querySelector('[data-cerrar-carrito]')).focus();
            });

            detalle.append(nombre, quitar, precio, inferior);
            fila.append(foto, detalle);
            lista.append(fila);
        });
        if (!articulos.length) lista.append(estadoVacio());

        total.textContent = formatoPrecio.format(totalCentavos / 100);
        contador.textContent = unidades;
        contador.hidden = unidades === 0;
        pagar.disabled = unidades === 0;
        boton.setAttribute('aria-label', `Abrir carrito de compras, ${unidades} ${unidades === 1 ? 'producto' : 'productos'}`);
        try {
            localStorage.setItem(claveAlmacenamiento, JSON.stringify(articulos));
        } catch {
            // Sin almacenamiento el carrito funciona solo durante esta visita.
        }
        document.dispatchEvent(new CustomEvent('carrito:actualizado'));
    };

    const abrirModalPago = () => {
        formularioPago.reset();
        formularioPago.querySelectorAll('details').forEach(detalle => { detalle.open = false; });
        modalPago.showModal();
        formularioPago.querySelector('input')?.focus();
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
        agregar: (datos, opciones = {}) => {
            const producto = normalizarProducto(datos);
            if (!producto) return;
            const existente = articulos.find(articulo => articulo.id === producto.id);
            const cantidad = (existente?.cantidad ?? 0) + 1;
            if (producto.cantidadDisponible !== null && cantidad > producto.cantidadDisponible) {
                estado.textContent = `Ya agregaste todas las unidades disponibles de ${producto.nombre}.`;
                abrir();
                avisar(`No hay más unidades de ${producto.nombre}.`, 'advertencia');
                return;
            }
            if (existente) Object.assign(existente, producto, { cantidad });
            else articulos.push({ ...producto, cantidad });
            actualizar();
            estado.textContent = `${producto.nombre} agregado. Total: ${total.textContent}.`;
            const yaAbierto = panel.open;
            abrir();
            if (opciones.silencioso) return;
            // El toast se crea con el panel abierto (capa superior) y al terminar su entrada,
            // para que la animación del panel no lo desplace.
            const mostrarAviso = () => avisar(`Agregado: ${producto.nombre}`, 'exito');
            if (yaAbierto) mostrarAviso();
            else setTimeout(mostrarAviso, 320);
        },
        abrirPago: (datos) => {
            if (!datos || !Number.isFinite(datos.total) || datos.total < 0) return;
            contextoPago = datos;
            totalPago.textContent = formatoPrecio.format(datos.total);
            resumenPago.replaceChildren();
            Object.entries(datos.resumen || {}).forEach(([etiqueta, valor]) => {
                const linea = document.createElement('p');
                const titulo = document.createElement('span');
                titulo.textContent = etiqueta;
                const dato = document.createElement('strong');
                dato.textContent = String(valor);
                linea.append(titulo, dato);
                resumenPago.append(linea);
            });
            resumenPago.hidden = false;
            // Un pago de cita no viene del carrito: no tiene sentido "volver al carrito".
            volverCarrito.hidden = true;
            abrirModalPago();
        }
    };
    actualizar();

    pagar.addEventListener('click', () => {
        if (!articulos.length) return;
        contextoPago = null;
        resumenPago.hidden = true;
        volverCarrito.hidden = false;
        totalPago.textContent = total.textContent;
        abrirModalPago();
    });
    formularioPago.querySelectorAll('input[name="metodo-pago"]').forEach((opcion) => {
        opcion.addEventListener('change', () => {
            formularioPago.querySelectorAll('details').forEach((detalle) => {
                detalle.open = opcion.value === 'transferencia' && opcion.checked;
            });
        });
    });
    document.getElementById('cerrar-pago').addEventListener('click', () => cerrarDialogo(modalPago));
    volverCarrito.addEventListener('click', () => cerrarDialogo(modalPago));
    modalPago.addEventListener('cancel', (evento) => {
        evento.preventDefault();
        cerrarDialogo(modalPago);
    });
    modalPago.addEventListener('click', (evento) => {
        if (evento.target !== modalPago) return;
        const borde = modalPago.getBoundingClientRect();
        if (evento.clientX < borde.left || evento.clientX > borde.right ||
            evento.clientY < borde.top || evento.clientY > borde.bottom) cerrarDialogo(modalPago);
    });

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
    document.getElementById('pago-listo').addEventListener('click', async () => {
        await cerrarDialogo(modalPago);
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
        cargando(botonConfirmar, true);
        try {
            const respuesta = await fetch('api/pedido.php', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(cuerpo)
            });
            const datos = await respuesta.json().catch(() => ({}));
            if (!respuesta.ok) {
                const mensaje = datos.error || 'No se pudo registrar tu pedido. Intenta de nuevo.';
                if (datos.requiereLogin) {
                    // El carrito se conserva; al iniciar sesión la página se recarga y puede pagar.
                    // Cerramos el pago y el carrito para que el login no quede detrás de otro diálogo modal.
                    await cerrarDialogo(modalPago);
                    await cerrarDialogo(panel);
                    estado.textContent = mensaje;
                    document.getElementById('abrir-perfil')?.click();
                    const estadoLogin = document.getElementById('login-estado');
                    if (estadoLogin) estadoLogin.textContent = mensaje;
                } else {
                    estadoPago.textContent = mensaje;
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
            const mensaje = 'No hay conexión con el servidor. Revisa que XAMPP esté encendido.';
            estadoPago.textContent = mensaje;
            avisar('Sin conexión con el servidor.', 'error');
        } finally {
            cargando(botonConfirmar, false);
        }
    });

    let cerrando = false;
    const cerrar = async () => {
        if (!panel.open || cerrando) return;
        cerrando = true;
        await cerrarDialogo(panel);
        cerrando = false;
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
        cerrando = false;
        boton.setAttribute('aria-expanded', 'false');
        document.documentElement.classList.remove('carrito-abierto');
        boton.focus();
    });
})();

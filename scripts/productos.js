// Los productos vienen de la base de datos (productos.php los imprime en #catalogo-productos).
// Se administran desde el panel: /admin
var productos = [];
try {
    productos = JSON.parse(document.getElementById('catalogo-productos')?.textContent || '[]');
} catch {
    productos = [];
}

(function () {
    const contenedorProductos = document.getElementById('tarjetas-productos');
    if (!contenedorProductos) return;

    const icono = (nombre, clases) => window.ui?.icono(nombre, clases) ?? document.createTextNode('');
    const formatoPrecio = new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' });
    const UMBRAL_POCAS = 5;

    window.carritoCompras?.actualizarCatalogo(productos);

    if (!productos.length) {
        const vacio = document.createElement('div');
        vacio.className = 'vacio tienda-vacia';
        const titulo = document.createElement('strong');
        titulo.textContent = 'Pronto tendremos productos';
        const texto = document.createElement('p');
        texto.textContent = 'Por ahora no hay productos disponibles. Mientras tanto, agenda tu corte.';
        const accion = document.createElement('a');
        accion.className = 'btn btn-contorno';
        accion.href = 'index.php#equipo';
        accion.append(icono('calendario'), document.createTextNode('Agenda tu cita'));
        vacio.append(icono('productos'), titulo, texto, accion);
        contenedorProductos.replaceChildren(vacio);
        contenedorProductos.removeAttribute('aria-busy');
        return;
    }

    function insigniaDisponibilidad(disponible) {
        if (disponible === null || disponible === undefined) return null;
        const insignia = document.createElement('span');
        insignia.className = 'insignia producto-insignia';
        if (disponible <= 0) {
            insignia.classList.add('insignia-error');
            insignia.textContent = 'Agotado';
        } else if (disponible <= UMBRAL_POCAS) {
            insignia.classList.add('insignia-advertencia');
            insignia.textContent = `Quedan ${disponible}`;
        } else {
            insignia.classList.add('insignia-exito');
            insignia.textContent = 'Disponible';
        }
        return insignia;
    }

    function crearTarjeta(producto) {
        const tarjeta = document.createElement('article');
        tarjeta.className = 'producto';

        const marco = document.createElement('div');
        marco.className = 'producto-foto';
        const foto = document.createElement('img');
        foto.src = producto.foto;
        foto.alt = producto.nombre;
        foto.loading = 'lazy';
        foto.decoding = 'async';
        marco.append(foto);
        const insignia = insigniaDisponibilidad(producto.cantidadDisponible);
        if (insignia) marco.append(insignia);

        const cuerpo = document.createElement('div');
        cuerpo.className = 'producto-cuerpo';

        const nombre = document.createElement('h3');
        nombre.className = 'producto-nombre';
        nombre.textContent = producto.nombre;

        const precio = document.createElement('p');
        precio.className = 'producto-precio t-num';
        precio.textContent = formatoPrecio.format(producto.precio);

        const agregar = document.createElement('button');
        agregar.type = 'button';
        agregar.className = 'btn btn-primario btn-bloque producto-agregar';
        const iconoBoton = document.createElement('span');
        iconoBoton.className = 'producto-agregar-icono';
        const textoBoton = document.createElement('span');
        agregar.append(iconoBoton, textoBoton);

        let confirmando = false;
        const pintarBoton = (estado) => {
            const nombreIcono = { agregar: 'carrito', listo: 'check', agotado: 'error', maximo: 'check' }[estado];
            const textos = { agregar: 'Agregar', listo: 'Agregado', agotado: 'Agotado', maximo: 'Máximo en carrito' };
            iconoBoton.replaceChildren(icono(nombreIcono, 'icono-sm'));
            textoBoton.textContent = textos[estado];
            agregar.classList.toggle('esta-confirmando', estado === 'listo');
            agregar.setAttribute('aria-label', estado === 'agregar'
                ? `Agregar ${producto.nombre} al carrito`
                : `${producto.nombre}: ${textos[estado]}`);
        };

        const actualizarBoton = () => {
            if (confirmando) return;
            const enCarrito = window.carritoCompras?.cantidad(producto.id) ?? 0;
            const limite = producto.cantidadDisponible;
            const sinExistencias = limite !== null && limite <= 0;
            const alMaximo = limite !== null && enCarrito >= limite;
            agregar.disabled = sinExistencias || alMaximo;
            pintarBoton(sinExistencias ? 'agotado' : alMaximo ? 'maximo' : 'agregar');
        };

        agregar.addEventListener('click', () => {
            const antes = window.carritoCompras?.cantidad(producto.id) ?? 0;
            window.carritoCompras?.agregar(producto);
            const despues = window.carritoCompras?.cantidad(producto.id) ?? 0;
            if (despues <= antes) return;
            // Confirmación breve en el propio botón; el toast/panel lo maneja carrito.js.
            confirmando = true;
            pintarBoton('listo');
            setTimeout(() => {
                confirmando = false;
                actualizarBoton();
            }, 1000);
        });
        document.addEventListener('carrito:actualizado', actualizarBoton);
        actualizarBoton();

        cuerpo.append(nombre, precio, agregar);
        tarjeta.append(marco, cuerpo);
        return tarjeta;
    }

    contenedorProductos.replaceChildren(...productos.map(crearTarjeta));
    contenedorProductos.removeAttribute('aria-busy');
    window.ui?.escalonar(contenedorProductos);
})();

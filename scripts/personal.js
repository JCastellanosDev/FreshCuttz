var personal =[
    {nombre:"Mario      ''El guero'' ",descri:"Especializado en pintar cabello rosa", id:1,foto:"/image/personal/mario-800.webp", fotoPequena:"/image/personal/mario-400.webp", ancho:800, alto:800},
    {nombre:"Dany       ''El matute''",descri:"Mitad barbero mitad policia", id:2,foto:"/image/personal/dany-800.webp", fotoPequena:"/image/personal/dany-400.webp", ancho:800, alto:1010},
    {nombre:"Cristian       ''La mune''",descri:"Drogo te lo corta mejor", id:3,foto:"/image/personal/criss-800.webp", fotoPequena:"/image/personal/criss-400.webp", ancho:800, alto:1186},
    {nombre:"Isziee         ''El isipisi''",descri:"Entre semana barbero y fines cevichero", id:4,foto:"/image/personal/isii-800.webp", fotoPequena:"/image/personal/isii-400.webp", ancho:800, alto:533},
    {nombre:"Mario      ''El grandpa''",descri:"El mas bueno pero el enojon", id:5,foto:"/image/personal/grampa-800.webp", fotoPequena:"/image/personal/grampa-400.webp", ancho:800, alto:533},];

var productos=[
    // Cambia null por la cantidad disponible de cada producto.
    {nombre:"cera", precio:250, cantidadDisponible:null, id:1, foto:"./image/productos/cera-sin-fondo.png"},
    {nombre:"Suavecito", precio:120, cantidadDisponible:null, id:2, foto:"./image/productos/sua-sin-fondo.png"},
    {nombre:"CeraGel", precio:300, cantidadDisponible:null, id:3, foto:"./image/productos/ceraGel-sin-fondo.png"},
    {nombre:"Mousse", precio:100, cantidadDisponible:null, id:5, foto:"./image/productos/mousse-sin-fondo.png"},
    {nombre:"Pomada", precio:70, cantidadDisponible:null, id:6, foto:"./image/productos/pomada-sin-fondo.png"},
    {nombre:"Crema", precio:80, cantidadDisponible:null, id:7, foto:"./image/productos/crema-sin-fondo.png"},
];

const contenedorProductos = document.getElementById('tarjetas-productos');

if (contenedorProductos) {
    contenedorProductos.replaceChildren();
    const formatoPrecio = new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' });
    window.carritoCompras?.actualizarCatalogo(productos);

    productos.forEach(producto => {
        const tarjeta = document.createElement('article');
        tarjeta.className = 'tarjeta-producto';

        const foto = document.createElement('img');
        foto.className = 'foto-producto';
        foto.src = producto.foto;
        foto.alt = producto.nombre;
        foto.loading = 'lazy';
        foto.decoding = 'async';

        const informacion = document.createElement('div');
        informacion.className = 'info-producto';

        const nombre = document.createElement('h2');
        nombre.textContent = producto.nombre;

        const precio = document.createElement('p');
        precio.className = 'precio-producto';
        precio.textContent = `Precio: ${formatoPrecio.format(producto.precio)}`;

        const cantidad = document.createElement('p');
        cantidad.className = 'cantidad-producto';
        cantidad.textContent = `Cantidad disponible: ${producto.cantidadDisponible ?? 'Por definir'}`;

        const agregar = document.createElement('button');
        agregar.type = 'button';
        agregar.className = 'agregar-carrito';
        agregar.setAttribute('aria-label', `Agregar ${producto.nombre} al carrito`);
        const actualizarBoton = () => {
            const enCarrito = window.carritoCompras?.cantidad(producto.id) ?? 0;
            const agotado = producto.cantidadDisponible !== null && enCarrito >= producto.cantidadDisponible;
            agregar.disabled = agotado;
            agregar.textContent = agotado
                ? (producto.cantidadDisponible === 0 ? 'Agotado' : 'Máximo disponible en carrito')
                : 'Agregar al carrito';
        };
        agregar.addEventListener('click', () => window.carritoCompras?.agregar(producto));
        document.addEventListener('carrito:actualizado', actualizarBoton);
        actualizarBoton();

        informacion.append(nombre, precio, cantidad, agregar);
        tarjeta.append(foto, informacion);
        contenedorProductos.appendChild(tarjeta);
    });
}

const contenedorPersonal = document.getElementById('tarjetas-personal');

const perfilBarbero = document.getElementById('perfil-barbero');

if (perfilBarbero) {
    const id = Number(new URLSearchParams(window.location.search).get('id'));
    const barbero = personal.find(persona => persona.id === id) || personal.find(persona => persona.id === 2);

    document.getElementById('nombre-perfil').textContent = barbero.nombre;
    document.getElementById('descripcion-perfil').textContent = barbero.descri;
    const fotoPerfil = document.getElementById('foto-perfil');
    fotoPerfil.width = barbero.ancho;
    fotoPerfil.height = barbero.alto;
    fotoPerfil.sizes = '(max-width: 700px) calc(100vw - 40px), (max-width: 1120px) 42vw, 454px';
    fotoPerfil.srcset = `.${barbero.fotoPequena} 400w, .${barbero.foto} 800w`;
    fotoPerfil.src = '.' + barbero.foto;
    fotoPerfil.alt = `Retrato de ${barbero.nombre.trim()}`;
    document.getElementById('barbero-cita').textContent = barbero.nombre;
    document.title = `${barbero.nombre.trim()} | Fresh Cuttz`;

    const modal = document.getElementById('modal-cita');
    const abrir = document.getElementById('agendar-cita');
    const estado = document.getElementById('estado-cita');

    const datos = document.getElementById('paso-datos');
    const horario = document.getElementById('paso-horario');
    const fecha = document.getElementById('fecha-cita');
    const hora = document.getElementById('hora-cita');
    const paso = document.getElementById('paso-cita');
    const confirmar = document.getElementById('confirmar-cita');
    const volver = document.getElementById('volver-datos');
    const formulario = document.getElementById('formulario-cita');
    const nombreCliente = document.getElementById('nombre-cliente');
    const telefonoCliente = document.getElementById('telefono-cliente');

    function validarDatosCliente(campo) {
        const valor = campo.value.normalize('NFC');
        let mensaje = '';
        if (campo === nombreCliente) {
            if (!valor.trim()) {
                mensaje = 'Escribe tu nombre.';
            } else if (!/^[A-Za-zÁÉÍÓÚÜÑáéíóúüñ ]+$/.test(valor)) {
                mensaje = 'El nombre solo permite letras, acentos, ñ y espacios. No uses números ni símbolos.';
            } else if (valor.trim().length < 2 || valor.length > 100) {
                mensaje = 'El nombre debe tener entre 2 y 100 caracteres, sin contar los espacios de los extremos.';
            }
        } else if (!valor) {
            mensaje = 'Escribe tu teléfono.';
        } else if (!/^[0-9]+$/.test(valor)) {
            mensaje = 'El teléfono solo permite números del 0 al 9, sin espacios, letras ni signos.';
        } else if (valor.length !== 10) {
            mensaje = 'El teléfono debe tener exactamente 10 números, sin lada internacional.';
        }
        campo.setCustomValidity(mensaje);
        campo.setAttribute('aria-invalid', String(Boolean(mensaje)));
        document.getElementById(campo === nombreCliente ? 'error-nombre' : 'error-telefono').textContent = mensaje;
        return !mensaje;
    }

    // Validamos explícitamente para mostrar los mismos avisos al escribir y al continuar.
    formulario.noValidate = true;
    [nombreCliente, telefonoCliente].forEach(campo => {
        campo.addEventListener('input', () => validarDatosCliente(campo));
        campo.addEventListener('blur', () => {
            if (campo === nombreCliente) campo.value = campo.value.normalize('NFC').trim();
            validarDatosCliente(campo);
        });
    });

    
    const reglas = JSON.parse(document.getElementById('reglas-negocio').textContent);
    const horasInicio = [];
    for (let inicio = reglas.horaApertura; inicio + reglas.duracionCitaHoras <= reglas.horaCierre; inicio += reglas.duracionCitaHoras) {
        horasInicio.push(inicio);
    }
    const etiquetaHora = valor => `${valor % 12 || 12} ${valor < 12 ? 'a. m.' : 'p. m.'}`;
    const nombresDias = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
    document.getElementById('horario-local').textContent = `Horario: ${reglas.diasAbiertos.map(dia => nombresDias[dia]).join(', ')}; de ${etiquetaHora(reglas.horaApertura)} a ${etiquetaHora(reglas.horaCierre)}.`;

    function actualizarHorarios() {
        const anterior = hora.value;
        hora.replaceChildren(new Option('Selecciona un horario', ''));
        hora.options[0].disabled = true;
        hora.setCustomValidity('');
        fecha.setCustomValidity('');
        actualizarFechaMinima();
        const dia = new Date(`${fecha.value}T12:00:00`);
        if (!fecha.value) {
            hora.disabled = true;
            return;
        }
        if (!reglas.diasAbiertos.includes(dia.getDay())) {
            fecha.setCustomValidity('El local esta cerrado ese dia. Selecciona un dia de lunes a sabado.');
            estado.textContent = fecha.validationMessage;
            hora.disabled = true;
            return;
        }
        horasInicio.forEach(inicio => {
            const valor = `${String(inicio).padStart(2, '0')}:00`;
            if (new Date(`${fecha.value}T${valor}`) <= new Date()) return;
            hora.append(new Option(`${etiquetaHora(inicio)} a ${etiquetaHora(inicio + reglas.duracionCitaHoras)}`, valor));
        });
        hora.disabled = hora.options.length === 1;
        if (hora.disabled) {
            fecha.setCustomValidity('No quedan horarios para esta fecha. Selecciona otro dia.');
            estado.textContent = fecha.validationMessage;
        } else if (Array.from(hora.options).some(opcion => opcion.value === anterior)) {
            hora.value = anterior;
        }
    }

    function mostrarHorario(visible) {
        datos.hidden = visible;
        datos.disabled = visible;
        horario.hidden = !visible;
        horario.disabled = !visible;
        volver.hidden = !visible;
        paso.textContent = visible ? 'Paso 2 de 2 · Fecha y hora' : 'Paso 1 de 2 · Tus datos';
        confirmar.textContent = visible ? 'Confirmar' : 'Continuar';
        estado.textContent = '';
        if (visible) actualizarHorarios();
    }

    function actualizarFechaMinima() {
        const hoy = new Date();
        fecha.min = `${hoy.getFullYear()}-${String(hoy.getMonth() + 1).padStart(2, '0')}-${String(hoy.getDate()).padStart(2, '0')}`;
    }

    volver.addEventListener('click', () => {
        mostrarHorario(false);
        document.getElementById('nombre-cliente').focus();
    });
    fecha.addEventListener('input', () => {
        estado.textContent = '';
        actualizarHorarios();
    });
    hora.addEventListener('change', () => {
        hora.setCustomValidity('');
        estado.textContent = '';
    });

    abrir.addEventListener('click', () => {
        mostrarHorario(false);
        actualizarFechaMinima();
        estado.textContent = '';
        modal.showModal();
        document.body.classList.add('cita-abierta');
    });
    document.getElementById('cerrar-cita').addEventListener('click', () => modal.close());
    
    modal.addEventListener('click', event => {
        const limites = modal.getBoundingClientRect();
        if (event.target === modal && (
            event.clientX < limites.left || event.clientX > limites.right ||
            event.clientY < limites.top || event.clientY > limites.bottom
        )) modal.close();
    });
    
    modal.addEventListener('close', () => {
        document.body.classList.remove('cita-abierta');
        abrir.focus();
    });
    formulario.addEventListener('submit', event => {
        event.preventDefault();
        nombreCliente.value = nombreCliente.value.normalize('NFC').trim();
        const nombreValido = validarDatosCliente(nombreCliente);
        const telefonoValido = validarDatosCliente(telefonoCliente);
        if (!nombreValido || !telefonoValido) {
            mostrarHorario(false);
            const campoInvalido = nombreValido ? telefonoCliente : nombreCliente;
            campoInvalido.focus();
            campoInvalido.reportValidity();
            return;
        }
        if (horario.hidden) {
            mostrarHorario(true);
            fecha.focus();
            return;
        }
        actualizarHorarios();
        if (!fecha.reportValidity() || !hora.reportValidity()) return;
        const seleccion = new Date(`${fecha.value}T${hora.value}`);
        if (seleccion <= new Date()) {
            hora.setCustomValidity('Selecciona una fecha y hora futuras.');
            hora.reportValidity();
            return;
        }
        const fechaTexto = seleccion.toLocaleDateString('es-MX');
        const horaTexto = hora.selectedOptions[0].textContent;
        modal.close();
        window.carritoCompras?.abrirPago({
            total: reglas.precioCorte,
            resumen: {
                'Cita con': barbero.nombre.trim(),
                'Fecha': fechaTexto,
                'Hora': horaTexto,
                'Precio del corte': new Intl.NumberFormat('es-MX', {
                    style: 'currency',
                    currency: 'MXN'
                }).format(reglas.precioCorte)
            }
        });
    });

    window.addEventListener('cita:pago-confirmado', ({ detail }) => {
        estado.textContent = `Cita reservada con ${detail['Cita con']} para el ${detail.Fecha}, ${detail.Hora}. Método de pago: ${detail.metodo}.`;
    });
}

if (contenedorPersonal) {
    contenedorPersonal.replaceChildren();

    for (let i = 0; i < personal.length; i++) {
        const persona = personal[i];

        const tarjeta = document.createElement('article');
        tarjeta.className = 'tarjeta-personal';
        const seleccionar = document.createElement('a');
        seleccionar.href = `./personal.php?id=${encodeURIComponent(persona.id)}`;
        seleccionar.className = 'seleccionar-personal';
        seleccionar.setAttribute('aria-label', `Ver perfil de ${persona.nombre}`);

        const foto = document.createElement('img');

        foto.width = persona.ancho;
        foto.height = persona.alto;
        foto.loading = i === 0 ? 'eager' : 'lazy';
        foto.decoding = 'async';
        foto.sizes = '(max-width: 600px) 240px, 220px';
        foto.srcset = `.${persona.fotoPequena} 400w, .${persona.foto} 800w`;
        foto.src = '.' + persona.fotoPequena;
        foto.alt = persona.nombre;
        foto.className = 'foto-personal';

        const informacion = document.createElement('span');
        informacion.className = 'info-personal';

        const nombre = document.createElement('span');
        nombre.className = 'nombre-personal';
        nombre.textContent = persona.nombre;

        informacion.append(nombre);
        seleccionar.append(foto, informacion);
        tarjeta.append(seleccionar);
        contenedorPersonal.appendChild(tarjeta);
    }
}

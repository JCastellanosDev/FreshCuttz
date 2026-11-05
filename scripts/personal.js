// Equipo de Fresh Cuttz: tarjetas de la portada (index.php) y perfil con cita (personal.php).
// Los id deben coincidir con BARBEROS en includes/negocio.php.
const SERVICIOS_BASE = ['Corte de cabello', 'Degradados (fade)', 'Arreglo de barba'];

var personal = [
    {nombre:"Mario      ''El guero'' ",descri:"Especializado en pintar cabello rosa", id:1,foto:"/image/personal/mario-800.webp", fotoPequena:"/image/personal/mario-400.webp", ancho:800, alto:800, encuadre:"center 30%", servicios:[...SERVICIOS_BASE, 'Color de fantasía (rosa)']},
    {nombre:"Dany       ''El matute''",descri:"Mitad barbero mitad policia", id:2,foto:"/image/personal/dany-800.webp", fotoPequena:"/image/personal/dany-400.webp", ancho:800, alto:1010, encuadre:"center 15%", servicios:SERVICIOS_BASE},
    {nombre:"Cristian       ''La mune''",descri:"Drogo te lo corta mejor", id:3,foto:"/image/personal/criss-800.webp", fotoPequena:"/image/personal/criss-400.webp", ancho:800, alto:1186, encuadre:"center 12%", servicios:SERVICIOS_BASE},
    {nombre:"Isziee         ''El isipisi''",descri:"Entre semana barbero y fines cevichero", id:4,foto:"/image/personal/isii-800.webp", fotoPequena:"/image/personal/isii-400.webp", ancho:800, alto:533, encuadre:"46% 30%", servicios:SERVICIOS_BASE},
    {nombre:"Mario      ''El grandpa''",descri:"El mas bueno pero el enojon", id:5,foto:"/image/personal/grampa-800.webp", fotoPequena:"/image/personal/grampa-400.webp", ancho:800, alto:533, encuadre:"32% 30%", servicios:SERVICIOS_BASE},];

/** Quita espacios sobrantes y separa nombre y apodo ("Mario ''El guero''"). */
function partesNombre(texto) {
    const completo = texto.trim().replace(/\s+/g, ' ');
    const coincidencia = completo.match(/^(.*?)\s*''(.+?)''$/);
    return coincidencia
        ? { completo, nombre: coincidencia[1], apodo: coincidencia[2] }
        : { completo, nombre: completo, apodo: '' };
}

function crearNombre(persona, etiqueta, clase) {
    const { nombre, apodo } = partesNombre(persona.nombre);
    const elemento = document.createElement(etiqueta);
    elemento.className = clase;
    elemento.append(nombre);
    if (apodo) {
        const textoApodo = document.createElement('span');
        textoApodo.className = `${clase.split(' ')[0].replace('-nombre', '')}-apodo`;
        textoApodo.textContent = apodo;
        elemento.append(' ', textoApodo);
    }
    return elemento;
}

function iconoUi(nombre, clases) {
    return window.ui ? window.ui.icono(nombre, clases) : document.createTextNode('');
}

const contenedorPersonal = document.getElementById('tarjetas-personal');
const perfilBarbero = document.getElementById('perfil-barbero');

if (perfilBarbero) {
    const id = Number(new URLSearchParams(window.location.search).get('id'));
    const barbero = personal.find(persona => persona.id === id) || personal.find(persona => persona.id === 2);
    const nombreBarbero = partesNombre(barbero.nombre);

    // ---------- Perfil ----------
    document.getElementById('nombre-perfil').replaceChildren(...crearNombre(barbero, 'h1', 'perfil-nombre').childNodes);
    document.getElementById('descripcion-perfil').textContent = barbero.descri;

    const fotoPerfil = document.getElementById('foto-perfil');
    fotoPerfil.width = barbero.ancho;
    fotoPerfil.height = barbero.alto;
    fotoPerfil.style.objectPosition = barbero.encuadre;
    fotoPerfil.sizes = '(max-width: 900px) calc(100vw - 32px), 560px';
    fotoPerfil.srcset = `.${barbero.fotoPequena} 400w, .${barbero.foto} 800w`;
    fotoPerfil.src = '.' + barbero.foto;
    fotoPerfil.alt = `Retrato de ${nombreBarbero.completo}`;
    document.title = `${nombreBarbero.completo} | Fresh Cuttz`;

    const listaServicios = document.getElementById('servicios-perfil');
    listaServicios.replaceChildren(...barbero.servicios.map(servicio => {
        const elemento = document.createElement('li');
        elemento.append(iconoUi('check', 'icono-sm'), servicio);
        return elemento;
    }));

    const otros = document.getElementById('otros-barberos');
    otros.replaceChildren(...personal.filter(persona => persona.id !== barbero.id).map(persona => {
        const elemento = document.createElement('li');
        const enlace = document.createElement('a');
        enlace.className = 'otro-barbero';
        enlace.href = `./personal.php?id=${encodeURIComponent(persona.id)}`;
        const foto = document.createElement('img');
        foto.src = '.' + persona.fotoPequena;
        foto.width = 400;
        foto.height = Math.round(400 * persona.alto / persona.ancho);
        foto.loading = 'lazy';
        foto.decoding = 'async';
        foto.alt = '';
        foto.style.objectPosition = persona.encuadre;
        enlace.append(foto, crearNombre(persona, 'span', 'otro-nombre'));
        elemento.append(enlace);
        return elemento;
    }));
    window.ui?.escalonar(otros);

    // ---------- Modal de cita ----------
    const modal = document.getElementById('modal-cita');
    const abrir = document.getElementById('agendar-cita');
    const estado = document.getElementById('estado-cita');
    const datos = document.getElementById('paso-datos');
    const horario = document.getElementById('paso-horario');
    const fecha = document.getElementById('fecha-cita');
    const hora = document.getElementById('hora-cita');
    const campoHora = document.getElementById('campo-hora');
    const sinHorarios = document.getElementById('sin-horarios');
    const errorFecha = document.getElementById('error-fecha');
    const errorHora = document.getElementById('error-hora');
    const paso = document.getElementById('paso-cita');
    const confirmar = document.getElementById('confirmar-cita');
    const volver = document.getElementById('volver-datos');
    const formulario = document.getElementById('formulario-cita');
    const nombreCliente = document.getElementById('nombre-cliente');
    const telefonoCliente = document.getElementById('telefono-cliente');

    document.getElementById('barbero-cita').textContent = nombreBarbero.apodo
        ? `${nombreBarbero.nombre} \u201C${nombreBarbero.apodo}\u201D`
        : nombreBarbero.nombre;

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
        mostrarError(campo, document.getElementById(campo === nombreCliente ? 'error-nombre' : 'error-telefono'), mensaje);
        return !mensaje;
    }

    function mostrarError(campo, elemento, mensaje) {
        campo.setCustomValidity(mensaje);
        campo.setAttribute('aria-invalid', String(Boolean(mensaje)));
        elemento.textContent = mensaje;
    }

    // Validamos a mano para mostrar los mismos avisos al escribir y al continuar.
    formulario.noValidate = true;
    [nombreCliente, telefonoCliente].forEach(campo => {
        campo.addEventListener('input', () => {
            if (campo.getAttribute('aria-invalid') === 'true') validarDatosCliente(campo);
        });
        campo.addEventListener('blur', () => {
            if (campo === nombreCliente) campo.value = campo.value.normalize('NFC').trim();
            if (campo.value) validarDatosCliente(campo);
        });
    });

    const reglas = JSON.parse(document.getElementById('reglas-negocio').textContent);
    const horasInicio = [];
    for (let inicio = reglas.horaApertura; inicio + reglas.duracionCitaHoras <= reglas.horaCierre; inicio += reglas.duracionCitaHoras) {
        horasInicio.push(inicio);
    }
    const etiquetaHora = valor => `${valor % 12 || 12} ${valor < 12 ? 'a. m.' : 'p. m.'}`;
    const nombresDias = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
    const diasTexto = reglas.diasAbiertos.length === 6 && !reglas.diasAbiertos.includes(0)
        ? 'de lunes a sábado'
        : reglas.diasAbiertos.map(dia => nombresDias[dia]).join(', ');
    document.getElementById('horario-local').textContent =
        `Abrimos ${diasTexto}, de ${etiquetaHora(reglas.horaApertura)} a ${etiquetaHora(reglas.horaCierre)}`;

    // Horarios ya reservados con este barbero (los consulta en la base de datos).
    let ocupadas = { fecha: '', horas: new Set() };
    let consultaActual = 0;
    let cargandoHoras = false;

    function hoyLocal() {
        const hoy = new Date();
        return `${hoy.getFullYear()}-${String(hoy.getMonth() + 1).padStart(2, '0')}-${String(hoy.getDate()).padStart(2, '0')}`;
    }
    function actualizarFechaMinima() {
        fecha.min = hoyLocal();
    }

    function marcarCargaHoras(activo) {
        cargandoHoras = activo;
        campoHora.classList.toggle('esta-cargando', activo);
        hora.toggleAttribute('aria-busy', activo);
        if (activo) {
            hora.replaceChildren(new Option('Consultando horarios…', ''));
            hora.disabled = true;
            sinHorarios.hidden = true;
        }
    }

    /** Llena el select con las horas libres. Devuelve true si la fecha es válida y hay horarios. */
    function actualizarHorarios() {
        const anterior = hora.value;
        hora.replaceChildren(new Option(fecha.value ? 'Selecciona un horario' : 'Elige primero una fecha', ''));
        hora.options[0].disabled = true;
        hora.options[0].selected = true;
        hora.setCustomValidity('');
        mostrarError(fecha, errorFecha, '');
        sinHorarios.hidden = true;
        actualizarFechaMinima();
        hora.disabled = true;

        if (!fecha.value) return false;
        if (fecha.value < fecha.min) {
            mostrarError(fecha, errorFecha, 'Elige la fecha de hoy o una futura.');
            return false;
        }
        const dia = new Date(`${fecha.value}T12:00:00`);
        if (!reglas.diasAbiertos.includes(dia.getDay())) {
            mostrarError(fecha, errorFecha, `Ese día está cerrado. Abrimos ${diasTexto}.`);
            return false;
        }
        horasInicio.forEach(inicio => {
            const valor = `${String(inicio).padStart(2, '0')}:00`;
            if (new Date(`${fecha.value}T${valor}`) <= new Date()) return;
            if (ocupadas.fecha === fecha.value && ocupadas.horas.has(valor)) return;
            hora.append(new Option(`${etiquetaHora(inicio)} a ${etiquetaHora(inicio + reglas.duracionCitaHoras)}`, valor));
        });
        if (hora.options.length === 1) {
            fecha.setCustomValidity('No quedan horarios para esta fecha. Selecciona otro día.');
            fecha.setAttribute('aria-invalid', 'true');
            sinHorarios.hidden = false;
            return false;
        }
        hora.disabled = false;
        if (Array.from(hora.options).some(opcion => opcion.value === anterior && anterior)) hora.value = anterior;
        return true;
    }

    /** Consulta las horas ocupadas. silencioso = sin esqueleto (revisión final antes de pagar). */
    async function cargarOcupadas(silencioso = false) {
        const consultada = fecha.value;
        if (!consultada) return;
        const numero = ++consultaActual;
        if (!silencioso) marcarCargaHoras(true);
        try {
            const respuesta = await fetch(`api/citas.php?barbero=${encodeURIComponent(barbero.id)}&fecha=${encodeURIComponent(consultada)}`);
            const resultado = respuesta.ok ? await respuesta.json() : { ocupadas: [] };
            if (numero !== consultaActual) return;
            ocupadas = { fecha: consultada, horas: new Set(resultado.ocupadas || []) };
        } catch {
            // Sin conexión mostramos todos los horarios; el servidor rechaza los ocupados al confirmar.
        }
        if (numero !== consultaActual) return;
        if (!silencioso) marcarCargaHoras(false);
        if (!horario.hidden) actualizarHorarios();
    }

    /** Valida el día elegido y, si el local abre, consulta la disponibilidad. */
    function alCambiarFecha() {
        estado.textContent = '';
        errorHora.textContent = '';
        consultaActual++;
        marcarCargaHoras(false);
        if (actualizarHorarios()) cargarOcupadas();
    }

    function mostrarHorario(visible) {
        datos.hidden = visible;
        datos.disabled = visible;
        horario.hidden = !visible;
        horario.disabled = !visible;
        volver.hidden = !visible;
        formulario.dataset.paso = visible ? '2' : '1';
        paso.textContent = visible ? 'Paso 2 de 2 · Fecha y hora' : 'Paso 1 de 2 · Tus datos';
        confirmar.textContent = visible ? 'Continuar al pago' : 'Continuar';
        estado.textContent = '';
        if (visible) {
            actualizarFechaMinima();
            alCambiarFecha();
        }
    }

    volver.addEventListener('click', () => {
        mostrarHorario(false);
        nombreCliente.focus();
    });
    fecha.addEventListener('change', alCambiarFecha);
    hora.addEventListener('change', () => {
        mostrarError(hora, errorHora, '');
        estado.textContent = '';
    });

    let abriendoPago = false;
    function cerrarModal() {
        if (window.ui) window.ui.cerrarDialogo(modal);
        else modal.close();
    }

    abrir.addEventListener('click', () => {
        mostrarHorario(false);
        abriendoPago = false;
        modal.showModal();
        document.body.classList.add('cita-abierta');
    });
    document.getElementById('cerrar-cita').addEventListener('click', cerrarModal);
    modal.addEventListener('cancel', event => {
        event.preventDefault();
        cerrarModal();
    });
    modal.addEventListener('click', event => {
        const limites = modal.getBoundingClientRect();
        if (event.target === modal && (
            event.clientX < limites.left || event.clientX > limites.right ||
            event.clientY < limites.top || event.clientY > limites.bottom
        )) cerrarModal();
    });
    modal.addEventListener('close', () => {
        document.body.classList.remove('cita-abierta');
        if (!abriendoPago) abrir.focus();
    });

    formulario.addEventListener('submit', async event => {
        event.preventDefault();
        if (confirmar.disabled) return;
        nombreCliente.value = nombreCliente.value.normalize('NFC').trim();
        const nombreValido = validarDatosCliente(nombreCliente);
        const telefonoValido = validarDatosCliente(telefonoCliente);
        if (!nombreValido || !telefonoValido) {
            mostrarHorario(false);
            (nombreValido ? telefonoCliente : nombreCliente).focus();
            return;
        }
        if (horario.hidden) {
            mostrarHorario(true);
            fecha.focus();
            return;
        }
        if (cargandoHoras) return;
        if (!fecha.value) {
            mostrarError(fecha, errorFecha, 'Elige la fecha de tu cita.');
            fecha.focus();
            return;
        }
        const elegida = hora.value;
        if (!actualizarHorarios()) {
            fecha.focus();
            return;
        }
        if (!elegida || hora.value !== elegida) {
            mostrarError(hora, errorHora, elegida ? 'Ese horario ya no está disponible. Elige otro.' : 'Elige un horario.');
            hora.focus();
            return;
        }

        // Revisamos de nuevo la disponibilidad justo antes de abrir el pago.
        window.ui?.cargando(confirmar, true);
        await cargarOcupadas(true);
        window.ui?.cargando(confirmar, false);
        if (hora.value !== elegida) {
            mostrarError(hora, errorHora, 'Alguien acaba de apartar ese horario. Elige otro.');
            hora.focus();
            return;
        }
        const seleccion = new Date(`${fecha.value}T${hora.value}`);
        if (seleccion <= new Date()) {
            mostrarError(hora, errorHora, 'Selecciona una fecha y hora futuras.');
            hora.focus();
            return;
        }
        if (!window.carritoCompras?.abrirPago) {
            window.ui?.toast('No pudimos abrir el pago. Recarga la página e inténtalo de nuevo.', 'error');
            return;
        }

        const fechaTexto = seleccion.toLocaleDateString('es-MX');
        const horaTexto = hora.selectedOptions[0].textContent;
        abriendoPago = true;
        if (window.ui) await window.ui.cerrarDialogo(modal);
        else modal.close();
        window.carritoCompras.abrirPago({
            total: reglas.precioCorte,
            cita: {
                barbero_id: barbero.id,
                fecha: fecha.value,
                hora: hora.value,
                nombre: nombreCliente.value,
                telefono: telefonoCliente.value
            },
            resumen: {
                'Cita con': nombreBarbero.completo,
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
        const mensaje = `Cita reservada con ${detail['Cita con']} para el ${detail.Fecha}, ${detail.Hora}. Método de pago: ${detail.metodo}.`;
        estado.textContent = mensaje;
        window.ui?.toast(`Cita reservada con ${detail['Cita con']}: ${detail.Fecha}, ${detail.Hora}.`, 'exito', 5000);
        // La hora quedó ocupada: limpiamos la selección para una siguiente cita.
        fecha.value = '';
        ocupadas = { fecha: '', horas: new Set() };
    });
}

if (contenedorPersonal) {
    contenedorPersonal.replaceChildren();

    personal.forEach((persona, i) => {
        const { completo } = partesNombre(persona.nombre);
        const tarjeta = document.createElement('article');
        tarjeta.className = 'barbero';

        const enlace = document.createElement('a');
        enlace.href = `./personal.php?id=${encodeURIComponent(persona.id)}`;
        enlace.className = 'barbero-enlace';

        const marco = document.createElement('div');
        marco.className = 'barbero-foto';
        const foto = document.createElement('img');
        foto.width = persona.ancho;
        foto.height = persona.alto;
        foto.loading = i < 2 ? 'eager' : 'lazy';
        foto.decoding = 'async';
        foto.sizes = '(max-width: 599px) calc(100vw - 32px), (max-width: 1023px) 50vw, 600px';
        foto.srcset = `.${persona.fotoPequena} 400w, .${persona.foto} 800w`;
        foto.src = '.' + persona.fotoPequena;
        foto.alt = `Retrato de ${completo}`;
        foto.style.objectPosition = persona.encuadre;
        marco.append(foto, crearNombre(persona, 'h3', 'barbero-nombre'));

        const pie = document.createElement('div');
        pie.className = 'barbero-pie';
        const descripcion = document.createElement('p');
        descripcion.className = 'barbero-descripcion';
        descripcion.textContent = persona.descri;
        const ver = document.createElement('span');
        ver.className = 'barbero-ver';
        ver.append(iconoUi('flecha-derecha'));
        const textoVer = document.createElement('span');
        textoVer.className = 'sr-only';
        textoVer.textContent = 'Ver perfil y agendar';
        ver.append(textoVer);
        pie.append(descripcion, ver);

        enlace.append(marco, pie);
        tarjeta.append(enlace);
        contenedorPersonal.append(tarjeta);
    });
    window.ui?.escalonar(contenedorPersonal);
}

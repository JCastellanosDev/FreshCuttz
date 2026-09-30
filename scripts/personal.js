var personal =[
    {nombre:"Mario      ''El güero'' ",descri:"Especializado en pintar cabello rosa", id:1,foto:"/image/personal/mario.jpg"},
    {nombre:"Dany       ''El matute''",descri:"Mitad barbero mitad policia", id:2,foto:"/image/personal/dany.png"},
    {nombre:"Cristian       ''La muñe''",descri:"Drogo te lo corta mejor", id:3,foto:"/image/personal/criss.png"},
    {nombre:"Isziee         ''El isipisi''",descri:"Entre semana barbero y fines cevichero", id:4,foto:"/image/personal/isii.jpg"},
    {nombre:"Mario      ''El grandpa''",descri:"El mas bueno pero el enojon", id:5,foto:"/image/personal/grampa.jpg"},];


const contenedorPersonal = document.getElementById('tarjetas-personal');

const perfilBarbero = document.getElementById('perfil-barbero');

if (perfilBarbero) {
    const id = Number(new URLSearchParams(window.location.search).get('id'));
    const barbero = personal.find(persona => persona.id === id) || personal.find(persona => persona.id === 2);

    document.getElementById('nombre-perfil').textContent = barbero.nombre;
    document.getElementById('descripcion-perfil').textContent = barbero.descri;
    const fotoPerfil = document.getElementById('foto-perfil');
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

    // Días de JavaScript: domingo = 0, lunes = 1, ..., sábado = 6.
    const reglas = JSON.parse(document.getElementById('reglas-negocio').textContent);
    const horasInicio = [];
    for (let inicio = reglas.horaApertura; inicio + reglas.duracionCitaHoras <= reglas.horaCierre; inicio += reglas.duracionCitaHoras) {
        horasInicio.push(inicio);
    }
    const etiquetaHora = valor => `${valor % 12 || 12} ${valor < 12 ? 'a. m.' : 'p. m.'}`;
    const nombresDias = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
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
            fecha.setCustomValidity('El local está cerrado ese día. Selecciona un día de lunes a sábado.');
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
            fecha.setCustomValidity('No quedan horarios para esta fecha. Selecciona otro día.');
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
    // El backdrop nativo dirige sus eventos al dialog; distinguimos su área visible.
    modal.addEventListener('click', event => {
        const limites = modal.getBoundingClientRect();
        if (event.target === modal && (
            event.clientX < limites.left || event.clientX > limites.right ||
            event.clientY < limites.top || event.clientY > limites.bottom
        )) modal.close();
    });
    // También se ejecuta cuando el usuario cierra con Escape.
    modal.addEventListener('close', () => {
        document.body.classList.remove('cita-abierta');
        abrir.focus();
    });
    document.getElementById('formulario-cita').addEventListener('submit', event => {
        event.preventDefault();
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
        estado.textContent = `Has seleccionado una cita con ${barbero.nombre.trim()} para el ${seleccion.toLocaleDateString('es-MX')}, de ${hora.selectedOptions[0].textContent}. Aún no se ha enviado ni reservado.`;
    });
}

if (contenedorPersonal) {
    contenedorPersonal.replaceChildren();

    for (let i = 0; i < personal.length; i++) {
        const persona = personal[i];

        const tarjeta = document.createElement('article');
        tarjeta.className = 'tarjeta-personal';
        const seleccionar = document.createElement('a');
        seleccionar.href = `personal.html?id=${persona.id}`;
        seleccionar.className = 'seleccionar-personal';
        seleccionar.setAttribute('aria-label', `Ver perfil de ${persona.nombre}`);

        const foto = document.createElement('img');

        foto.src = '.' + persona.foto;
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

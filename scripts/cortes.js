// Galería de cortes (productos.php → #galeria-cortes).
// Slider con scroll-snap: swipe nativo en móvil, flechas e indicadores en todos los tamaños.
var cortes = [
    { nombre: "Axel sin barba", descripcion: "Antes", precio: 250, foto: "./image/cortes/antes.jpeg", id: 1 },
    { nombre: "Axel barbón", descripcion: "Después", precio: 250, foto: "./image/cortes/depues.jpeg", id: 2 },
    { nombre: "Axel barbón y largo", descripcion: "Mega después", precio: 250, foto: "./image/cortes/megaD.png", id: 8 },
    { nombre: "Nice", descripcion: "nice", precio: 250, foto: "./image/cortes/cortesi.jpg", id: 4 },
    { nombre: "Clean", descripcion: "ta chilo", precio: 250, foto: "./image/cortes/corteM.jpg", id: 7 },
    { nombre: "Ta chido", descripcion: "bien loquillo", precio: 250, foto: "./image/cortes/diseno-autor.jpg", id: 5 },
    { nombre: "Pelón", descripcion: "bien pelón mi compa", precio: 250, foto: "./image/cortes/Pelon.jpg", id: 6 },
];

(function () {
    const galeria = document.getElementById('galeria-cortes');
    const controles = document.getElementById('galeria-controles');
    if (!galeria || !cortes.length) return;

    const icono = (nombre, clases) => window.ui?.icono(nombre, clases) ?? document.createTextNode('');
    const reducirMovimiento = window.matchMedia('(prefers-reduced-motion: reduce)');
    const total = cortes.length;

    galeria.setAttribute('role', 'region');
    galeria.setAttribute('aria-roledescription', 'carrusel');
    galeria.setAttribute('aria-label', 'Galería de cortes');

    const pista = document.createElement('ul');
    pista.className = 'galeria-pista';
    pista.tabIndex = 0;
    pista.setAttribute('aria-label', 'Cortes, usa las flechas del teclado para recorrerlos');

    const indicadores = document.createElement('div');
    indicadores.className = 'galeria-indicadores';
    indicadores.setAttribute('role', 'group');
    indicadores.setAttribute('aria-label', 'Elegir corte');

    const anuncio = document.createElement('p');
    anuncio.className = 'sr-only';
    anuncio.setAttribute('aria-live', 'polite');

    cortes.forEach((corte, i) => {
        const item = document.createElement('li');
        item.className = 'galeria-item';
        item.setAttribute('role', 'group');
        item.setAttribute('aria-roledescription', 'diapositiva');
        item.setAttribute('aria-label', `${i + 1} de ${total}: ${corte.nombre}`);

        const figura = document.createElement('figure');
        const foto = document.createElement('img');
        foto.src = corte.foto;
        foto.alt = `Corte: ${corte.nombre}`;
        foto.loading = i < 3 ? 'eager' : 'lazy';
        foto.decoding = 'async';
        foto.draggable = false;

        const pie = document.createElement('figcaption');
        const nombre = document.createElement('h3');
        nombre.textContent = corte.nombre;
        const contador = document.createElement('span');
        contador.className = 'galeria-contador t-num';
        contador.setAttribute('aria-hidden', 'true');
        contador.textContent = `${String(i + 1).padStart(2, '0')} / ${String(total).padStart(2, '0')}`;
        pie.append(nombre, contador);

        figura.append(foto, pie);
        item.append(figura);
        pista.append(item);

        const punto = document.createElement('button');
        punto.type = 'button';
        punto.className = 'galeria-punto';
        punto.setAttribute('aria-label', `Ver corte ${i + 1}: ${corte.nombre}`);
        punto.addEventListener('click', () => irA(i, true));
        indicadores.append(punto);
    });

    galeria.replaceChildren(pista, indicadores, anuncio);

    const crearFlecha = (direccion, etiqueta) => {
        const boton = document.createElement('button');
        boton.type = 'button';
        boton.className = 'btn btn-contorno btn-icono galeria-flecha';
        boton.setAttribute('aria-label', etiqueta);
        boton.setAttribute('aria-controls', 'galeria-cortes');
        boton.append(icono(`chevron-${direccion}`));
        return boton;
    };
    const anterior = crearFlecha('izquierda', 'Corte anterior');
    const siguiente = crearFlecha('derecha', 'Siguiente corte');
    anterior.addEventListener('click', () => irA(actual - 1, true));
    siguiente.addEventListener('click', () => irA(actual + 1, true));
    if (controles) {
        controles.replaceChildren(anterior, siguiente);
        controles.hidden = false;
    }

    const items = [...pista.children];
    const puntos = [...indicadores.children];
    let actual = -1;
    let objetivo = null; // destino elegido con botón o teclado (puede quedar más allá del último alcanzable)

    // Índice máximo al que se puede desplazar (en escritorio caben varias diapositivas).
    const ultimoAlcanzable = () => {
        const limite = pista.scrollWidth - pista.clientWidth - 2;
        const indice = items.findIndex(item => item.offsetLeft - pista.offsetLeft >= limite);
        return indice === -1 ? total - 1 : indice;
    };

    function marcar(indice, anunciar) {
        if (indice === actual) return;
        actual = indice;
        puntos.forEach((punto, i) => {
            const activo = i === indice;
            punto.classList.toggle('activo', activo);
            if (activo) punto.setAttribute('aria-current', 'true');
            else punto.removeAttribute('aria-current');
        });
        items.forEach((item, i) => item.classList.toggle('activo', i === indice));
        anterior.disabled = indice <= 0;
        siguiente.disabled = indice >= ultimoAlcanzable();
        if (anunciar) anuncio.textContent = `Corte ${indice + 1} de ${total}: ${cortes[indice].nombre}`;
    }

    function irA(indice, anunciar) {
        const destino = Math.max(0, Math.min(indice, total - 1));
        objetivo = destino;
        pista.scrollTo({
            left: items[destino].offsetLeft - pista.offsetLeft,
            behavior: reducirMovimiento.matches ? 'auto' : 'smooth',
        });
        marcar(destino, false);
        if (anunciar) anuncio.textContent = `Corte ${destino + 1} de ${total}: ${cortes[destino].nombre}`;
    }

    // Sincroniza el indicador con el desplazamiento (swipe, rueda o trackpad).
    let pendiente = false;
    pista.addEventListener('scroll', () => {
        if (pendiente) return;
        pendiente = true;
        requestAnimationFrame(() => {
            pendiente = false;
            const izquierda = pista.scrollLeft;
            let cercano = 0;
            let distancia = Infinity;
            items.forEach((item, i) => {
                const d = Math.abs(item.offsetLeft - pista.offsetLeft - izquierda);
                if (d < distancia) { distancia = d; cercano = i; }
            });
            if (izquierda >= pista.scrollWidth - pista.clientWidth - 2) {
                cercano = Math.max(cercano, objetivo ?? ultimoAlcanzable());
            }
            marcar(cercano, false);
        });
    }, { passive: true });

    // Un gesto directo del usuario anula el destino elegido con botón.
    ['pointerdown', 'wheel', 'touchstart'].forEach(tipo =>
        pista.addEventListener(tipo, () => { objetivo = null; }, { passive: true }));

    pista.addEventListener('keydown', (evento) => {
        const teclas = { ArrowLeft: actual - 1, ArrowRight: actual + 1, Home: 0, End: total - 1 };
        if (!(evento.key in teclas)) return;
        evento.preventDefault();
        irA(teclas[evento.key], true);
    });

    window.addEventListener('resize', () => {
        const indice = actual;
        actual = -1;
        marcar(Math.min(indice, ultimoAlcanzable()), false);
    });

    marcar(0, false);
})();

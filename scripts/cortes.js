var cortes = [
    {nombre:"Axel sin barba",descripcion:"Antes",  precio:250, foto:"./image/cortes/antes.jpeg",id:1},
    {nombre:"Axel barbon",descripcion:"Despues", precio:250, foto:"./image/cortes/depues.jpeg", id:2},
    {nombre:"Axel barbon y largo",descripcion:"Mega despues", precio:250, foto:"./image/cortes/megaD.png", id:8},
    {nombre:"Nice",descripcion:"nice", precio:250, foto:"./image/cortes/cortesi.jpg", id:4},
    {nombre:"Clean",descripcion:"ta chilo", precio:250, foto:"./image/cortes/corteM.jpg", id:7},
    
    {nombre:"Ta chido",descripcion:"bien loquillo", precio:250, foto:"./image/cortes/dieñoAutor.jpg", id:5},
    {nombre:"Peloon",descripcion:"bien pelon mi compa", precio:250, foto:"./image/cortes/Pelon.jpg", id:6},
   

];



const carrusel = document.getElementById('carouselExampleIndicators');

if (carrusel && cortes.length > 0) {
    const contenido = document.createElement('div');
    contenido.className = 'carousel-inner';
    const pantallaMovil = window.matchMedia('(max-width: 600px)');

    const indicadores = document.createElement('div');
    indicadores.className = 'carousel-indicators';

   
    for (let i = 0; i < cortes.length; i++) {
        const corte = cortes[i];

        const diapositiva = document.createElement('figure');
        diapositiva.className = 'carousel-item';
        diapositiva.hidden = i !== 0;
        diapositiva.setAttribute('role', 'group');
        diapositiva.setAttribute('aria-label', `${i + 1} de ${cortes.length}`);

        const foto = document.createElement('img');
        foto.src = corte.foto;
        foto.alt = corte.nombre;
        foto.loading = 'lazy';
        foto.decoding = 'async';

        
        const informacion = document.createElement('figcaption');
        const nombre = document.createElement('h2');
        nombre.textContent = corte.nombre;
        const contador = document.createElement('span');
        contador.textContent = `${i + 1} / ${cortes.length}`;

      
        informacion.append(nombre, contador);
        diapositiva.append(foto, informacion);
        contenido.appendChild(diapositiva);

        const indicador = document.createElement('button');
        indicador.type = 'button';
        indicador.setAttribute('aria-label', `Ver corte ${i + 1}: ${corte.nombre}`);
        indicador.addEventListener('click', () => mostrarCorte(i));
        indicadores.appendChild(indicador);
    }

    carrusel.replaceChildren(contenido, indicadores);

    const anterior = document.createElement('button');
    anterior.type = 'button';
    anterior.className = 'carousel-control-prev';
    anterior.textContent = '‹';
    anterior.setAttribute('aria-label', 'Corte anterior');

    const siguiente = document.createElement('button');
    siguiente.type = 'button';
    siguiente.className = 'carousel-control-next';
    siguiente.textContent = '›';
    siguiente.setAttribute('aria-label', 'Siguiente corte');
    carrusel.append(anterior, siguiente);

    let corteActual = 0;

   
    function mostrarCorte(indice) {
        corteActual = (indice + cortes.length) % cortes.length;

        for (let i = 0; i < cortes.length; i++) {
            const activo = i === corteActual;
            contenido.children[i].hidden = pantallaMovil.matches && !activo;
            contenido.children[i].classList.toggle('active', activo);
            indicadores.children[i].classList.toggle('active', activo);
            if (activo) indicadores.children[i].setAttribute('aria-current', 'true');
            else indicadores.children[i].removeAttribute('aria-current');
        }
    }

    anterior.addEventListener('click', () => mostrarCorte(corteActual - 1));
    siguiente.addEventListener('click', () => mostrarCorte(corteActual + 1));
    carrusel.addEventListener('keydown', (evento) => {
        if (!pantallaMovil.matches) return;
        if (evento.key !== 'ArrowLeft' && evento.key !== 'ArrowRight') return;
        evento.preventDefault();
        mostrarCorte(corteActual + (evento.key === 'ArrowRight' ? 1 : -1));
    });

    function actualizarVista() {
        if (pantallaMovil.matches) {
            carrusel.setAttribute('aria-roledescription', 'carrusel');
            carrusel.tabIndex = 0;
            contenido.setAttribute('aria-live', 'polite');
        } else {
            carrusel.removeAttribute('aria-roledescription');
            carrusel.removeAttribute('tabindex');
            contenido.removeAttribute('aria-live');
        }
        mostrarCorte(corteActual);
    }

    let inicioToque = null;
    contenido.addEventListener('touchstart', (evento) => {
        inicioToque = evento.touches[0].clientX;
    }, { passive: true });
    contenido.addEventListener('touchend', (evento) => {
        if (!pantallaMovil.matches || inicioToque === null) return;
        const distancia = inicioToque - evento.changedTouches[0].clientX;
        if (Math.abs(distancia) > 50) mostrarCorte(corteActual + (distancia > 0 ? 1 : -1));
        inicioToque = null;
    }, { passive: true });
    pantallaMovil.addEventListener('change', actualizarVista);
    actualizarVista();
}

var personal =[
    {nombre:"Mario      ''El güero'' ",descri:"Especializado en pintar cabello rosa", id:1,foto:"/image/personal/mario.jpg"},
    {nombre:"Dany       '' El matute''",descri:"Mitad barbero mitad policia", id:2,foto:"/image/personal/dany.png"},
    {nombre:"Cristian       ''La muñe''",descri:"Drogo te lo corta mejor", id:3,foto:"/image/personal/criss.png"},
    {nombre:"Isziee         ''El isipisi''",descri:"Entre semana barbero y fines cevichero", id:4,foto:"/image/personal/isii.jpg"},
    {nombre:"Mario      ''El grandpa''",descri:"El mas bueno pero el enojon", id:5,foto:"/image/personal/grampa.jpg"},];


const contenedorPersonal = document.getElementById('tarjetas-personal');

if (contenedorPersonal) {
    contenedorPersonal.replaceChildren();

  
    const ventana = document.createElement('dialog');
    ventana.className = 'ventana-personal';
    ventana.setAttribute('aria-labelledby', 'nombre-barbero');
    ventana.setAttribute('aria-describedby', 'descripcion-barbero');
    const tituloVentana = document.createElement('h2');
    tituloVentana.id = 'nombre-barbero';
    const descripcionVentana = document.createElement('p');
    descripcionVentana.id = 'descripcion-barbero';
    const cerrar = document.createElement('button');
    cerrar.type = 'button';
    cerrar.textContent = 'Cerrar';
    cerrar.addEventListener('click', () => ventana.close());
    ventana.append(tituloVentana, descripcionVentana, cerrar);
    contenedorPersonal.after(ventana);

    for (let i = 0; i < personal.length; i++) {
        const persona = personal[i];

        const tarjeta = document.createElement('article');
        tarjeta.className = 'tarjeta-personal';
        const seleccionar = document.createElement('button');
        seleccionar.type = 'button';
        seleccionar.className = 'seleccionar-personal';
        seleccionar.setAttribute('aria-label', `Ver descripción de ${persona.nombre}`);
        seleccionar.setAttribute('aria-haspopup', 'dialog');
        seleccionar.addEventListener('click', () => {
            tituloVentana.textContent = persona.nombre;
            descripcionVentana.textContent = persona.descri;
            ventana.showModal();
        });

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

<?php
$tituloPagina = 'Personal';
$mostrarNavegacion = false;
$hojasEstiloAdicionales = ['./styles/contenedores.css'];
require __DIR__ . '/includes/header.php';
?>
    <script id="reglas-negocio" type="application/json">
    {
        "diasAbiertos": [1, 2, 3, 4, 5, 6],
        "horaApertura": 10,
        "horaCierre": 20,
        "duracionCitaHoras": 1
    }
    </script>
    <script src="scripts/personal.js" defer></script>
    <main class="perfil-personal">
      <article class="contenedor-info" id="perfil-barbero" aria-labelledby="nombre-perfil">
        <div class="contenedor-foto">
            <img id="foto-perfil" alt="" width="800" height="1010" fetchpriority="high" loading="eager">
        </div>
        <div class="contenedor-barber">
            <p class="perfil-etiqueta">Fresh Cuttz · Nuestro equipo</p>
            <h1 id="nombre-perfil"></h1>
            <p id="descripcion-perfil" class="perfil-descripcion"></p>
            <section class="perfil-servicios" aria-labelledby="titulo-servicios">
                <h2 id="titulo-servicios">Servicios que realiza</h2>
                <ul id="servicios-perfil"></ul>
            </section>
            <button class="boton-cita" id="agendar-cita" type="button" aria-haspopup="dialog" aria-controls="modal-cita">Agendar Cita <span aria-hidden="true">↗</span></button>
        </div>
      </article>
      <noscript>Activa JavaScript para consultar el perfil y abrir el formulario de citas.</noscript>
    </main>

    <dialog class="modal-cita" id="modal-cita" aria-labelledby="titulo-cita" aria-describedby="detalle-cita">
        <button class="cerrar-cita" id="cerrar-cita" type="button" aria-label="Cerrar formulario">×</button>
        <p class="perfil-etiqueta">Tu proximo corte</p>
        <h2 id="titulo-cita">Agendar Cita</h2>
        <p id="detalle-cita">Completa tus datos para solicitar una cita con <span id="barbero-cita"></span>.</p>
        <form id="formulario-cita">
            <p id="paso-cita" class="paso-cita">Paso 1 de 2 · Tus datos</p>
            <fieldset id="paso-datos">
                <legend class="sr-only">Tus datos</legend>
                <label for="nombre-cliente">Tu Nombre</label>
                <input id="nombre-cliente" name="nombre" type="text" autocomplete="name" maxlength="100" required autofocus>
                <label for="telefono-cliente">Tu Telefono</label>
                <input id="telefono-cliente" name="telefono" type="tel" autocomplete="tel" maxlength="30" required>
            </fieldset>
            <fieldset id="paso-horario" hidden disabled>
                <legend class="sr-only">Fecha y hora de tu cita</legend>
                <p class="aviso-cita" id="horario-local"></p>
                <label for="fecha-cita">Fecha</label>
                <input id="fecha-cita" name="fecha" type="date" required>
                <label for="hora-cita">Hora</label>
                <select id="hora-cita" name="hora" required>
                    <option value="" disabled selected>Selecciona un horario</option>
                </select>
            </fieldset>
            <p class="aviso-cita">Los datos aun no se envian ni se guardan. La disponibilidad esta pendiente de confirmacion.</p>
            <button class="boton-cita" id="confirmar-cita" type="submit">Continuar</button>
            <button class="volver-datos" id="volver-datos" type="button" hidden>Volver a mis datos</button>
            <p id="estado-cita" role="status" aria-live="polite"></p>
        </form>
    </dialog>
    
<?php require __DIR__ . '/includes/footer.php'; ?>

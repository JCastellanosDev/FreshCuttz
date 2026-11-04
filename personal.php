<?php
$tituloPagina = 'Personal';
$mostrarNavegacion = false;
$hojasEstiloAdicionales = ['./styles/contenedores.css?v=' . filemtime(__DIR__ . '/styles/contenedores.css')];
require_once __DIR__ . '/includes/negocio.php';
$reglasNegocio = [
    'diasAbiertos' => DIAS_ABIERTOS,
    'horaApertura' => HORA_APERTURA,
    'horaCierre' => HORA_CIERRE,
    'duracionCitaHoras' => DURACION_CITA_HORAS,
    'precioCorte' => precioCorte(),
];
require __DIR__ . '/includes/header.php';
?>
<script id="reglas-negocio" type="application/json"><?= json_encode($reglasNegocio) ?></script>
<script src="scripts/personal.js?v=<?= filemtime(__DIR__ . '/scripts/personal.js') ?>" defer></script>
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
            <button class="boton-cita" id="agendar-cita" type="button" aria-haspopup="dialog"
                aria-controls="modal-cita">Agendar Cita <span aria-hidden="true">↗</span></button>
        </div>
    </article>
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
            <input id="nombre-cliente" name="nombre" type="text" autocomplete="name" minlength="2" maxlength="100"
                pattern="[A-Za-zÁÉÍÓÚÜÑáéíóúüñ ]+" aria-describedby="ayuda-nombre error-nombre" required autofocus>
            <p class="ayuda-campo" id="ayuda-nombre">Solo letras de favor</p>
            <p class="error-campo" id="error-nombre" aria-live="polite"></p>
            <label for="telefono-cliente">Tu Teléfono</label>
            <input id="telefono-cliente" name="telefono" type="tel" autocomplete="tel-national" inputmode="numeric"
                pattern="[0-9]{10}" aria-describedby="ayuda-telefono error-telefono" required>
            <p class="ayuda-campo" id="ayuda-telefono">Exactamente 10 números</p>
            <p class="error-campo" id="error-telefono" aria-live="polite"></p>
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
        </fieldset> La disponibilidad esta pendiente de confirmacion.</p>
        <button class="boton-cita" id="confirmar-cita" type="submit">Continuar</button>
        <button class="volver-datos" id="volver-datos" type="button" hidden>Volver a mis datos</button>
        <p id="estado-cita" role="status" aria-live="polite"></p>
    </form>
</dialog>

<?php require __DIR__ . '/includes/footer.php'; ?>
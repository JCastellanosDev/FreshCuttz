<?php
$tituloPagina = 'Nuestro equipo | Fresh Cuttz';
$mostrarNavegacion = false;
$hojasEstiloAdicionales = ['./styles/perfil.css?v=' . filemtime(__DIR__ . '/styles/perfil.css')];
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
<main id="contenido" class="perfil">
    <a class="perfil-volver btn btn-fantasma" href="index.php#equipo"><?= svgIcono('flecha-izquierda') ?>Volver al equipo</a>

    <article class="perfil-barbero" id="perfil-barbero" aria-labelledby="nombre-perfil">
        <div class="perfil-foto">
            <img id="foto-perfil" alt="" width="800" height="1010" fetchpriority="high" loading="eager">
        </div>
        <div class="perfil-info">
            <h1 id="nombre-perfil" class="perfil-nombre">
                <span class="esqueleto perfil-esqueleto-nombre" aria-hidden="true"></span>
            </h1>
            <p id="descripcion-perfil" class="perfil-descripcion"></p>

            <dl class="perfil-detalles">
                <div>
                    <dt><?= svgIcono('tijeras', 'icono-sm') ?>Corte</dt>
                    <dd class="t-num"><?= e(dinero($reglasNegocio['precioCorte'])) ?> MXN</dd>
                </div>
                <div>
                    <dt><?= svgIcono('reloj', 'icono-sm') ?>Duración</dt>
                    <dd><?= DURACION_CITA_HORAS ?> hora<?= DURACION_CITA_HORAS === 1 ? '' : 's' ?></dd>
                </div>
                <div>
                    <dt><?= svgIcono('calendario', 'icono-sm') ?>Horario</dt>
                    <dd><span class="sin-corte">Lun–Sáb</span> · <span class="sin-corte"><?= HORA_APERTURA ?>:00–<?= HORA_CIERRE ?>:00</span></dd>
                </div>
            </dl>

            <section class="perfil-servicios" aria-labelledby="titulo-servicios">
                <h2 id="titulo-servicios">Servicios que realiza</h2>
                <ul id="servicios-perfil" class="servicios-lista"></ul>
            </section>

            <button class="btn btn-primario btn-lg perfil-cta" id="agendar-cita" type="button" aria-haspopup="dialog"
                aria-controls="modal-cita"><?= svgIcono('calendario') ?>Agendar cita</button>
        </div>
    </article>

    <section class="otros-barberos" aria-labelledby="titulo-otros">
        <h2 id="titulo-otros">También en el equipo</h2>
        <ul id="otros-barberos" class="otros-lista"></ul>
    </section>
</main>

<dialog class="dialogo dialogo-cita" id="modal-cita" aria-labelledby="titulo-cita" aria-describedby="detalle-cita">
    <div class="dialogo-cabecera">
        <h2 id="titulo-cita">Agenda tu cita</h2>
        <button class="btn btn-fantasma btn-icono" id="cerrar-cita" type="button" aria-label="Cerrar formulario">
            <?= svgIcono('cerrar') ?>
        </button>
    </div>
    <p id="detalle-cita" class="cita-detalle">Con <strong id="barbero-cita"></strong>. Primero tus datos, luego eliges día y hora.</p>

    <form id="formulario-cita" class="cita-formulario" data-paso="1">
        <div class="cita-pasos">
            <div class="cita-progreso" aria-hidden="true"><span></span><span></span></div>
            <p id="paso-cita" class="cita-paso" aria-live="polite">Paso 1 de 2 · Tus datos</p>
        </div>

        <fieldset id="paso-datos" class="cita-fieldset">
            <legend class="sr-only">Tus datos</legend>
            <div class="campo">
                <label for="nombre-cliente">Tu nombre</label>
                <input id="nombre-cliente" name="nombre" type="text" autocomplete="name" minlength="2" maxlength="100"
                    pattern="[A-Za-zÁÉÍÓÚÜÑáéíóúüñ ]+" aria-describedby="ayuda-nombre error-nombre" required autofocus>
                <p class="campo-ayuda" id="ayuda-nombre">Solo letras, acentos y espacios.</p>
                <p class="campo-error" id="error-nombre" aria-live="polite"></p>
            </div>
            <div class="campo">
                <label for="telefono-cliente">Tu teléfono</label>
                <input id="telefono-cliente" name="telefono" type="tel" autocomplete="tel-national" inputmode="numeric"
                    pattern="[0-9]{10}" maxlength="10" aria-describedby="ayuda-telefono error-telefono" required>
                <p class="campo-ayuda" id="ayuda-telefono">10 números, sin lada internacional.</p>
                <p class="campo-error" id="error-telefono" aria-live="polite"></p>
            </div>
        </fieldset>

        <fieldset id="paso-horario" class="cita-fieldset" hidden disabled>
            <legend class="sr-only">Fecha y hora de tu cita</legend>
            <p class="aviso"><?= svgIcono('reloj') ?><span id="horario-local"></span></p>
            <div class="campo">
                <label for="fecha-cita">Fecha</label>
                <input id="fecha-cita" name="fecha" type="date" aria-describedby="error-fecha" required>
                <p class="campo-error" id="error-fecha" aria-live="polite"></p>
            </div>
            <div class="campo campo-hora" id="campo-hora">
                <label for="hora-cita">Hora</label>
                <div class="hora-control">
                    <select id="hora-cita" name="hora" aria-describedby="ayuda-hora error-hora" required disabled>
                        <option value="" disabled selected>Elige primero una fecha</option>
                    </select>
                    <span class="esqueleto hora-esqueleto" aria-hidden="true"></span>
                </div>
                <p class="campo-ayuda" id="ayuda-hora">La disponibilidad se confirma al registrar tu pago.</p>
                <p class="campo-error" id="error-hora" aria-live="polite"></p>
            </div>
            <p class="aviso aviso-advertencia" id="sin-horarios" hidden><?= svgIcono('alerta') ?><span>Ya no quedan horarios libres ese día. Prueba con otra fecha.</span></p>
        </fieldset>

        <div class="cita-acciones">
            <button class="btn btn-primario btn-lg btn-bloque" id="confirmar-cita" type="submit">Continuar</button>
            <button class="btn btn-fantasma btn-bloque" id="volver-datos" type="button" hidden>Volver a mis datos</button>
        </div>
        <p id="estado-cita" class="cita-estado" role="status" aria-live="polite"></p>
    </form>
</dialog>

<?php require __DIR__ . '/includes/footer.php'; ?>

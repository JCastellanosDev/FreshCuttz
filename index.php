<?php
$paginaActual = 'inicio';
$tituloPagina = 'Fresh Cuttz Barbershop · Chihuahua';
$hojasEstiloAdicionales = ['./styles/inicio.css?v=' . filemtime(__DIR__ . '/styles/inicio.css')];
require_once __DIR__ . '/includes/negocio.php';
try {
    $precioCorte = precioCorte();
} catch (PDOException $e) {
    error_log('FreshCuttz inicio: ' . $e->getMessage());
    $precioCorte = 250;
}
require __DIR__ . '/includes/header.php';
?>
<main id="contenido" class="inicio">
    <section class="portada" aria-labelledby="titulo-portada">
        <div class="portada-foto">
            <img src="./image/fresco.jpg" width="1080" height="1080"
                alt="Barbero de espaldas con la camisa negra que dice #ISTAYFRESH" fetchpriority="high">
        </div>
        <div class="portada-texto">
            <h1 id="titulo-portada">Cortes <span class="portada-acento">frescos</span> desde 2013</h1>
            <p class="portada-apoyo">Barbería urbana en Chihuahua. Elige a tu barbero, aparta tu hora y sal fresco.</p>
            <p id="bienvenida-perfil" class="saludo-cliente" role="status" hidden></p>
            <div class="portada-acciones">
                <a class="btn btn-primario btn-lg" href="#equipo"><?= svgIcono('calendario') ?>Agenda tu cita</a>
                <a class="btn btn-contorno btn-lg" href="productos.php">Ver servicios</a>
            </div>
        </div>
    </section>

    <section class="datos-local" aria-label="Datos del local">
        <dl class="datos-lista">
            <div class="dato">
                <?= svgIcono('reloj') ?>
                <dt>Horario</dt>
                <dd>Lun–Sáb · 10:00–20:00</dd>
            </div>
            <div class="dato">
                <?= svgIcono('ubicacion') ?>
                <dt>Ubicación</dt>
                <dd><a href="contacto.php">Av. Industrias, Chihuahua</a></dd>
            </div>
            <div class="dato">
                <?= svgIcono('tijeras') ?>
                <dt>Corte</dt>
                <dd class="t-num"><?= e(dinero($precioCorte)) ?> MXN</dd>
            </div>
        </dl>
    </section>

    <section class="equipo" id="equipo" aria-labelledby="titulo-equipo">
        <div class="equipo-cabecera">
            <h2 id="titulo-equipo">El equipo</h2>
            <p class="equipo-intro">Cada cita se agenda directo con tu barbero. Elige con quién te quieres sentar.</p>
        </div>
        <div id="tarjetas-personal" class="equipo-rejilla"></div>
    </section>
</main>
<script src="./scripts/personal.js?v=<?= filemtime(__DIR__ . '/scripts/personal.js') ?>" defer></script>
<?php require __DIR__ . '/includes/footer.php'; ?>

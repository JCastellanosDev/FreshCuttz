<?php
$tituloPagina = 'Servicios · Fresh Cuttz';
$paginaActual = 'productos';
$claseBody = 'pagina-servicios';
$hojasEstiloAdicionales = ['./styles/servicios.css?v=' . filemtime(__DIR__ . '/styles/servicios.css')];
$enlaceResenas = 'https://www.google.com/search?q=fresh+cuttz+barbershop&hl=es#lrd=0x86ea4182764ea61d:0xa3ee2cd426c2fa55,1,,,,';

// Precio del corte (el mismo de la galería en scripts/cortes.js).
$precioCorte = 250;

$calificacion = 4.8;
$totalOpiniones = 89;
$resenas = [
    [
        'autor' => 'Carlos Arturo Leyva Estrada',
        'texto' => 'Excelente servicio y excelente ambiente súper recomendado',
    ],
    [
        'autor' => 'Emmanuel HS',
        'texto' => 'Muy buena y rápida atención',
    ],
    [
        'autor' => 'alonso amaya',
        'texto' => 'Excelentes personas y buenos cortes de pelo y atención.',
    ],
];
require_once __DIR__ . '/includes/negocio.php';
try {
    $catalogo = productosActivos();
} catch (PDOException $e) {
    error_log('FreshCuttz catálogo: ' . $e->getMessage());
    $catalogo = [];
}
require __DIR__ . '/includes/header.php';

$enlaceResenasSeguro = htmlspecialchars($enlaceResenas, ENT_QUOTES, 'UTF-8');
$rellenoEstrellas = round($calificacion / 5 * 100, 1);
?>
<script id="catalogo-productos" type="application/json"><?= json_encode($catalogo, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
<main id="contenido" class="servicios-pagina">
    <header class="sv-encabezado contenedor">
        <div class="sv-encabezado-titulo">
            <span class="sv-poste" aria-hidden="true"></span>
            <h1>Servicios</h1>
        </div>
        <p class="sv-encabezado-texto">Algunos trabajos de la barbería, nuestros productos y lo que opinan nuestros clientes.</p>

        <div class="sv-precio-corte">
            <p class="sv-precio">
                <span class="sv-precio-etiqueta">Corte de cabello</span>
                <span class="sv-precio-monto t-num">$<?= number_format($precioCorte) ?> <small>MXN</small></span>
            </p>
            <a class="btn btn-primario btn-lg" href="index.php#equipo">
                <?= svgIcono('calendario') ?>
                Agenda tu cita
            </a>
        </div>

        <nav class="sv-subnav" aria-label="En esta página">
            <a href="#cortes"><?= svgIcono('tijeras', 'icono-sm') ?>Cortes</a>
            <a href="#productos"><?= svgIcono('productos', 'icono-sm') ?>Productos</a>
            <a href="#resenas"><?= svgIcono('estrella', 'icono-sm') ?>Reseñas</a>
        </nav>
    </header>

    <section id="cortes" class="sv-seccion contenedor" aria-labelledby="titulo-cortes">
        <div class="sv-seccion-cabecera">
            <h2 id="titulo-cortes">Cortes</h2>
            <div class="galeria-controles" id="galeria-controles" hidden></div>
        </div>
        <div id="galeria-cortes" class="galeria"></div>
    </section>

    <section id="productos" class="sv-seccion contenedor" aria-labelledby="titulo-productos">
        <div class="sv-seccion-cabecera">
            <h2 id="titulo-productos">Productos</h2>
            <?php if ($catalogo): ?>
                <p class="sv-seccion-nota t-num"><?= count($catalogo) ?> <?= count($catalogo) === 1 ? 'producto' : 'productos' ?></p>
            <?php endif; ?>
        </div>
        <div id="tarjetas-productos" class="tienda" aria-busy="true">
            <?php for ($i = 0; $i < min(count($catalogo), 8); $i++): ?>
                <div class="producto producto-esqueleto" aria-hidden="true">
                    <div class="producto-foto esqueleto"></div>
                    <div class="producto-cuerpo">
                        <span class="esqueleto esq-linea"></span>
                        <span class="esqueleto esq-precio"></span>
                        <span class="esqueleto esq-boton"></span>
                    </div>
                </div>
            <?php endfor; ?>
        </div>
    </section>

    <section id="resenas" class="sv-seccion contenedor opiniones" aria-labelledby="titulo-resenas">
        <div class="opiniones-resumen">
            <h2 id="titulo-resenas">Reseñas en Google</h2>
            <p class="opiniones-calificacion t-num"><?= number_format($calificacion, 1) ?><span>/ 5</span></p>
            <div class="estrellas" role="img" aria-label="<?= number_format($calificacion, 1) ?> de 5 estrellas">
                <span class="estrellas-base" aria-hidden="true"><?= str_repeat(svgIcono('estrella', 'icono-relleno'), 5) ?></span>
                <span class="estrellas-relleno" aria-hidden="true" style="width: <?= $rellenoEstrellas ?>%"><?= str_repeat(svgIcono('estrella', 'icono-relleno'), 5) ?></span>
            </div>
            <p class="opiniones-total t-num"><?= $totalOpiniones ?> opiniones en Google</p>
            <a class="btn btn-contorno" href="<?= $enlaceResenasSeguro ?>" target="_blank" rel="noopener noreferrer">
                Ver todas las reseñas en Google
                <?= svgIcono('externo', 'icono-sm') ?>
                <span class="sr-only">(se abre en una pestaña nueva)</span>
            </a>
            <p class="opiniones-fuente">Selección de opiniones · Datos consultados el <time datetime="2026-10-22">22 de octubre de 2026</time>.</p>
        </div>

        <div class="opiniones-lista">
            <?php foreach ($resenas as $resena): ?>
                <figure class="cita">
                    <blockquote cite="<?= $enlaceResenasSeguro ?>">
                        <p>“<?= htmlspecialchars($resena['texto'], ENT_QUOTES, 'UTF-8') ?>”</p>
                    </blockquote>
                    <figcaption><?= htmlspecialchars($resena['autor'], ENT_QUOTES, 'UTF-8') ?></figcaption>
                </figure>
            <?php endforeach; ?>
        </div>
    </section>
</main>
<script src="./scripts/cortes.js?v=<?= filemtime(__DIR__ . '/scripts/cortes.js') ?>" defer></script>
<script src="./scripts/productos.js?v=<?= filemtime(__DIR__ . '/scripts/productos.js') ?>" defer></script>
<?php require __DIR__ . '/includes/footer.php'; ?>

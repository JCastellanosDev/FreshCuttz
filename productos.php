<?php
$tituloPagina = 'Nuestros Servicios';
$paginaActual = 'productos';
$claseBody = 'pagina-servicios';
$enlaceResenas = 'https://www.google.com/search?q=fresh+cuttz+barbershop&hl=es#lrd=0x86ea4182764ea61d:0xa3ee2cd426c2fa55,1,,,,';

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
?>
<script id="catalogo-productos" type="application/json"><?= json_encode($catalogo, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
<main class="servicios">
    <details class="servicios-desplegable">
        <summary class="servicios-intro">
            <h1>
                <span class="barber-pole" aria-hidden="true"></span>
                <span>Nuestros cortes</span>
                <span class="barber-pole" aria-hidden="true"></span>
            </h1>
        </summary>
        <div class="servicios-intro">
            <p>Algunos trabajos de la barberia.</p>
        </div>
        <section id="carouselExampleIndicators" class="carousel slide" aria-label="Galeria de cortes">
        </section>
    </details>
    <details class="servicios-desplegable">
        <summary class="servicios-intro">
            <h1 class="segundo-titulo">
                <span>Nuestros productos</span>
            </h1>
        </summary>
        <section id="tarjetas-productos" class="tarjetas-productos" aria-label="Nuestros productos"></section>
    </details>
    <section class="resenas-google" aria-labelledby="titulo-resenas">
        <h2 id="titulo-resenas">
            <span class="resenas-estrella" aria-hidden="true">★</span>
            <span>Reseñas en Google</span>
            <span class="resenas-estrella" aria-hidden="true">★</span>
        </h2>
        <div class="resenas-resumen">
            <strong class="resenas-calificacion">4.8 <span>/ 5</span></strong>
            <span class="resenas-estrellas" role="img" aria-label="4.8 de 5 estrellas">
                <span aria-hidden="true">★★★★★</span>
                <span class="resenas-estrellas-relleno" aria-hidden="true">★★★★★</span>
            </span>
            <p>89 opiniones en Google</p>
        </div>
        <div class="resenas-lista">
            <?php foreach ($resenas as $resena): ?>
                <article class="resena-tarjeta">
                    <p class="resena-origen">Opinión en Google</p>
                    <blockquote cite="<?= htmlspecialchars($enlaceResenas, ENT_QUOTES, 'UTF-8') ?>">
                        “<?= htmlspecialchars($resena['texto'], ENT_QUOTES, 'UTF-8') ?>”</blockquote>
                    <p class="resena-autor">
                        <?= htmlspecialchars($resena['autor'], ENT_QUOTES, 'UTF-8') ?>
                    </p>
                </article>
            <?php endforeach; ?>
        </div>
        <div class="resenas-enlaces">
            <a class="resenas-ver-todas" href="<?= htmlspecialchars($enlaceResenas, ENT_QUOTES, 'UTF-8') ?>"
                target="_blank" rel="noopener noreferrer">Ver todas las reseñas en Google <span
                    aria-hidden="true">↗</span></a>
            <p>Selección de opiniones · Datos consultados el <time datetime="2026-10-22">22 de octubre de 2026</time>.
            </p>
        </div>
    </section>
</main>
<script src="./scripts/cortes.js" defer></script>
<script src="./scripts/personal.js?v=<?= filemtime(__DIR__ . '/scripts/personal.js') ?>" defer></script>
<?php require __DIR__ . '/includes/footer.php'; ?>
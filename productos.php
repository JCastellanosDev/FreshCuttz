<?php
$tituloPagina = 'Nuestros Servicios';
$paginaActual = 'productos';
$claseBody = 'pagina-servicios';
require __DIR__ . '/includes/header.php';
?>
    <main class="servicios">
        <div class="servicios-intro">
            <h1>
                <span class="barber-pole" aria-hidden="true"></span>
                <span>Nuestros cortes</span>
                <span class="barber-pole" aria-hidden="true"></span>
            </h1>
            <p>Algunos trabajos de la barberia.</p>
        </div>
        <section id="carouselExampleIndicators" class="carousel slide" aria-label="Galeria de cortes">
        </section>
    </main>
    <script src="./scripts/cortes.js" defer></script>
<?php require __DIR__ . '/includes/footer.php'; ?>

<?php
$paginaActual = 'inicio';
require __DIR__ . '/includes/header.php';
?>
    <main class="personal" aria-labelledby="titulo-personal">
        <h1 id="titulo-personal">Te presentamos a nuestro personal</h1>
        <div id="tarjetas-personal" class="tarjetas-personal"></div>
    </main>
    <script src="./scripts/personal.js?v=2" defer></script>
<?php require __DIR__ . '/includes/footer.php'; ?>

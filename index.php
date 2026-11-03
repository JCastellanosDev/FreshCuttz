<?php
$paginaActual = 'inicio';
require __DIR__ . '/includes/header.php';
?>
<main class="personal" aria-labelledby="titulo-personal">
    <p id="bienvenida-perfil" class="bienvenida-perfil" role="status" hidden></p>
    <h1 id="titulo-personal">Te presentamos a nuestro personal</h1>
    <div id="tarjetas-personal" class="tarjetas-personal"></div>
</main>
<script src="./scripts/personal.js?v=<?= filemtime(__DIR__ . '/scripts/personal.js') ?>" defer></script>
<?php require __DIR__ . '/includes/footer.php'; ?>

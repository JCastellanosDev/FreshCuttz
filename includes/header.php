
<?php
$tituloPagina = $tituloPagina ?? 'Fresh Cuttz Barbershop';
$paginaActual = $paginaActual ?? '';
$hojasEstiloAdicionales = $hojasEstiloAdicionales ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tituloPagina, ENT_QUOTES, 'UTF-8') ?></title>

    <link rel="stylesheet" href="./styles/styles.css">

    <?php foreach ($hojasEstiloAdicionales as $css): ?>
        <link rel="stylesheet" href="<?= htmlspecialchars($css, ENT_QUOTES, 'UTF-8') ?>">
    <?php endforeach; ?>
</head>

<body>
    <header>
        <div class="logo">
            <a href="index.php">
                <img src="./image/logo1.webp"
                     width="259"
                     height="192"
                     alt="Fresh Cuttz">
            </a>
        </div>

        <nav class="accesos" aria-label="Navegación principal">
            <a href="index.php" <?= $paginaActual === 'inicio' ? 'aria-current="page"' : '' ?>>Inicio</a>
            <a href="productos.php" <?= $paginaActual === 'productos' ? 'aria-current="page"' : '' ?>>Servicios</a>
            <a href="contacto.php" <?= $paginaActual === 'contacto' ? 'aria-current="page"' : '' ?>>Contáctanos</a>
        </nav>
    </header>

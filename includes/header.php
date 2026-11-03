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

    <link rel="stylesheet" href="./styles/styles.css?v=<?= filemtime(__DIR__ . '/../styles/styles.css') ?>">
    <script src="./scripts/carrito.js?v=<?= filemtime(__DIR__ . '/../scripts/carrito.js') ?>" defer></script>
    <script src="./scripts/perfil.js?v=<?= filemtime(__DIR__ . '/../scripts/perfil.js') ?>" defer></script>

    <?php foreach ($hojasEstiloAdicionales as $css): ?>
        <link rel="stylesheet" href="<?= htmlspecialchars($css, ENT_QUOTES, 'UTF-8') ?>">
    <?php endforeach; ?>
</head>

<body>
    <header>
        <div class="logo">
            <a href="index.php">
                <img src="./image/logo1.webp" width="259" height="192" alt="Fresh Cuttz">
            </a>
        </div>

        <nav class="accesos" aria-label="Navegación principal">
            <a href="index.php" <?= $paginaActual === 'inicio' ? 'aria-current="page"' : '' ?>>Inicio</a>
            <a href="productos.php" <?= $paginaActual === 'productos' ? 'aria-current="page"' : '' ?>>Servicios</a>
            <a href="contacto.php" <?= $paginaActual === 'contacto' ? 'aria-current="page"' : '' ?>>Contáctanos</a>
            <button class="boton-carrito" type="button" aria-label="Abrir carrito de compras" aria-haspopup="dialog"
                aria-controls="panel-carrito" aria-expanded="false">
                <img class="icono-carrito" src="./image/icon/botonCarrito-sin-fondo.png" width="40" height="40" alt="">
                <span class="carrito-contador" id="carrito-contador" hidden aria-hidden="true">0</span>
            </button>
            <button class="boton-perfil" id="abrir-perfil" type="button" aria-label="Abrir inicio de sesión"
                aria-haspopup="dialog" aria-controls="modal-login" aria-expanded="false" title="Mi perfil">
                <img src="./image/icon/perfil.webp" width="32" height="32" alt="">
            </button>
            <button class="boton-cerrar-sesion" id="cerrar-sesion" type="button" hidden>Cerrar sesión</button>
        </nav>
    </header>

    <dialog class="modal-login" id="modal-login" aria-labelledby="titulo-login" aria-describedby="descripcion-login">
        <div class="login-cabecera">
            <span class="login-etiqueta">Fresh Cuttz · Mi perfil</span>
            <button class="carrito-cerrar" id="cerrar-login" type="button"
                aria-label="Cerrar inicio de sesión">×</button>
        </div>
        <h2 id="titulo-login">Iniciar sesión</h2>
        <p id="descripcion-login">Ingresa tu nombre y utiliza tu número de teléfono como contraseña.</p>
        <form id="formulario-login" method="post">
            <label for="login-nombre">Nombre</label>
            <input id="login-nombre" name="nombre" type="text" autocomplete="username" minlength="2" maxlength="100"
                pattern="[A-Za-zÁÉÍÓÚÜÑáéíóúüñ ]+" aria-describedby="login-ayuda-nombre login-error-nombre" required
                autofocus>
            <p class="login-ayuda" id="login-ayuda-nombre"></p>
            <p class="login-error" id="login-error-nombre" aria-live="polite"></p>
            <label for="login-telefono">Contraseña (tu teléfono)</label>
            <div class="login-clave">
                <input id="login-telefono" name="telefono" type="password" inputmode="numeric"
                    autocomplete="current-password" pattern="[0-9]{10}"
                    aria-describedby="login-ayuda-telefono login-error-telefono" required>
                <button id="mostrar-clave" type="button" aria-controls="login-telefono" aria-pressed="false"
                    aria-label="Mostrar contraseña">Mostrar</button>
            </div>
            <p class="login-ayuda" id="login-ayuda-telefono"></p>
            <p class="login-error" id="login-error-telefono" aria-live="polite"></p>
            <button class="carrito-continuar" type="submit">Iniciar sesión</button>
            <button class="login-cuenta" id="ya-tengo-cuenta" type="button">Ya tengo cuenta</button>
            <p id="login-estado" role="status" aria-live="polite"></p>
        </form>
    </dialog>

    <dialog class="panel-carrito" id="panel-carrito" aria-labelledby="titulo-carrito">
        <div class="carrito-contenido">
            <div class="carrito-cabecera">
                <h2 id="titulo-carrito">Tu carrito</h2>
                <button class="carrito-cerrar" type="button" aria-label="Cerrar carrito" data-cerrar-carrito
                    autofocus>&times;</button>
            </div>
            <div class="carrito-productos" id="carrito-productos">
                <p class="carrito-vacio">Tu carrito está vacío.</p>
            </div>
            <div class="carrito-acciones">
                <p class="carrito-total"><span>Total</span><strong id="carrito-total">$0.00</strong></p>
                <p class="carrito-estado" id="carrito-estado" role="status" aria-live="polite"></p>
                <button class="carrito-continuar" id="carrito-pagar" type="button" aria-haspopup="dialog"
                    aria-controls="modal-pago" disabled>Pagar</button>
            </div>
        </div>
    </dialog>
    <dialog class="modal-pago" id="modal-pago" aria-labelledby="titulo-pago" aria-describedby="descripcion-pago">
        <div class="pago-cabecera">
            <h2 id="titulo-pago">Método de pago</h2>
            <button class="carrito-cerrar" id="cerrar-pago" type="button" aria-label="Volver al carrito">×</button>
        </div>
        <p id="descripcion-pago">Elige cómo deseas pagar.</p>
        <div class="pago-resumen" id="pago-resumen" hidden aria-live="polite"></div>
        <p class="carrito-total"><span>Total</span><strong id="pago-total">$0.00</strong></p>
        <form id="formulario-pago">
            <fieldset class="pago-metodos">
                <legend>Selecciona una opción</legend>
                <label class="pago-opcion">
                    <input type="radio" name="metodo-pago" value="efectivo" required
                        aria-describedby="detalle-efectivo">
                    <span><strong>Efectivo</strong><small id="detalle-efectivo">Paga en la barbería al recoger tus
                            productos.</small></span>
                </label>
                <div class="pago-transferencia">
                    <label class="pago-opcion">
                        <input type="radio" name="metodo-pago" value="transferencia" required
                            aria-describedby="detalle-transferencia">
                        <span><strong>Transferencia</strong><small id="detalle-transferencia">Consulta los datos para
                                realizar tu pago.</small></span>
                    </label>
                    <details class="pago-cuenta">
                        <summary>Ver datos de transferencia</summary>
                        <p><strong>Banco</strong><span>BBVA</span></p>
                        <p><strong>Titular</strong><span>Jesús Castellanos</span></p>
                        <p><strong>Número de cuenta</strong><span>0101010101010101010101</span></p>
                    </details>
                </div>
                <label class="pago-opcion">
                    <input type="radio" name="metodo-pago" value="mercado-pago" required
                        aria-describedby="detalle-mercado-pago">
                    <span><strong>Mercado Pago</strong><small id="detalle-mercado-pago"></small></span>
                </label>
            </fieldset>
            <button class="carrito-continuar" type="submit">Confirmar pago</button>
            <button class="pago-volver" id="volver-carrito" type="button">Volver al carrito</button>
        </form>
    </dialog>

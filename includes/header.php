<?php
require_once __DIR__ . '/iconos.php';
$tituloPagina = $tituloPagina ?? 'Fresh Cuttz Barbershop';
$paginaActual = $paginaActual ?? '';
$hojasEstiloAdicionales = $hojasEstiloAdicionales ?? [];
$claseBody = $claseBody ?? '';
$enlacesNav = [
    'inicio' => ['index.php', 'Inicio'],
    'productos' => ['productos.php', 'Servicios'],
    'club' => ['tarjeta.php', 'Fresh Club'],
    'contacto' => ['contacto.php', 'Contáctanos'],
];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#110f0e">
    <title><?= htmlspecialchars($tituloPagina, ENT_QUOTES, 'UTF-8') ?></title>

    <link rel="preload" href="./fonts/archivo-latin.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="./styles/sistema.css?v=<?= filemtime(__DIR__ . '/../styles/sistema.css') ?>">
    <link rel="stylesheet" href="./styles/styles.css?v=<?= filemtime(__DIR__ . '/../styles/styles.css') ?>">
    <script src="./scripts/ui.js?v=<?= filemtime(__DIR__ . '/../scripts/ui.js') ?>" defer></script>
    <script src="./scripts/carrito.js?v=<?= filemtime(__DIR__ . '/../scripts/carrito.js') ?>" defer></script>
    <script src="./scripts/perfil.js?v=<?= filemtime(__DIR__ . '/../scripts/perfil.js') ?>" defer></script>

    <?php foreach ($hojasEstiloAdicionales as $css): ?>
        <link rel="stylesheet" href="<?= htmlspecialchars($css, ENT_QUOTES, 'UTF-8') ?>">
    <?php endforeach; ?>
</head>

<body<?= $claseBody !== '' ? ' class="' . htmlspecialchars($claseBody, ENT_QUOTES, 'UTF-8') . '"' : '' ?>>
    <a class="saltar-contenido" href="#contenido">Saltar al contenido</a>

    <header class="encabezado">
        <div class="contenedor encabezado-barra">
            <a class="encabezado-logo" href="index.php">
                <img src="./image/logo1.webp" width="259" height="192" alt="Fresh Cuttz, ir al inicio">
            </a>

            <nav class="nav-principal" id="nav-principal" aria-label="Navegación principal">
                <ul>
                    <?php foreach ($enlacesNav as $clave => [$url, $texto]): ?>
                        <li>
                            <a href="<?= $url ?>"<?= $paginaActual === $clave ? ' aria-current="page"' : '' ?>><?= $texto ?><?= svgIcono('chevron-derecha') ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>

            <div class="encabezado-acciones">
                <button class="btn btn-fantasma btn-icono boton-carrito" type="button"
                    aria-label="Abrir carrito de compras" aria-haspopup="dialog" aria-controls="panel-carrito"
                    aria-expanded="false" title="Carrito">
                    <?= svgIcono('carrito', 'icono-lg') ?>
                    <span class="carrito-contador" id="carrito-contador" hidden aria-hidden="true">0</span>
                </button>
                <button class="btn btn-fantasma btn-icono boton-perfil" id="abrir-perfil" type="button"
                    aria-label="Abrir inicio de sesión" aria-haspopup="dialog" aria-controls="modal-login"
                    aria-expanded="false" title="Mi perfil">
                    <?= svgIcono('usuario', 'icono-lg') ?>
                </button>
                <button class="btn btn-fantasma btn-icono boton-cerrar-sesion" id="cerrar-sesion" type="button"
                    aria-label="Cerrar sesión" hidden>
                    <?= svgIcono('salir') ?>
                    <span class="cerrar-sesion-texto" aria-hidden="true">Cerrar sesión</span>
                </button>
                <button class="btn btn-fantasma btn-icono boton-menu" id="boton-menu" type="button"
                    aria-label="Abrir menú" aria-controls="nav-principal" aria-expanded="false">
                    <?= svgIcono('menu', 'icono-lg icono-menu-abrir') ?>
                    <?= svgIcono('cerrar', 'icono-lg icono-menu-cerrar') ?>
                </button>
            </div>
        </div>
    </header>

    <dialog class="dialogo modal-login" id="modal-login" aria-labelledby="titulo-login"
        aria-describedby="descripcion-login">
        <div class="dialogo-cabecera">
            <h2 id="titulo-login">Crear cuenta</h2>
            <button class="btn btn-fantasma btn-icono" id="cerrar-login" type="button"
                aria-label="Cerrar inicio de sesión"><?= svgIcono('cerrar') ?></button>
        </div>
        <p class="dialogo-descripcion" id="descripcion-login">Ingresa tu nombre y utiliza tu número de teléfono como contraseña.</p>
        <form class="formulario-login" id="formulario-login" method="post">
            <div class="campo">
                <label for="login-nombre">Nombre</label>
                <input id="login-nombre" name="nombre" type="text" autocomplete="username" minlength="2"
                    maxlength="100" pattern="[A-Za-zÁÉÍÓÚÜÑáéíóúüñ ]+"
                    aria-describedby="login-ayuda-nombre login-error-nombre" required autofocus>
                <p class="campo-ayuda" id="login-ayuda-nombre"></p>
                <p class="campo-error" id="login-error-nombre" aria-live="polite"></p>
            </div>
            <div class="campo">
                <label for="login-telefono">Contraseña (tu teléfono a 10 dígitos)</label>
                <div class="campo-clave">
                    <input id="login-telefono" name="telefono" type="password" inputmode="numeric"
                        autocomplete="current-password" pattern="[0-9]{10}"
                        aria-describedby="login-ayuda-telefono login-error-telefono" required>
                    <button class="btn btn-fantasma btn-icono" id="mostrar-clave" type="button"
                        aria-controls="login-telefono" aria-pressed="false"
                        aria-label="Mostrar contraseña"><?= svgIcono('ojo') ?></button>
                </div>
                <p class="campo-ayuda" id="login-ayuda-telefono"></p>
                <p class="campo-error" id="login-error-telefono" aria-live="polite"></p>
            </div>
            <p class="dialogo-estado" id="login-estado" role="status" aria-live="polite"></p>
            <div class="login-acciones">
                <button class="btn btn-primario btn-lg btn-bloque" type="submit">Crear cuenta</button>
                <button class="btn btn-fantasma btn-bloque" id="ya-tengo-cuenta" type="button">Ya tengo cuenta</button>
            </div>
        </form>
    </dialog>

    <dialog class="dialogo dialogo-lateral panel-carrito" id="panel-carrito" aria-labelledby="titulo-carrito">
        <div class="carrito-contenido">
            <div class="carrito-cabecera">
                <h2 id="titulo-carrito">Tu carrito</h2>
                <button class="btn btn-fantasma btn-icono" type="button" aria-label="Cerrar carrito"
                    data-cerrar-carrito autofocus><?= svgIcono('cerrar') ?></button>
            </div>
            <div class="carrito-productos" id="carrito-productos"></div>
            <div class="carrito-acciones">
                <p class="linea-total carrito-total"><span>Total</span><strong id="carrito-total">$0.00</strong></p>
                <p class="carrito-estado" id="carrito-estado" role="status" aria-live="polite"></p>
                <button class="btn btn-primario btn-lg btn-bloque" id="carrito-pagar" type="button"
                    aria-haspopup="dialog" aria-controls="modal-pago" disabled>
                    Pagar<?= svgIcono('flecha-derecha') ?>
                </button>
            </div>
        </div>
    </dialog>

    <dialog class="dialogo modal-pago" id="modal-pago" aria-labelledby="titulo-pago" aria-describedby="descripcion-pago">
        <div class="dialogo-cabecera">
            <h2 id="titulo-pago">Método de pago</h2>
            <button class="btn btn-fantasma btn-icono" id="cerrar-pago" type="button"
                aria-label="Cerrar método de pago"><?= svgIcono('cerrar') ?></button>
        </div>
        <p class="dialogo-descripcion" id="descripcion-pago">Elige cómo deseas pagar.</p>
        <div class="pago-resumen" id="pago-resumen" hidden aria-live="polite"></div>
        <p class="linea-total"><span>Total a pagar</span><strong id="pago-total">$0.00</strong></p>
        <form class="formulario-pago" id="formulario-pago">
            <fieldset class="pago-metodos">
                <legend>Selecciona una opción</legend>
                <label class="pago-opcion">
                    <input type="radio" name="metodo-pago" value="efectivo" required
                        aria-describedby="detalle-efectivo">
                    <span class="pago-opcion-icono"><?= svgIcono('efectivo') ?></span>
                    <span class="pago-opcion-texto">
                        <strong>Efectivo</strong>
                        <small id="detalle-efectivo">Paga en la barbería al recoger tus productos.</small>
                    </span>
                    <span class="pago-opcion-marca"><?= svgIcono('check') ?></span>
                </label>
                <div class="pago-transferencia">
                    <label class="pago-opcion">
                        <input type="radio" name="metodo-pago" value="transferencia" required
                            aria-describedby="detalle-transferencia">
                        <span class="pago-opcion-icono"><?= svgIcono('banco') ?></span>
                        <span class="pago-opcion-texto">
                            <strong>Transferencia</strong>
                            <small id="detalle-transferencia">Consulta los datos para realizar tu pago.</small>
                        </span>
                        <span class="pago-opcion-marca"><?= svgIcono('check') ?></span>
                    </label>
                    <details class="pago-cuenta">
                        <summary>Ver datos de transferencia<?= svgIcono('abajo', 'icono-sm') ?></summary>
                        <dl>
                            <div><dt>Banco</dt><dd>BBVA</dd></div>
                            <div><dt>Titular</dt><dd>Jesús Castellanos</dd></div>
                            <div><dt>Número de cuenta</dt><dd>0101010101010101010101</dd></div>
                        </dl>
                    </details>
                </div>
                <label class="pago-opcion">
                    <input type="radio" name="metodo-pago" value="mercado-pago" required
                        aria-describedby="detalle-mercado-pago">
                    <span class="pago-opcion-icono"><?= svgIcono('tarjeta') ?></span>
                    <span class="pago-opcion-texto">
                        <strong>Mercado Pago</strong>
                        <small id="detalle-mercado-pago">Te enviamos un enlace para pagar con tarjeta.</small>
                    </span>
                    <span class="pago-opcion-marca"><?= svgIcono('check') ?></span>
                </label>
            </fieldset>
            <p class="pago-estado" id="pago-estado" role="alert"></p>
            <div class="pago-botones">
                <button class="btn btn-primario btn-lg btn-bloque" type="submit">Confirmar pago</button>
                <button class="btn btn-fantasma btn-bloque" id="volver-carrito" type="button">
                    <?= svgIcono('flecha-izquierda') ?>Volver al carrito
                </button>
            </div>
        </form>
        <div class="pago-confirmacion" id="pago-confirmacion" role="status" hidden>
            <span class="pago-confirmacion-icono"><?= svgIcono('listo') ?></span>
            <h3>¡Listo!</h3>
            <p id="pago-confirmacion-texto"></p>
            <button class="btn btn-primario btn-lg btn-bloque" id="pago-listo" type="button">Volver al inicio</button>
        </div>
    </dialog>

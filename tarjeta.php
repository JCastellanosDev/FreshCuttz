<?php
// Fresh Club: tarjeta de sellos del cliente. Cada visita (tap NFC en el mostrador) suma un sello.
require_once __DIR__ . '/includes/sesion.php';
require_once __DIR__ . '/includes/lealtad.php';
abrirSesion();

$usuarioId = isset($_SESSION['usuario_id']) ? (int)$_SESSION['usuario_id'] : null;
$nombre = $usuarioId ? (string)$_SESSION['usuario_nombre'] : '';
$aviso = $_SESSION['club_aviso'] ?? null;
unset($_SESSION['club_aviso']);

$pendiente = (int)($_SESSION['club_visita_pendiente'] ?? 0);
if ($pendiente && time() - $pendiente > 15 * 60) {
    unset($_SESSION['club_visita_pendiente']);
    $pendiente = 0;
}

$tarjeta = null;
try {
    // El cliente tocó la etiqueta sin sesión y acaba de entrar: guardamos ese sello.
    if ($usuarioId && $pendiente) {
        unset($_SESSION['club_visita_pendiente']);
        $pendiente = 0;
        $aviso = registrarVisita($usuarioId, 'nfc')
            ? ['sello', '¡Sello agregado! Gracias por tu visita.']
            : ['info', 'Tu visita de hoy ya estaba registrada.'];
    }
    $tarjeta = $usuarioId ? tarjetaCliente($usuarioId) : estadoTarjeta(0, 0, metaSellos()) + ['historial' => [], 'visitoHoy' => false];
} catch (PDOException $e) {
    error_log('FreshCuttz tarjeta: ' . $e->getMessage());
    $aviso = ['error', 'No pudimos cargar tu tarjeta. Intenta de nuevo en un momento.'];
}

$meta = $tarjeta['meta'] ?? SELLOS_META_DEFECTO;
$sellos = $tarjeta['sellos'] ?? 0;
$selloNuevo = ($aviso[0] ?? '') === 'sello';
// Si el sello nuevo completó la tarjeta, la vista muestra la tarjeta llena antes de reiniciar.
$tarjetaLlena = $selloNuevo && $sellos === 0 && ($tarjeta['visitas'] ?? 0) > 0;
$sellosVisibles = $tarjetaLlena ? $meta : $sellos;

$tituloPagina = 'Fresh Club · Fresh Cuttz';
$paginaActual = 'club';
$claseBody = 'pagina-club';
$hojasEstiloAdicionales = ['./styles/tarjeta.css?v=' . filemtime(__DIR__ . '/styles/tarjeta.css')];
require __DIR__ . '/includes/header.php';

function fechaVisita(string $dia): string
{
    $meses = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
    $marca = strtotime($dia);
    if ($dia === date('Y-m-d')) return 'Hoy';
    if ($dia === date('Y-m-d', strtotime('-1 day'))) return 'Ayer';
    return date('j', $marca) . ' ' . $meses[(int)date('n', $marca) - 1] . (date('Y', $marca) !== date('Y') ? ' ' . date('Y', $marca) : '');
}
?>
<main id="contenido" class="club contenedor">
    <header class="club-cabecera">
        <p class="club-antetitulo">Programa de lealtad</p>
        <h1>Fresh Club</h1>
        <p class="club-intro">Cada corte suma un sello. Junta <?= $meta ?> y el siguiente va por la casa.</p>
    </header>

    <?php if ($aviso): ?>
        <div class="club-aviso club-aviso-<?= e($aviso[0]) ?>" role="<?= $aviso[0] === 'error' ? 'alert' : 'status' ?>">
            <?= svgIcono($aviso[0] === 'error' ? 'error' : ($aviso[0] === 'sello' ? 'tijeras' : 'info')) ?>
            <p><?= e($aviso[1]) ?></p>
        </div>
    <?php elseif ($pendiente && !$usuarioId): ?>
        <div class="club-aviso club-aviso-sello" role="status">
            <?= svgIcono('tijeras') ?>
            <p>Detectamos tu visita. Crea tu cuenta o inicia sesión para guardar el sello de hoy.</p>
        </div>
    <?php endif; ?>

    <div class="club-rejilla">
        <article class="pase<?= $usuarioId ? '' : ' pase-vacio' ?>" aria-label="Tarjeta Fresh Club">
            <header class="pase-cabecera">
                <span class="pase-logo"><img src="./image/logo1.webp" width="259" height="192" alt=""></span>
                <span class="pase-negocio">Fresh Cuttz Barbershop</span>
                <span class="pase-nfc-insignia" aria-hidden="true">NFC</span>
            </header>

            <div class="pase-cuerpo">
                <p class="pase-titulo">Fresh <?= svgIcono('tijeras', 'pase-titulo-icono') ?> Club</p>
                <dl class="pase-totales">
                    <div>
                        <dt>Sellos</dt>
                        <dd class="t-num"><?= $sellosVisibles ?>/<?= $meta ?></dd>
                    </div>
                    <div>
                        <dt>Recompensas</dt>
                        <dd class="t-num"><?= (int)($tarjeta['recompensas'] ?? 0) ?></dd>
                    </div>
                </dl>

                <div class="pase-tap" aria-hidden="true">
                    <span class="pase-ondas"><span></span><span></span><span></span></span>
                    <?= svgIcono('telefono', 'pase-tap-icono') ?>
                </div>
                <p class="pase-tap-texto">Acerca tu celular a la etiqueta del mostrador</p>

                <p class="pase-nombre"><?= $usuarioId ? e($nombre) : 'Tu nombre aquí' ?></p>
            </div>

            <div class="pase-sellos">
                <ol class="sellos" style="--meta: <?= $meta ?>" aria-label="<?= $sellosVisibles ?> de <?= $meta ?> sellos">
                    <?php for ($i = 1; $i <= $meta; $i++):
                        $lleno = $i <= $sellosVisibles;
                        $clases = 'sello' . ($lleno ? ' sello-lleno' : '') . ($i === $meta ? ' sello-premio' : '')
                            . ($selloNuevo && $i === $sellosVisibles ? ' sello-nuevo' : ''); ?>
                        <li class="<?= $clases ?>">
                            <?php if ($lleno): ?>
                                <img class="sello-logo" src="./image/logo1.webp" width="259" height="192" alt="">
                            <?php elseif ($i === $meta): ?>
                                <span class="sello-gratis">Gratis</span>
                            <?php endif; ?>
                        </li>
                    <?php endfor; ?>
                </ol>
            </div>
        </article>

        <div class="club-lateral">
            <?php if (!$usuarioId): ?>
                <section class="club-bloque club-cta">
                    <h2>Empieza a juntar sellos</h2>
                    <p>Tu tarjeta vive en tu cuenta: no hay nada que imprimir ni perder.</p>
                    <button class="btn btn-primario btn-lg btn-bloque" type="button" data-abrir-login>Crear cuenta o entrar</button>
                </section>
            <?php elseif (($tarjeta['recompensas'] ?? 0) > 0): ?>
                <section class="club-bloque club-premio">
                    <?= svgIcono('estrella', 'icono-lg') ?>
                    <div>
                        <h2>Tienes <?= (int)$tarjeta['recompensas'] === 1 ? 'un corte gratis' : (int)$tarjeta['recompensas'] . ' cortes gratis' ?></h2>
                        <p>Pídelo en el mostrador en tu próxima visita.</p>
                    </div>
                </section>
            <?php endif; ?>

            <section class="club-bloque">
                <h2>Cómo funciona</h2>
                <ol class="pasos">
                    <li><span class="paso-num t-num">1</span><p><strong>Llega a tu corte</strong> y desbloquea tu celular.</p></li>
                    <li><span class="paso-num t-num">2</span><p><strong>Acércalo a la etiqueta NFC</strong> del mostrador. Se abre esta página y suma tu sello.</p></li>
                    <li><span class="paso-num t-num">3</span><p><strong>Al juntar <?= $meta ?></strong>, tu siguiente corte es gratis.</p></li>
                </ol>
                <p class="club-nota">¿Tu celular no tiene NFC? Pide en el mostrador que registren tu visita con tu teléfono. Un sello por día.</p>
            </section>

            <?php if ($usuarioId): ?>
                <section class="club-bloque">
                    <h2>Tus visitas</h2>
                    <?php if (!$tarjeta['historial']): ?>
                        <p class="club-nota">Aún no hay visitas. Tu primer sello llega en tu próximo corte.</p>
                    <?php else: ?>
                        <ul class="historial">
                            <?php foreach ($tarjeta['historial'] as $visita): ?>
                                <li>
                                    <span><?= e(fechaVisita($visita['dia'])) ?></span>
                                    <span class="historial-origen"><?= $visita['origen'] === 'nfc' ? 'Etiqueta NFC' : 'En mostrador' ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <p class="club-nota t-num"><?= (int)$tarjeta['visitas'] ?> <?= (int)$tarjeta['visitas'] === 1 ? 'visita' : 'visitas' ?> en total · <?= (int)$tarjeta['canjeadas'] ?> <?= (int)$tarjeta['canjeadas'] === 1 ? 'corte canjeado' : 'cortes canjeados' ?></p>
                    <?php endif; ?>
                </section>
            <?php endif; ?>
        </div>
    </div>
</main>
<script>
    document.querySelector('[data-abrir-login]')?.addEventListener('click', () => document.getElementById('abrir-perfil')?.click());
</script>
<?php require __DIR__ . '/includes/footer.php'; ?>

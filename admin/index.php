<?php
require_once __DIR__ . '/../includes/admin.php';
require_once __DIR__ . '/../includes/lealtad.php';

$admin = exigirAdmin();
$secciones = [
    'resumen' => ['Resumen', 'resumen'],
    'pedidos' => ['Pedidos', 'pedidos'],
    'citas' => ['Citas', 'citas'],
    'productos' => ['Productos', 'productos'],
    'cuentas' => ['Clientes', 'cuentas'],
    'club' => ['Fresh Club', 'tijeras'],
    'ajustes' => ['Ajustes', 'ajustes'],
];
$descripciones = [
    'resumen' => ucfirst(fechaLarga()),
    'pedidos' => 'Compras de productos y pagos de cortes. Marca cada pedido como pagado cuando recibas el dinero.',
    'citas' => 'Agenda de cortes reservados desde el sitio.',
    'productos' => 'Catálogo de la tienda, precios e inventario.',
    'cuentas' => 'Clientes que crearon una cuenta en el sitio.',
    'club' => 'Visitas y sellos de cada cliente. Cada visita suma un sello; al completar la tarjeta gana un corte gratis.',
    'ajustes' => 'Precio del servicio y accesos al panel.',
];
$seccion = isset($secciones[$_GET['s'] ?? '']) ? $_GET['s'] : 'resumen';
$aviso = tomarAviso();
$hoy = date('Y-m-d');
$db = conexion();
$pendientesMenu = (int)$db->query("SELECT COUNT(*) FROM pedidos WHERE estado = 'pendiente'")->fetchColumn();

function etiquetaEstado(string $estado): string
{
    return '<span class="insignia estado estado-' . e($estado) . '">' . e(ESTADOS_PEDIDO[$estado] ?? $estado) . '</span>';
}

function fechaCorta(string $fecha): string
{
    return date('d/m/Y', strtotime($fecha));
}

function horaCorta(string $hora): string
{
    return date('g:i a', strtotime($hora));
}

function campoOculto(string $accion, string $volver): string
{
    return campoCsrf() . '<input type="hidden" name="accion" value="' . e($accion) . '"><input type="hidden" name="volver" value="' . e($volver) . '">';
}

/** Selector de estado de un pedido. Los cancelados ya no cambian. */
function formularioEstado(array $pedido, string $volver): string
{
    if ($pedido['estado'] === 'cancelado') {
        return etiquetaEstado('cancelado');
    }
    $opciones = '';
    foreach (ESTADOS_PEDIDO as $valor => $texto) {
        $opciones .= '<option value="' . e($valor) . '"' . ($valor === $pedido['estado'] ? ' selected' : '') . '>' . e($texto) . '</option>';
    }
    $id = (int)$pedido['id'];
    return '<form class="cambio-estado" method="post" action="acciones.php" '
        . 'onsubmit="return this.estado.value !== \'cancelado\' || confirm(\'¿Cancelar el pedido #' . $id . '? Se libera el horario o se regresa el stock, y ya no se podrá cambiar.\')">'
        . campoOculto('pedido_estado', $volver)
        . '<input type="hidden" name="id" value="' . $id . '">'
        . '<label class="sr-only" for="estado-' . $id . '">Estado del pedido ' . $id . '</label>'
        . '<span class="selector estado-' . e($pedido['estado']) . '"><select id="estado-' . $id . '" name="estado" data-inicial="' . e($pedido['estado']) . '">' . $opciones . '</select>' . icono('abajo', 14) . '</span>'
        . '<button class="btn btn-contorno btn-sm" type="submit">Guardar</button></form>';
}

function vacio(string $titulo, string $texto, string $icono = 'buscar'): string
{
    return '<div class="vacio">' . icono($icono, 24) . '<strong>' . e($titulo) . '</strong><p>' . e($texto) . '</p></div>';
}

$version = fn(string $ruta): int => filemtime(__DIR__ . '/' . $ruta);
?>
<!DOCTYPE html>
<html lang="es" data-tema="claro">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title><?= e($secciones[$seccion][0]) ?> · Fresh Cuttz Admin</title>
    <link rel="preload" href="../fonts/archivo-latin.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="../styles/sistema.css?v=<?= $version('../styles/sistema.css') ?>">
    <link rel="stylesheet" href="admin.css?v=<?= $version('admin.css') ?>">
    <script src="../scripts/ui.js?v=<?= $version('../scripts/ui.js') ?>" defer></script>
</head>

<body>
    <a class="saltar-contenido" href="#contenido">Saltar al contenido</a>
    <div class="aplicacion">
        <aside class="lateral">
            <a class="marca" href="index.php">
                <img src="../image/logo1.webp" width="40" height="30" alt="">
                <span>Fresh Cuttz</span>
            </a>
            <nav class="navegacion" aria-label="Secciones del panel">
                <?php foreach ($secciones as $clave => [$titulo, $iconoSeccion]): ?>
                    <a href="?s=<?= e($clave) ?>" <?= $clave === $seccion ? 'aria-current="page"' : '' ?>>
                        <?= icono($iconoSeccion) ?>
                        <span><?= e($titulo) ?></span>
                        <?php if ($clave === 'pedidos' && $pendientesMenu > 0): ?>
                            <span class="contador" aria-label="<?= $pendientesMenu ?> pendientes"><?= $pendientesMenu ?></span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </nav>
            <div class="sesion">
                <a class="enlace-lateral" href="../index.php" target="_blank" rel="noopener"><?= icono('externo', 16) ?><span>Ver sitio</span></a>
                <div class="sesion-usuario">
                    <span class="avatar" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($admin['usuario'], 0, 1))) ?></span>
                    <span class="sesion-nombre"><?= e($admin['usuario']) ?></span>
                    <form method="post" action="acciones.php">
                        <?= campoOculto('salir', 'resumen') ?>
                        <button class="btn btn-fantasma btn-icono btn-sm" type="submit" aria-label="Cerrar sesión" title="Cerrar sesión"><?= icono('salir', 16) ?></button>
                    </form>
                </div>
            </div>
        </aside>

        <main id="contenido" class="principal" tabindex="-1">
            <header class="encabezado">
                <h1><?= e($secciones[$seccion][0]) ?></h1>
                <p><?= e($descripciones[$seccion]) ?></p>
            </header>

            <?php if ($aviso):
                $tipoAviso = $aviso['tipo'] === 'error' ? 'error' : 'exito'; ?>
                <div class="aviso aviso-<?= $tipoAviso ?> aviso-flash" data-aviso="<?= $tipoAviso ?>" role="<?= $tipoAviso === 'error' ? 'alert' : 'status' ?>">
                    <?= icono($tipoAviso === 'error' ? 'error' : 'listo') ?>
                    <p><?= e($aviso['mensaje']) ?></p>
                </div>
            <?php endif; ?>

            <?php if ($seccion === 'resumen'):
                $ventas = $db->query("SELECT COALESCE(SUM(total), 0) FROM pedidos WHERE estado IN ('pagado', 'completado')")->fetchColumn();
                $ventasMes = $db->prepare("SELECT COALESCE(SUM(total), 0) FROM pedidos WHERE estado IN ('pagado', 'completado') AND creado_en >= ?");
                $ventasMes->execute([date('Y-m-01')]);
                $ventasMes = $ventasMes->fetchColumn();
                $porCobrar = $db->query("SELECT COALESCE(SUM(total), 0) FROM pedidos WHERE estado = 'pendiente'")->fetchColumn();
                $citasHoy = $db->prepare('SELECT c.*, p.id AS pedido_id, p.cliente_nombre, p.cliente_telefono, p.estado FROM citas c JOIN pedidos p ON p.id = c.pedido_id WHERE c.fecha = ? AND c.cancelada = 0 ORDER BY c.hora');
                $citasHoy->execute([$hoy]);
                $citasHoy = $citasHoy->fetchAll();
                $proximaSemana = $db->prepare('SELECT COUNT(*) FROM citas WHERE fecha > ? AND fecha <= ? AND cancelada = 0');
                $proximaSemana->execute([$hoy, date('Y-m-d', strtotime('+7 days'))]);
                $cuentas = $db->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();
                $cuentasMes = $db->prepare('SELECT COUNT(*) FROM usuarios WHERE creado_en >= ?');
                $cuentasMes->execute([date('Y-m-01')]);
                $stockBajo = $db->query('SELECT nombre, stock FROM productos WHERE activo = 1 AND stock IS NOT NULL AND stock <= 3 ORDER BY stock')->fetchAll();
                $recientes = $db->query('SELECT * FROM pedidos ORDER BY id DESC LIMIT 6')->fetchAll();
            ?>
                <section class="indicadores" aria-label="Indicadores del negocio" data-escalonar>
                    <div class="indicador">
                        <p class="indicador-etiqueta">Ventas cobradas</p>
                        <p class="indicador-valor"><?= dinero($ventas) ?></p>
                        <p class="indicador-nota"><?= dinero($ventasMes) ?> este mes</p>
                    </div>
                    <a class="indicador" href="?s=pedidos&amp;estado=pendiente">
                        <p class="indicador-etiqueta">Por cobrar</p>
                        <p class="indicador-valor"><?= dinero($porCobrar) ?></p>
                        <p class="indicador-nota"><?= $pendientesMenu ?> <?= $pendientesMenu === 1 ? 'pedido pendiente' : 'pedidos pendientes' ?></p>
                    </a>
                    <a class="indicador" href="?s=citas">
                        <p class="indicador-etiqueta">Citas hoy</p>
                        <p class="indicador-valor"><?= count($citasHoy) ?></p>
                        <p class="indicador-nota"><?= (int)$proximaSemana->fetchColumn() ?> en los próximos 7 días</p>
                    </a>
                    <a class="indicador" href="?s=cuentas">
                        <p class="indicador-etiqueta">Clientes</p>
                        <p class="indicador-valor"><?= (int)$cuentas ?></p>
                        <?php $nuevos = (int)$cuentasMes->fetchColumn(); ?>
                        <p class="indicador-nota"><?= $nuevos === 1 ? '1 nuevo este mes' : "$nuevos nuevos este mes" ?></p>
                    </a>
                </section>

                <?php if ($stockBajo): ?>
                    <div class="aviso aviso-advertencia">
                        <?= icono('alerta') ?>
                        <p>Poco inventario: <?= e(implode(', ', array_map(fn($p) => "{$p['nombre']} ({$p['stock']})", $stockBajo))) ?>.
                            <a href="?s=productos">Actualizar inventario</a></p>
                    </div>
                <?php endif; ?>

                <div class="rejilla-doble">
                    <section class="tarjeta bloque">
                        <header class="bloque-encabezado">
                            <h2>Agenda de hoy</h2>
                            <a href="?s=citas">Ver agenda</a>
                        </header>
                        <?php if (!$citasHoy): ?>
                            <?= vacio('Sin citas para hoy', 'Las citas que reserven los clientes desde el sitio aparecerán aquí.', 'calendario') ?>
                        <?php else: ?>
                            <div class="desplazable"><table class="tabla tabla-compacta">
                                <thead>
                                    <tr>
                                        <th scope="col">Hora</th>
                                        <th scope="col">Cliente</th>
                                        <th scope="col">Barbero</th>
                                        <th scope="col">Estado</th>
                                    </tr>
                                </thead>
                                <tbody data-escalonar>
                                    <?php foreach ($citasHoy as $cita): ?>
                                        <tr>
                                            <td class="dato"><?= e(horaCorta($cita['hora'])) ?></td>
                                            <td><?= e($cita['cliente_nombre']) ?></td>
                                            <td class="secundario"><?= e($cita['barbero_nombre']) ?></td>
                                            <td><?= etiquetaEstado($cita['estado']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table></div>
                        <?php endif; ?>
                    </section>
                    <section class="tarjeta bloque">
                        <header class="bloque-encabezado">
                            <h2>Pedidos recientes</h2>
                            <a href="?s=pedidos">Ver todos</a>
                        </header>
                        <?php if (!$recientes): ?>
                            <?= vacio('Todavía no hay pedidos', 'Cuando un cliente compre productos o pague un corte, lo verás aquí.', 'pedidos') ?>
                        <?php else: ?>
                            <div class="desplazable"><table class="tabla tabla-compacta">
                                <thead>
                                    <tr>
                                        <th scope="col">Folio</th>
                                        <th scope="col">Cliente</th>
                                        <th scope="col" class="num">Total</th>
                                        <th scope="col">Estado</th>
                                    </tr>
                                </thead>
                                <tbody data-escalonar>
                                    <?php foreach ($recientes as $pedido): ?>
                                        <tr>
                                            <td class="dato">#<?= (int)$pedido['id'] ?></td>
                                            <td><?= e($pedido['cliente_nombre']) ?><span class="secundario"> · <?= $pedido['tipo'] === 'cita' ? 'Corte' : 'Productos' ?></span></td>
                                            <td class="num"><?= dinero($pedido['total']) ?></td>
                                            <td><?= etiquetaEstado($pedido['estado']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table></div>
                        <?php endif; ?>
                    </section>
                </div>

            <?php elseif ($seccion === 'pedidos'):
                $filtro = isset(ESTADOS_PEDIDO[$_GET['estado'] ?? '']) ? $_GET['estado'] : '';
                $conteos = $db->query('SELECT estado, COUNT(*) FROM pedidos GROUP BY estado')->fetchAll(PDO::FETCH_KEY_PAIR);
                $sql = "SELECT p.*,
                        (SELECT GROUP_CONCAT(CONCAT(pp.cantidad, ' × ', pp.nombre) SEPARATOR ', ') FROM pedido_productos pp WHERE pp.pedido_id = p.id) AS articulos,
                        c.barbero_nombre, c.fecha, c.hora
                    FROM pedidos p LEFT JOIN citas c ON c.pedido_id = p.id"
                    . ($filtro ? ' WHERE p.estado = ?' : '') . ' ORDER BY p.id DESC LIMIT 200';
                $pedidos = $db->prepare($sql);
                $pedidos->execute($filtro ? [$filtro] : []);
                $pedidos = $pedidos->fetchAll();
            ?>
                <nav class="pestanas" aria-label="Filtrar pedidos por estado">
                    <a href="?s=pedidos" <?= $filtro === '' ? 'aria-current="true"' : '' ?>>Todos <span class="pestana-conteo"><?= array_sum($conteos) ?></span></a>
                    <?php foreach (ESTADOS_PEDIDO as $valor => $texto): ?>
                        <a href="?s=pedidos&amp;estado=<?= e($valor) ?>" <?= $filtro === $valor ? 'aria-current="true"' : '' ?>><?= e($texto) ?> <span class="pestana-conteo"><?= (int)($conteos[$valor] ?? 0) ?></span></a>
                    <?php endforeach; ?>
                </nav>
                <?php if (!$pedidos): ?>
                    <?= $filtro ? vacio('Sin pedidos en este estado', 'Prueba con otro filtro o revisa todos los pedidos.', 'pedidos') : vacio('Todavía no hay pedidos', 'Cuando un cliente compre productos o pague un corte, lo verás aquí.', 'pedidos') ?>
                <?php else: ?>
                    <div class="tarjeta bloque bloque-tabla">
                        <div class="desplazable">
                            <table class="tabla">
                                <thead>
                                    <tr>
                                        <th scope="col">Folio</th>
                                        <th scope="col">Fecha</th>
                                        <th scope="col">Cliente</th>
                                        <th scope="col">Concepto</th>
                                        <th scope="col" class="num">Total</th>
                                        <th scope="col">Estado</th>
                                    </tr>
                                </thead>
                                <tbody data-escalonar>
                                    <?php foreach ($pedidos as $pedido): ?>
                                        <tr>
                                            <td class="dato">#<?= (int)$pedido['id'] ?></td>
                                            <td class="dato"><span class="principal-celda"><?= e(date('d/m/Y', strtotime($pedido['creado_en']))) ?></span><span class="secundario"><?= e(date('H:i', strtotime($pedido['creado_en']))) ?></span></td>
                                            <td><span class="principal-celda"><?= e($pedido['cliente_nombre']) ?></span><span class="secundario dato"><?= e($pedido['cliente_telefono']) ?><?= $pedido['usuario_id'] ? '' : ' · sin cuenta' ?></span></td>
                                            <td>
                                                <?php if ($pedido['tipo'] === 'cita'): ?>
                                                    <span class="principal-celda">Corte con <?= e($pedido['barbero_nombre']) ?></span><span class="secundario"><?= e(fechaCorta($pedido['fecha'])) ?>, <?= e(horaCorta($pedido['hora'])) ?></span>
                                                <?php else: ?>
                                                    <span class="principal-celda"><?= e($pedido['articulos']) ?></span><span class="secundario">Productos</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="num"><?= dinero($pedido['total']) ?><span class="secundario"><?= e(METODOS_PAGO[$pedido['metodo_pago']] ?? $pedido['metodo_pago']) ?></span></td>
                                            <td><?= formularioEstado($pedido, 'pedidos') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>

            <?php elseif ($seccion === 'citas'):
                $pasadas = ($_GET['vista'] ?? '') === 'pasadas';
                $citas = $db->prepare('SELECT c.*, p.id, p.cliente_nombre, p.cliente_telefono, p.estado, p.metodo_pago, p.total
                    FROM citas c JOIN pedidos p ON p.id = c.pedido_id
                    WHERE c.fecha ' . ($pasadas ? '<' : '>=') . ' ?
                    ORDER BY c.fecha ' . ($pasadas ? 'DESC' : 'ASC') . ', c.hora LIMIT 200');
                $citas->execute([$hoy]);
                $citas = $citas->fetchAll();
            ?>
                <nav class="pestanas" aria-label="Ver citas">
                    <a href="?s=citas" <?= !$pasadas ? 'aria-current="true"' : '' ?>>Próximas</a>
                    <a href="?s=citas&amp;vista=pasadas" <?= $pasadas ? 'aria-current="true"' : '' ?>>Pasadas</a>
                </nav>
                <?php if (!$citas): ?>
                    <?= $pasadas ? vacio('Sin citas pasadas', 'Aquí quedará el historial de cortes atendidos.', 'calendario') : vacio('Sin citas próximas', 'Las reservas que hagan los clientes desde el perfil de cada barbero aparecerán aquí.', 'calendario') ?>
                <?php else: ?>
                    <div class="tarjeta bloque bloque-tabla">
                        <div class="desplazable">
                            <table class="tabla">
                                <thead>
                                    <tr>
                                        <th scope="col">Fecha</th>
                                        <th scope="col">Hora</th>
                                        <th scope="col">Barbero</th>
                                        <th scope="col">Cliente</th>
                                        <th scope="col" class="num">Total</th>
                                        <th scope="col">Estado</th>
                                    </tr>
                                </thead>
                                <tbody data-escalonar>
                                    <?php foreach ($citas as $cita): ?>
                                        <tr class="<?= $cita['cancelada'] ? 'fila-inactiva' : '' ?>">
                                            <td class="dato"><?= e(fechaCorta($cita['fecha'])) ?><?= $cita['fecha'] === $hoy ? ' <span class="insignia insignia-primario">Hoy</span>' : '' ?></td>
                                            <td class="dato"><?= e(horaCorta($cita['hora'])) ?></td>
                                            <td><?= e($cita['barbero_nombre']) ?></td>
                                            <td><span class="principal-celda"><?= e($cita['cliente_nombre']) ?></span><span class="secundario dato"><?= e($cita['cliente_telefono']) ?> · folio #<?= (int)$cita['id'] ?></span></td>
                                            <td class="num"><?= dinero($cita['total']) ?><span class="secundario"><?= e(METODOS_PAGO[$cita['metodo_pago']] ?? '') ?></span></td>
                                            <td><?= formularioEstado($cita, 'citas') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>

            <?php elseif ($seccion === 'productos'):
                $productos = $db->query('SELECT * FROM productos ORDER BY activo DESC, nombre')->fetchAll();
            ?>
                <details class="tarjeta bloque desplegable" <?= $productos ? '' : 'open' ?>>
                    <summary>
                        <span class="desplegable-titulo"><?= icono('mas') ?>Agregar producto</span>
                        <?= icono('abajo', 16) ?>
                    </summary>
                    <form class="formulario-alta" method="post" action="acciones.php" enctype="multipart/form-data">
                        <?= campoOculto('producto_crear', 'productos') ?>
                        <div class="campo">
                            <label for="alta-nombre">Nombre</label>
                            <input id="alta-nombre" name="nombre" type="text" minlength="2" maxlength="100" required>
                        </div>
                        <div class="campo">
                            <label for="alta-precio">Precio (MXN)</label>
                            <input id="alta-precio" name="precio" type="number" min="0" step="0.01" inputmode="decimal" required>
                        </div>
                        <div class="campo">
                            <label for="alta-stock">Inventario</label>
                            <input id="alta-stock" name="stock" type="number" min="0" step="1" placeholder="Sin límite">
                        </div>
                        <div class="campo campo-archivo">
                            <label for="alta-foto">Foto</label>
                            <input id="alta-foto" name="foto" type="file" accept="image/png,image/jpeg,image/webp" required aria-describedby="alta-ayuda">
                        </div>
                        <label class="interruptor"><input name="activo" type="checkbox" checked><span>Visible en la tienda</span></label>
                        <div class="formulario-pie">
                            <p class="campo-ayuda" id="alta-ayuda">PNG, JPG o WEBP de hasta 3 MB. Deja el inventario vacío si no llevas conteo.</p>
                            <button class="btn btn-primario" type="submit">Agregar producto</button>
                        </div>
                    </form>
                </details>

                <?php if (!$productos): ?>
                    <?= vacio('El catálogo está vacío', 'Agrega tu primer producto con el formulario de arriba para que aparezca en la tienda.', 'productos') ?>
                <?php else: ?>
                    <div class="tarjeta bloque bloque-tabla">
                        <div class="desplazable">
                            <table class="tabla tabla-edicion">
                                <thead>
                                    <tr>
                                        <th scope="col">Producto</th>
                                        <th scope="col">Precio</th>
                                        <th scope="col">Inventario</th>
                                        <th scope="col">Visible</th>
                                        <th scope="col">Nueva foto</th>
                                        <th scope="col"><span class="sr-only">Acciones</span></th>
                                    </tr>
                                </thead>
                                <tbody data-escalonar>
                                    <?php foreach ($productos as $producto): $f = 'producto-' . (int)$producto['id']; ?>
                                        <tr class="<?= $producto['activo'] ? '' : 'fila-inactiva' ?>">
                                            <td>
                                                <div class="producto-celda">
                                                    <img class="miniatura" src="../<?= e(ltrim($producto['foto'], './')) ?>" width="44" height="44" alt="">
                                                    <input class="control control-compacto" form="<?= $f ?>" name="nombre" type="text" value="<?= e($producto['nombre']) ?>" minlength="2" maxlength="100" required aria-label="Nombre del producto">
                                                </div>
                                            </td>
                                            <td><span class="con-prefijo"><span aria-hidden="true">$</span><input class="control control-compacto" form="<?= $f ?>" name="precio" type="number" min="0" step="0.01" value="<?= e($producto['precio']) ?>" required aria-label="Precio de <?= e($producto['nombre']) ?>"></span></td>
                                            <td><input class="control control-compacto campo-corto" form="<?= $f ?>" name="stock" type="number" min="0" step="1" value="<?= e($producto['stock'] ?? '') ?>" placeholder="Sin límite" aria-label="Inventario de <?= e($producto['nombre']) ?>"></td>
                                            <td><label class="interruptor"><input form="<?= $f ?>" name="activo" type="checkbox" <?= $producto['activo'] ? 'checked' : '' ?>><span class="sr-only">Visible en la tienda</span></label></td>
                                            <td><input class="archivo-compacto" form="<?= $f ?>" name="foto" type="file" accept="image/png,image/jpeg,image/webp" aria-label="Nueva foto de <?= e($producto['nombre']) ?>"></td>
                                            <td class="acciones">
                                                <form id="<?= $f ?>" method="post" action="acciones.php" enctype="multipart/form-data">
                                                    <?= campoOculto('producto_editar', 'productos') ?>
                                                    <input type="hidden" name="id" value="<?= (int)$producto['id'] ?>">
                                                    <button class="btn btn-contorno btn-sm" type="submit">Guardar</button>
                                                </form>
                                                <form method="post" action="acciones.php" onsubmit="return confirm('¿Eliminar «<?= e(addslashes($producto['nombre'])) ?>»? Si solo quieres ocultarlo, desactiva «Visible».')">
                                                    <?= campoOculto('producto_eliminar', 'productos') ?>
                                                    <input type="hidden" name="id" value="<?= (int)$producto['id'] ?>">
                                                    <button class="btn btn-fantasma btn-icono btn-sm accion-peligro" type="submit" aria-label="Eliminar <?= e($producto['nombre']) ?>" title="Eliminar"><?= icono('borrar', 16) ?></button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>

            <?php elseif ($seccion === 'cuentas'):
                $busqueda = trim((string)($_GET['q'] ?? ''));
                $sql = 'SELECT u.*, (SELECT COUNT(*) FROM pedidos p WHERE p.usuario_id = u.id) AS pedidos FROM usuarios u';
                $parametros = [];
                if ($busqueda !== '') {
                    $sql .= ' WHERE u.nombre LIKE ? OR u.telefono LIKE ?';
                    $parametros = ["%$busqueda%", "%$busqueda%"];
                }
                $usuarios = $db->prepare($sql . ' ORDER BY u.id DESC LIMIT 300');
                $usuarios->execute($parametros);
                $usuarios = $usuarios->fetchAll();
            ?>
                <form class="buscador" method="get" role="search">
                    <input type="hidden" name="s" value="cuentas">
                    <label class="sr-only" for="q">Buscar cliente</label>
                    <span class="buscador-campo"><?= icono('buscar', 16) ?><input class="control" id="q" name="q" type="search" value="<?= e($busqueda) ?>" placeholder="Buscar por nombre o teléfono"></span>
                    <button class="btn btn-secundario" type="submit">Buscar</button>
                </form>
                <?php if (!$usuarios): ?>
                    <?= $busqueda !== '' ? vacio('Sin resultados', 'Ningún cliente coincide con «' . $busqueda . '». Revisa la ortografía o busca por teléfono.', 'buscar') : vacio('Todavía no hay clientes', 'Las cuentas que se creen desde el sitio aparecerán aquí.', 'cuentas') ?>
                <?php else: ?>
                    <div class="tarjeta bloque bloque-tabla">
                        <div class="desplazable">
                            <table class="tabla">
                                <thead>
                                    <tr>
                                        <th scope="col">Cliente</th>
                                        <th scope="col">Teléfono</th>
                                        <th scope="col">Alta</th>
                                        <th scope="col" class="num">Pedidos</th>
                                        <th scope="col"><span class="sr-only">Acciones</span></th>
                                    </tr>
                                </thead>
                                <tbody data-escalonar>
                                    <?php foreach ($usuarios as $usuario): ?>
                                        <tr>
                                            <td>
                                                <span class="cliente-celda">
                                                    <span class="avatar avatar-chico" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($usuario['nombre'], 0, 1))) ?></span>
                                                    <?= e($usuario['nombre']) ?>
                                                </span>
                                            </td>
                                            <td class="dato"><?= e($usuario['telefono']) ?></td>
                                            <td class="dato secundario"><?= e(fechaCorta($usuario['creado_en'])) ?></td>
                                            <td class="num"><?= (int)$usuario['pedidos'] ?></td>
                                            <td class="acciones">
                                                <form method="post" action="acciones.php" onsubmit="return confirm('¿Eliminar la cuenta de <?= e(addslashes($usuario['nombre'])) ?>? Sus pedidos se conservan en el historial.')">
                                                    <?= campoOculto('usuario_eliminar', 'cuentas') ?>
                                                    <input type="hidden" name="id" value="<?= (int)$usuario['id'] ?>">
                                                    <button class="btn btn-fantasma btn-icono btn-sm accion-peligro" type="submit" aria-label="Eliminar la cuenta de <?= e($usuario['nombre']) ?>" title="Eliminar cuenta"><?= icono('borrar', 16) ?></button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>

            <?php elseif ($seccion === 'club'):
                $meta = metaSellos();
                $busqueda = trim((string)($_GET['q'] ?? ''));
                $sql = 'SELECT u.id, u.nombre, u.telefono,
                               COUNT(v.id) AS visitas,
                               MAX(v.dia) AS ultima,
                               COALESCE(SUM(v.dia = CURDATE()), 0) AS hoy,
                               (SELECT COUNT(*) FROM canjes c WHERE c.usuario_id = u.id) AS canjes
                        FROM usuarios u LEFT JOIN visitas v ON v.usuario_id = u.id';
                $parametros = [];
                if ($busqueda !== '') {
                    $sql .= ' WHERE u.nombre LIKE ? OR u.telefono LIKE ?';
                    $parametros = ["%$busqueda%", "%$busqueda%"];
                }
                $clientes = $db->prepare($sql . ' GROUP BY u.id ORDER BY MAX(v.dia) IS NULL, MAX(v.dia) DESC, u.nombre LIMIT 300');
                $clientes->execute($parametros);
                $clientes = $clientes->fetchAll();

                $visitasHoy = (int)$db->query('SELECT COUNT(*) FROM visitas WHERE dia = CURDATE()')->fetchColumn();
                $visitasMes = $db->prepare('SELECT COUNT(*) FROM visitas WHERE dia >= ?');
                $visitasMes->execute([date('Y-m-01')]);
                $visitasMes = (int)$visitasMes->fetchColumn();
                $socios = (int)$db->query('SELECT COUNT(DISTINCT usuario_id) FROM visitas')->fetchColumn();
                $porEntregar = (int)$db->query(
                    "SELECT COALESCE(SUM(GREATEST(FLOOR(t.visitas / $meta) - t.canjes, 0)), 0) FROM (
                        SELECT u.id, (SELECT COUNT(*) FROM visitas v WHERE v.usuario_id = u.id) AS visitas,
                               (SELECT COUNT(*) FROM canjes c WHERE c.usuario_id = u.id) AS canjes
                        FROM usuarios u) t"
                )->fetchColumn();

                // Link que se graba en el chip: mismo dominio y carpeta con los que se abrió el panel.
                $https = ($_SERVER['HTTPS'] ?? '') === 'on' || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
                $carpeta = rtrim(str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME']))), '/');
                $host = (string)($_SERVER['HTTP_HOST'] ?? 'localhost');
                $linkNfc = ($https ? 'https' : 'http') . '://' . $host . $carpeta . '/visita.php?t=' . tokenNfc();
                $esLocal = (bool)preg_match('/^(localhost|127\.0\.0\.1|\[::1\])(:\d+)?$/', $host);
            ?>
                <section class="indicadores" aria-label="Indicadores de Fresh Club" data-escalonar>
                    <div class="indicador">
                        <p class="indicador-etiqueta">Visitas hoy</p>
                        <p class="indicador-valor"><?= $visitasHoy ?></p>
                        <p class="indicador-nota">Una por cliente al día</p>
                    </div>
                    <div class="indicador">
                        <p class="indicador-etiqueta">Visitas este mes</p>
                        <p class="indicador-valor"><?= $visitasMes ?></p>
                        <p class="indicador-nota">Desde el 1 de <?= e(explode(' de ', fechaLarga())[1]) ?></p>
                    </div>
                    <div class="indicador">
                        <p class="indicador-etiqueta">Cortes gratis por entregar</p>
                        <p class="indicador-valor"><?= $porEntregar ?></p>
                        <p class="indicador-nota">Tarjetas de <?= $meta ?> sellos</p>
                    </div>
                    <div class="indicador">
                        <p class="indicador-etiqueta">Clientes con visitas</p>
                        <p class="indicador-valor"><?= $socios ?></p>
                        <p class="indicador-nota">Al menos un sello</p>
                    </div>
                </section>

                <form class="buscador" method="get" role="search">
                    <input type="hidden" name="s" value="club">
                    <label class="sr-only" for="q">Buscar cliente</label>
                    <span class="buscador-campo"><?= icono('buscar', 16) ?><input class="control" id="q" name="q" type="search" value="<?= e($busqueda) ?>" placeholder="Buscar por nombre o teléfono"></span>
                    <button class="btn btn-secundario" type="submit">Buscar</button>
                </form>

                <?php if (!$clientes): ?>
                    <?= $busqueda !== '' ? vacio('Sin resultados', 'Ningún cliente coincide con «' . $busqueda . '».', 'buscar') : vacio('Todavía no hay clientes', 'Cuando un cliente cree su cuenta y toque el chip NFC, verás sus visitas aquí.', 'cuentas') ?>
                <?php else: ?>
                    <div class="tarjeta bloque bloque-tabla">
                        <div class="desplazable">
                            <table class="tabla">
                                <thead>
                                    <tr>
                                        <th scope="col">Cliente</th>
                                        <th scope="col" class="num">Visitas</th>
                                        <th scope="col">Tarjeta actual</th>
                                        <th scope="col" class="num">Cortes gratis</th>
                                        <th scope="col">Última visita</th>
                                        <th scope="col"><span class="sr-only">Acciones</span></th>
                                    </tr>
                                </thead>
                                <tbody data-escalonar>
                                    <?php foreach ($clientes as $cliente):
                                        $estado = estadoTarjeta((int)$cliente['visitas'], (int)$cliente['canjes'], $meta);
                                        $idCliente = (int)$cliente['id']; ?>
                                        <tr>
                                            <td>
                                                <span class="cliente-celda">
                                                    <span class="avatar avatar-chico" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($cliente['nombre'], 0, 1))) ?></span>
                                                    <span>
                                                        <span class="principal-celda"><?= e($cliente['nombre']) ?></span>
                                                        <span class="secundario dato"><?= e($cliente['telefono']) ?></span>
                                                    </span>
                                                </span>
                                            </td>
                                            <td class="num"><?= $estado['visitas'] ?></td>
                                            <td>
                                                <span class="progreso-sellos" title="<?= $estado['sellos'] ?> de <?= $meta ?> sellos">
                                                    <span class="barra-sellos" style="--avance: <?= round($estado['sellos'] / $meta, 3) ?>" aria-hidden="true"><span></span></span>
                                                    <span class="dato"><?= $estado['sellos'] ?>/<?= $meta ?></span>
                                                </span>
                                            </td>
                                            <td class="num">
                                                <?php if ($estado['recompensas'] > 0): ?>
                                                    <span class="insignia insignia-primario"><?= $estado['recompensas'] ?> por entregar</span>
                                                <?php else: ?>
                                                    <span class="secundario">—</span>
                                                <?php endif; ?>
                                                <?php if ($estado['canjeadas'] > 0): ?><span class="secundario"><?= $estado['canjeadas'] ?> canjeado<?= $estado['canjeadas'] === 1 ? '' : 's' ?></span><?php endif; ?>
                                            </td>
                                            <td class="dato secundario"><?= $cliente['ultima'] ? ((int)$cliente['hoy'] ? 'Hoy' : e(fechaCorta($cliente['ultima']))) : 'Sin visitas' ?></td>
                                            <td class="acciones">
                                                <?php if ($estado['recompensas'] > 0): ?>
                                                    <form method="post" action="acciones.php" onsubmit="return confirm('¿Entregar un corte gratis a <?= e(addslashes($cliente['nombre'])) ?>?')">
                                                        <?= campoOculto('club_canjear', 'club') ?>
                                                        <input type="hidden" name="id" value="<?= $idCliente ?>">
                                                        <button class="btn btn-primario btn-sm" type="submit"><?= icono('estrella', 14) ?>Canjear</button>
                                                    </form>
                                                <?php endif; ?>
                                                <?php if ((int)$cliente['hoy']): ?>
                                                    <span class="insignia insignia-exito"><?= icono('check', 14) ?>Vino hoy</span>
                                                <?php else: ?>
                                                    <form method="post" action="acciones.php">
                                                        <?= campoOculto('club_sello', 'club') ?>
                                                        <input type="hidden" name="id" value="<?= $idCliente ?>">
                                                        <button class="btn btn-contorno btn-sm" type="submit" aria-label="Sumar sello a <?= e($cliente['nombre']) ?>"><?= icono('mas', 14) ?>Sello</button>
                                                    </form>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="ajustes club-ajustes">
                    <section class="ajuste">
                        <div class="ajuste-descripcion">
                            <h2>Chip NFC del mostrador</h2>
                            <p>Graba este link en la etiqueta con la app NFC Tools (Escribir → URL). Al acercar el celular, el cliente suma su sello del día.</p>
                        </div>
                        <div class="tarjeta bloque formulario-ajuste">
                            <?php if ($esLocal): ?>
                                <div class="aviso aviso-advertencia">
                                    <?= icono('alerta') ?>
                                    <p>Abriste el panel desde <strong><?= e($host) ?></strong>: un celular no puede abrir ese link. Entra al panel con la IP de tu Mac (por ejemplo http://10.1.125.63/FreshCuttz/admin/) o desde el sitio publicado.</p>
                                </div>
                            <?php endif; ?>
                            <div class="campo">
                                <label for="link-nfc">Link para el chip</label>
                                <div class="link-nfc">
                                    <input class="dato" id="link-nfc" type="text" value="<?= e($linkNfc) ?>" readonly>
                                    <button class="btn btn-secundario" type="button" data-copiar="link-nfc"><?= icono('check', 16) ?>Copiar</button>
                                </div>
                            </div>
                            <div class="formulario-pie">
                                <a class="btn btn-fantasma" href="<?= e($linkNfc) ?>" target="_blank" rel="noopener"><?= icono('externo', 16) ?>Probar link</a>
                                <form method="post" action="acciones.php" onsubmit="return confirm('¿Renovar el link? El chip actual dejará de sumar sellos hasta que grabes el nuevo.')">
                                    <?= campoOculto('club_token', 'club') ?>
                                    <button class="btn btn-contorno accion-peligro" type="submit">Renovar link</button>
                                </form>
                            </div>
                        </div>
                    </section>

                    <section class="ajuste">
                        <div class="ajuste-descripcion">
                            <h2>Sellos para un corte gratis</h2>
                            <p>Al cambiarla se recalculan las tarjetas de todos los clientes con sus visitas actuales.</p>
                        </div>
                        <form class="tarjeta bloque formulario-ajuste" method="post" action="acciones.php">
                            <?= campoOculto('club_meta', 'club') ?>
                            <div class="campo">
                                <label for="meta-sellos">Sellos (<?= SELLOS_META_MIN ?> a <?= SELLOS_META_MAX ?>)</label>
                                <input class="campo-corto" id="meta-sellos" name="meta" type="number" min="<?= SELLOS_META_MIN ?>" max="<?= SELLOS_META_MAX ?>" value="<?= $meta ?>" required>
                            </div>
                            <div class="formulario-pie"><button class="btn btn-primario" type="submit">Guardar meta</button></div>
                        </form>
                    </section>
                </div>

            <?php elseif ($seccion === 'ajustes'):
                $admins = $db->query('SELECT id, usuario, creado_en FROM administradores ORDER BY usuario')->fetchAll();
            ?>
                <div class="ajustes">
                    <section class="ajuste">
                        <div class="ajuste-descripcion">
                            <h2>Precio del corte</h2>
                            <p>Se aplica a las citas nuevas. Las que ya están reservadas conservan su precio.</p>
                        </div>
                        <form class="tarjeta bloque formulario-ajuste" method="post" action="acciones.php">
                            <?= campoOculto('precio_corte', 'ajustes') ?>
                            <div class="campo">
                                <label for="precio-corte">Precio (MXN)</label>
                                <span class="con-prefijo"><span aria-hidden="true">$</span><input id="precio-corte" name="precio" type="number" min="0" step="0.01" value="<?= e(precioCorte()) ?>" required></span>
                            </div>
                            <div class="formulario-pie"><button class="btn btn-primario" type="submit">Guardar precio</button></div>
                        </form>
                    </section>

                    <section class="ajuste">
                        <div class="ajuste-descripcion">
                            <h2>Tu contraseña</h2>
                            <p>Usa al menos 8 caracteres. Cerrar sesión no es necesario después de cambiarla.</p>
                        </div>
                        <form class="tarjeta bloque formulario-ajuste" method="post" action="acciones.php">
                            <?= campoOculto('admin_clave', 'ajustes') ?>
                            <div class="campo">
                                <label for="clave-actual">Contraseña actual</label>
                                <input id="clave-actual" name="actual" type="password" autocomplete="current-password" required>
                            </div>
                            <div class="campo">
                                <label for="clave-nueva">Nueva contraseña</label>
                                <input id="clave-nueva" name="nueva" type="password" autocomplete="new-password" minlength="8" required>
                            </div>
                            <div class="campo">
                                <label for="clave-confirmar">Repite la nueva contraseña</label>
                                <input id="clave-confirmar" name="confirmar" type="password" autocomplete="new-password" minlength="8" required>
                            </div>
                            <div class="formulario-pie"><button class="btn btn-primario" type="submit">Cambiar contraseña</button></div>
                        </form>
                    </section>

                    <section class="ajuste">
                        <div class="ajuste-descripcion">
                            <h2>Administradores</h2>
                            <p>Personas con acceso a este panel. Cada una entra con su propio usuario.</p>
                        </div>
                        <div class="tarjeta bloque">
                            <ul class="lista-admins" data-escalonar>
                                <?php foreach ($admins as $cuenta): ?>
                                    <li>
                                        <span class="avatar avatar-chico" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($cuenta['usuario'], 0, 1))) ?></span>
                                        <span class="lista-admins-nombre"><?= e($cuenta['usuario']) ?><?= (int)$cuenta['id'] === $admin['id'] ? ' <span class="insignia insignia-primario">Tú</span>' : '' ?></span>
                                        <span class="secundario dato">Desde <?= e(fechaCorta($cuenta['creado_en'])) ?></span>
                                        <?php if ((int)$cuenta['id'] !== $admin['id']): ?>
                                            <form method="post" action="acciones.php" onsubmit="return confirm('¿Quitar el acceso de <?= e(addslashes($cuenta['usuario'])) ?>?')">
                                                <?= campoOculto('admin_eliminar', 'ajustes') ?>
                                                <input type="hidden" name="id" value="<?= (int)$cuenta['id'] ?>">
                                                <button class="btn btn-fantasma btn-icono btn-sm accion-peligro" type="submit" aria-label="Quitar acceso a <?= e($cuenta['usuario']) ?>" title="Quitar acceso"><?= icono('borrar', 16) ?></button>
                                            </form>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <form class="formulario-en-linea" method="post" action="acciones.php">
                                <?= campoOculto('admin_crear', 'ajustes') ?>
                                <div class="campo">
                                    <label for="nuevo-admin">Usuario</label>
                                    <input id="nuevo-admin" name="usuario" type="text" pattern="[A-Za-z0-9_.\-]{3,50}" autocomplete="off" required>
                                </div>
                                <div class="campo">
                                    <label for="nuevo-admin-clave">Contraseña</label>
                                    <input id="nuevo-admin-clave" name="clave" type="password" minlength="8" autocomplete="new-password" required>
                                </div>
                                <button class="btn btn-secundario" type="submit"><?= icono('mas', 16) ?>Agregar</button>
                            </form>
                        </div>
                    </section>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
        // Panel: aviso como toast, aparición escalonada, color del selector de estado y carga al enviar.
        document.addEventListener('DOMContentLoaded', () => {
            if (!window.ui) return; // sin ui.js el aviso queda visible como bloque

            const aviso = document.querySelector('[data-aviso]');
            if (aviso) {
                const tipo = aviso.dataset.aviso;
                ui.toast(aviso.textContent.trim(), tipo, tipo === 'error' ? 7000 : 4500);
                aviso.classList.add('sr-only'); // el texto sigue disponible para lectores de pantalla
            }

            document.querySelectorAll('[data-escalonar]').forEach((contenedor) => ui.escalonar(contenedor));

            document.querySelectorAll('[data-copiar]').forEach((boton) => {
                boton.addEventListener('click', async () => {
                    const campo = document.getElementById(boton.dataset.copiar);
                    try {
                        await navigator.clipboard.writeText(campo.value);
                        ui.toast('Link copiado.', 'exito');
                    } catch {
                        campo.select(); // sin permiso de portapapeles (http): queda seleccionado para copiar a mano
                        ui.toast('Selecciona el link y cópialo con Cmd+C.', 'info');
                    }
                });
            });

            document.querySelectorAll('.cambio-estado select').forEach((selector) => {
                selector.addEventListener('change', () => {
                    const envoltura = selector.closest('.selector');
                    envoltura.className = 'selector estado-' + selector.value;
                    selector.closest('form').classList.toggle('tiene-cambios', selector.value !== selector.dataset.inicial);
                });
            });
        });

        document.addEventListener('submit', (evento) => {
            if (evento.defaultPrevented || !window.ui) return;
            const boton = evento.submitter;
            if (boton && boton.classList.contains('btn')) ui.cargando(boton, true);
        });

        window.addEventListener('pageshow', (evento) => {
            if (evento.persisted && window.ui) document.querySelectorAll('.btn.esta-cargando').forEach((boton) => ui.cargando(boton, false));
        });
    </script>
</body>

</html>

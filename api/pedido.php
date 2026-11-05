<?php
// Registra compras de productos y pagos de citas de corte.
require_once __DIR__ . '/../includes/sesion.php';
abrirSesion();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/negocio.php';

function responder(int $codigo, array $datos): void
{
    http_response_code($codigo);
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(405, ['error' => 'Método no permitido.']);
}

$entrada = json_decode(file_get_contents('php://input'), true) ?: [];
$tipo = $entrada['tipo'] ?? '';
$metodo = $entrada['metodo'] ?? '';
$usuarioId = isset($_SESSION['usuario_id']) ? (int)$_SESSION['usuario_id'] : null;

if (!isset(METODOS_PAGO[$metodo])) {
    responder(422, ['error' => 'Selecciona un método de pago válido.']);
}

try {
    $db = conexion();

    if ($tipo === 'productos') {
        if ($usuarioId === null) {
            responder(401, ['error' => 'Inicia sesión o crea una cuenta para comprar productos.', 'requiereLogin' => true]);
        }
        $cliente = $db->prepare('SELECT nombre, telefono FROM usuarios WHERE id = ?');
        $cliente->execute([$usuarioId]);
        $cliente = $cliente->fetch();
        if (!$cliente) {
            unset($_SESSION['usuario_id'], $_SESSION['usuario_nombre']);
            responder(401, ['error' => 'Tu cuenta ya no existe. Crea una nueva para comprar.', 'requiereLogin' => true]);
        }

        // Juntamos cantidades por producto y validamos lo que manda el navegador.
        $pedidas = [];
        foreach (is_array($entrada['articulos'] ?? null) ? $entrada['articulos'] : [] as $articulo) {
            $id = filter_var($articulo['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            $cantidad = filter_var($articulo['cantidad'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 99]]);
            if ($id === false || $cantidad === false) {
                responder(422, ['error' => 'El carrito tiene datos no válidos. Recarga la página.']);
            }
            $pedidas[$id] = ($pedidas[$id] ?? 0) + $cantidad;
        }
        if (!$pedidas || count($pedidas) > 50) {
            responder(422, ['error' => 'Tu carrito está vacío.']);
        }

        $db->beginTransaction();
        // Los precios y el stock salen de la base, nunca del navegador.
        $marcadores = implode(',', array_fill(0, count($pedidas), '?'));
        $consulta = $db->prepare("SELECT id, nombre, precio, stock FROM productos WHERE activo = 1 AND id IN ($marcadores) FOR UPDATE");
        $consulta->execute(array_keys($pedidas));
        $productos = array_column($consulta->fetchAll(), null, 'id');

        $totalCentavos = 0;
        foreach ($pedidas as $id => $cantidad) {
            $producto = $productos[$id] ?? null;
            if (!$producto) {
                $db->rollBack();
                responder(409, ['error' => 'Un producto de tu carrito ya no está disponible. Quítalo e intenta de nuevo.']);
            }
            if ($producto['stock'] !== null && $cantidad > (int)$producto['stock']) {
                $db->rollBack();
                $quedan = (int)$producto['stock'];
                responder(409, ['error' => $quedan === 0
                    ? "{$producto['nombre']} se agotó."
                    : "Solo quedan $quedan de {$producto['nombre']}."]);
            }
            $totalCentavos += (int)round($producto['precio'] * 100) * $cantidad;
        }

        $db->prepare('INSERT INTO pedidos (usuario_id, cliente_nombre, cliente_telefono, tipo, total, metodo_pago) VALUES (?, ?, ?, ?, ?, ?)')
            ->execute([$usuarioId, $cliente['nombre'], $cliente['telefono'], 'productos', $totalCentavos / 100, $metodo]);
        $pedidoId = (int)$db->lastInsertId();

        $item = $db->prepare('INSERT INTO pedido_productos (pedido_id, producto_id, nombre, precio_unitario, cantidad) VALUES (?, ?, ?, ?, ?)');
        $descontar = $db->prepare('UPDATE productos SET stock = stock - ? WHERE id = ? AND stock IS NOT NULL');
        foreach ($pedidas as $id => $cantidad) {
            $item->execute([$pedidoId, $id, $productos[$id]['nombre'], $productos[$id]['precio'], $cantidad]);
            $descontar->execute([$cantidad, $id]);
        }
        $db->commit();

        responder(201, ['pedido' => ['id' => $pedidoId, 'total' => $totalCentavos / 100, 'metodo' => METODOS_PAGO[$metodo]]]);
    }

    if ($tipo === 'cita') {
        $barberoId = filter_var($entrada['barbero_id'] ?? null, FILTER_VALIDATE_INT);
        $nombre = nombreValido($entrada['nombre'] ?? '');
        $telefono = telefonoValido($entrada['telefono'] ?? '');
        $fechaTexto = (string)($entrada['fecha'] ?? '');
        $hora = (string)($entrada['hora'] ?? '');
        $fecha = DateTime::createFromFormat('!Y-m-d', $fechaTexto);

        if ($barberoId === false || !isset(BARBEROS[$barberoId])) {
            responder(422, ['error' => 'Selecciona un barbero válido.']);
        }
        if ($nombre === null) {
            responder(422, ['error' => 'Revisa tu nombre: solo letras, entre 2 y 100 caracteres.']);
        }
        if ($telefono === null) {
            responder(422, ['error' => 'Revisa tu teléfono: deben ser 10 números.']);
        }
        if (!$fecha || $fecha->format('Y-m-d') !== $fechaTexto || !in_array($hora, horasCita(), true)) {
            responder(422, ['error' => 'Selecciona una fecha y un horario válidos.']);
        }
        if (!in_array((int)$fecha->format('w'), DIAS_ABIERTOS, true)) {
            responder(422, ['error' => 'El local está cerrado ese día.']);
        }
        if (new DateTime("$fechaTexto $hora") <= new DateTime()) {
            responder(422, ['error' => 'Selecciona una fecha y hora futuras.']);
        }

        $precio = precioCorte();
        $db->beginTransaction();
        try {
            $db->prepare('INSERT INTO pedidos (usuario_id, cliente_nombre, cliente_telefono, tipo, total, metodo_pago) VALUES (?, ?, ?, ?, ?, ?)')
                ->execute([$usuarioId, $nombre, $telefono, 'cita', $precio, $metodo]);
            $pedidoId = (int)$db->lastInsertId();
            $db->prepare('INSERT INTO citas (pedido_id, barbero_id, barbero_nombre, fecha, hora) VALUES (?, ?, ?, ?, ?)')
                ->execute([$pedidoId, $barberoId, BARBEROS[$barberoId], $fechaTexto, $hora]);
            $db->commit();
        } catch (PDOException $e) {
            $db->rollBack();
            if ($e->getCode() === '23000') {
                responder(409, ['error' => 'Ese horario se acaba de ocupar. Elige otro, por favor.']);
            }
            throw $e;
        }

        responder(201, ['pedido' => ['id' => $pedidoId, 'total' => $precio, 'metodo' => METODOS_PAGO[$metodo]]]);
    }

    responder(400, ['error' => 'Tipo de pedido no válido.']);
} catch (PDOException $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    error_log('FreshCuttz pedido: ' . $e->getMessage());
    responder(500, ['error' => 'No pudimos registrar tu pedido. Intenta de nuevo más tarde.']);
}

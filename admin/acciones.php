<?php
// Todas las acciones del panel llegan aquí por POST y regresan a la sección de origen.
require_once __DIR__ . '/../includes/admin.php';
require_once __DIR__ . '/../includes/lealtad.php';

$admin = exigirAdmin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}
verificarCsrf();

$accion = (string)($_POST['accion'] ?? '');
$secciones = ['resumen', 'pedidos', 'citas', 'productos', 'cuentas', 'club', 'ajustes'];
$volver = in_array($_POST['volver'] ?? '', $secciones, true) ? $_POST['volver'] : 'resumen';

function regresar(string $seccion): void
{
    header('Location: index.php?s=' . urlencode($seccion));
    exit;
}

function entero($valor, int $minimo = 1): ?int
{
    $numero = filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => $minimo]]);
    return $numero === false ? null : $numero;
}

/** Valida nombre, precio y stock de un producto. Devuelve [datos, error]. */
function datosProducto(): array
{
    $nombre = trim(preg_replace('/\s+/u', ' ', (string)($_POST['nombre'] ?? '')));
    $precio = filter_var($_POST['precio'] ?? '', FILTER_VALIDATE_FLOAT);
    $stockTexto = trim((string)($_POST['stock'] ?? ''));
    $stock = $stockTexto === '' ? null : entero($stockTexto, 0);

    if (mb_strlen($nombre, 'UTF-8') < 2 || mb_strlen($nombre, 'UTF-8') > 100) {
        return [null, 'El nombre del producto debe tener entre 2 y 100 caracteres.'];
    }
    if ($precio === false || $precio < 0 || $precio > 99999999) {
        return [null, 'Escribe un precio válido.'];
    }
    if ($stockTexto !== '' && $stock === null) {
        return [null, 'El stock debe ser un número entero (o vacío si no tiene límite).'];
    }
    return [['nombre' => $nombre, 'precio' => round($precio, 2), 'stock' => $stock, 'activo' => isset($_POST['activo']) ? 1 : 0], null];
}

/**
 * Guarda la foto subida en MySQL (tabla imagenes) y devuelve su ruta pública, o lanza un mensaje de error.
 * No se escribe en disco: en Vercel el disco del contenedor es temporal y no se comparte entre instancias.
 */
function guardarFoto(array $archivo): string
{
    if (($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('No se pudo subir la foto. Intenta con otra imagen de máximo 3 MB.');
    }
    if ($archivo['size'] > 3 * 1024 * 1024) {
        throw new RuntimeException('La foto pesa más de 3 MB.');
    }
    $tipos = ['image/png' => 'png', 'image/webp' => 'webp', 'image/jpeg' => 'jpg'];
    $tipo = (new finfo(FILEINFO_MIME_TYPE))->file($archivo['tmp_name']);
    if (!isset($tipos[$tipo])) {
        throw new RuntimeException('La foto debe ser PNG, JPG o WEBP.');
    }
    $ruta = './image/productos/producto-' . bin2hex(random_bytes(6)) . '.' . $tipos[$tipo];
    conexion()->prepare('INSERT INTO imagenes (ruta, tipo, datos) VALUES (?, ?, ?)')
        ->execute([$ruta, $tipo, file_get_contents($archivo['tmp_name'])]);
    return $ruta;
}

$db = conexion();

try {
    switch ($accion) {
        case 'salir':
            unset($_SESSION['admin_id'], $_SESSION['admin_usuario']);
            session_regenerate_id(true);
            header('Location: login.php');
            exit;

        case 'pedido_estado':
            $id = entero($_POST['id'] ?? null);
            $estado = (string)($_POST['estado'] ?? '');
            if (!$id || !isset(ESTADOS_PEDIDO[$estado])) {
                avisar('Estado no válido.', 'error');
                break;
            }
            $db->beginTransaction();
            $pedido = $db->prepare('SELECT tipo, estado FROM pedidos WHERE id = ? FOR UPDATE');
            $pedido->execute([$id]);
            $pedido = $pedido->fetch();
            if (!$pedido) {
                $db->rollBack();
                avisar('Ese pedido ya no existe.', 'error');
                break;
            }
            if ($pedido['estado'] === 'cancelado') {
                $db->rollBack();
                avisar("El pedido #$id está cancelado y ya no se puede cambiar.", 'error');
                break;
            }
            $db->prepare('UPDATE pedidos SET estado = ? WHERE id = ?')->execute([$estado, $id]);
            if ($estado === 'cancelado') {
                if ($pedido['tipo'] === 'productos') {
                    // Regresamos al inventario lo que se había apartado.
                    $db->prepare(
                        'UPDATE productos p JOIN pedido_productos pp ON pp.producto_id = p.id
                         SET p.stock = p.stock + pp.cantidad
                         WHERE pp.pedido_id = ? AND p.stock IS NOT NULL'
                    )->execute([$id]);
                } else {
                    // Liberamos el horario de la cita.
                    $db->prepare('UPDATE citas SET cancelada = 1 WHERE pedido_id = ?')->execute([$id]);
                }
            }
            $db->commit();
            avisar("Pedido #$id: " . ESTADOS_PEDIDO[$estado] . '.');
            break;

        case 'producto_crear':
            [$datos, $error] = datosProducto();
            if ($error) {
                avisar($error, 'error');
                break;
            }
            $foto = guardarFoto($_FILES['foto'] ?? []);
            $db->prepare('INSERT INTO productos (nombre, precio, stock, foto, activo) VALUES (?, ?, ?, ?, ?)')
                ->execute([$datos['nombre'], $datos['precio'], $datos['stock'], $foto, $datos['activo']]);
            avisar("Producto «{$datos['nombre']}» agregado.");
            break;

        case 'producto_editar':
            $id = entero($_POST['id'] ?? null);
            [$datos, $error] = datosProducto();
            if (!$id || $error) {
                avisar($error ?: 'Producto no válido.', 'error');
                break;
            }
            $tieneFoto = ($_FILES['foto']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
            if ($tieneFoto) {
                $db->prepare('UPDATE productos SET nombre = ?, precio = ?, stock = ?, activo = ?, foto = ? WHERE id = ?')
                    ->execute([$datos['nombre'], $datos['precio'], $datos['stock'], $datos['activo'], guardarFoto($_FILES['foto']), $id]);
            } else {
                $db->prepare('UPDATE productos SET nombre = ?, precio = ?, stock = ?, activo = ? WHERE id = ?')
                    ->execute([$datos['nombre'], $datos['precio'], $datos['stock'], $datos['activo'], $id]);
            }
            avisar("Producto «{$datos['nombre']}» actualizado.");
            break;

        case 'producto_eliminar':
            $id = entero($_POST['id'] ?? null);
            if ($id) {
                // El historial de pedidos conserva el nombre y precio del producto.
                $db->prepare('DELETE FROM productos WHERE id = ?')->execute([$id]);
                avisar('Producto eliminado.');
            }
            break;

        case 'usuario_eliminar':
            $id = entero($_POST['id'] ?? null);
            if ($id) {
                // Sus pedidos se conservan (con nombre y teléfono) pero quedan sin cuenta ligada.
                $db->prepare('DELETE FROM usuarios WHERE id = ?')->execute([$id]);
                avisar('Cuenta eliminada.');
            }
            break;

        case 'precio_corte':
            $precio = filter_var($_POST['precio'] ?? '', FILTER_VALIDATE_FLOAT);
            if ($precio === false || $precio < 0 || $precio > 99999) {
                avisar('Escribe un precio válido.', 'error');
                break;
            }
            $db->prepare("INSERT INTO configuracion (clave, valor) VALUES ('precio_corte', ?) ON DUPLICATE KEY UPDATE valor = VALUES(valor)")
                ->execute([(string)round($precio, 2)]);
            avisar('Precio del corte actualizado a ' . dinero($precio) . '.');
            break;

        case 'admin_clave':
            $actual = (string)($_POST['actual'] ?? '');
            $nueva = (string)($_POST['nueva'] ?? '');
            $hash = $db->prepare('SELECT clave_hash FROM administradores WHERE id = ?');
            $hash->execute([$admin['id']]);
            if (!password_verify($actual, (string)$hash->fetchColumn())) {
                avisar('La contraseña actual no es correcta.', 'error');
            } elseif (strlen($nueva) < 8 || $nueva !== ($_POST['confirmar'] ?? '')) {
                avisar('La nueva contraseña debe tener al menos 8 caracteres y coincidir en ambos campos.', 'error');
            } else {
                $db->prepare('UPDATE administradores SET clave_hash = ? WHERE id = ?')
                    ->execute([password_hash($nueva, PASSWORD_DEFAULT), $admin['id']]);
                avisar('Contraseña actualizada.');
            }
            break;

        case 'admin_crear':
            $usuario = trim((string)($_POST['usuario'] ?? ''));
            $clave = (string)($_POST['clave'] ?? '');
            if (!preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $usuario) || strlen($clave) < 8) {
                avisar('Usuario de 3 a 50 caracteres (letras, números, _ . -) y contraseña de al menos 8.', 'error');
                break;
            }
            try {
                $db->prepare('INSERT INTO administradores (usuario, clave_hash) VALUES (?, ?)')
                    ->execute([$usuario, password_hash($clave, PASSWORD_DEFAULT)]);
                avisar("Administrador «{$usuario}» creado.");
            } catch (PDOException $e) {
                if ($e->getCode() !== '23000') {
                    throw $e;
                }
                avisar('Ya existe un administrador con ese usuario.', 'error');
            }
            break;

        case 'admin_eliminar':
            $id = entero($_POST['id'] ?? null);
            if ($id === $admin['id']) {
                avisar('No puedes eliminar tu propia cuenta de administrador.', 'error');
            } elseif ($id) {
                $db->prepare('DELETE FROM administradores WHERE id = ?')->execute([$id]);
                avisar('Administrador eliminado.');
            }
            break;

        case 'club_sello':
            $id = entero($_POST['id'] ?? null);
            $cliente = $db->prepare('SELECT nombre FROM usuarios WHERE id = ?');
            $cliente->execute([$id]);
            $nombre = $cliente->fetchColumn();
            if (!$id || $nombre === false) {
                avisar('Ese cliente ya no existe.', 'error');
            } elseif (registrarVisita($id, 'manual')) {
                $tarjeta = tarjetaCliente($id);
                avisar("Sello agregado a {$nombre}: {$tarjeta['sellos']}/{$tarjeta['meta']}"
                    . ($tarjeta['sellos'] === 0 ? ' — ¡completó su tarjeta y ganó un corte gratis!' : '.'));
            } else {
                avisar("{$nombre} ya tiene registrada su visita de hoy.", 'error');
            }
            break;

        case 'club_canjear':
            $id = entero($_POST['id'] ?? null);
            if (!$id || tarjetaCliente($id)['recompensas'] < 1) {
                avisar('Ese cliente no tiene cortes gratis por canjear.', 'error');
                break;
            }
            $db->prepare('INSERT INTO canjes (usuario_id) VALUES (?)')->execute([$id]);
            avisar('Corte gratis canjeado.');
            break;

        case 'club_meta':
            $meta = entero($_POST['meta'] ?? null, SELLOS_META_MIN);
            if (!$meta || $meta > SELLOS_META_MAX) {
                avisar('La meta debe estar entre ' . SELLOS_META_MIN . ' y ' . SELLOS_META_MAX . ' sellos.', 'error');
                break;
            }
            guardarConfiguracion('sellos_meta', (string)$meta);
            avisar("Ahora se necesitan {$meta} sellos para un corte gratis.");
            break;

        case 'club_token':
            nuevoTokenNfc();
            avisar('Link del chip renovado. Graba el nuevo link en la etiqueta: el anterior ya no suma sellos.');
            break;

        default:
            avisar('Acción no reconocida.', 'error');
    }
} catch (PDOException $e) {
    // Va antes que RuntimeException porque PDOException hereda de ella.
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    error_log('FreshCuttz admin: ' . $e->getMessage());
    avisar('Ocurrió un error con la base de datos. No se guardaron los cambios.', 'error');
} catch (RuntimeException $e) {
    avisar($e->getMessage(), 'error');
}

regresar($volver);

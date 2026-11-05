<?php
// Fresh Club: tarjeta de sellos por visita. La etiqueta NFC del mostrador abre visita.php?t=TOKEN.
require_once __DIR__ . '/negocio.php';

const SELLOS_META_DEFECTO = 8;
const SELLOS_META_MIN = 3;
const SELLOS_META_MAX = 20;

function configuracion(string $clave): ?string
{
    $consulta = conexion()->prepare('SELECT valor FROM configuracion WHERE clave = ?');
    $consulta->execute([$clave]);
    $valor = $consulta->fetchColumn();
    return $valor === false ? null : (string)$valor;
}

function guardarConfiguracion(string $clave, string $valor): void
{
    conexion()->prepare('INSERT INTO configuracion (clave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = VALUES(valor)')
        ->execute([$clave, $valor]);
}

/** Cortes necesarios para ganar uno gratis. */
function metaSellos(): int
{
    $meta = (int)(configuracion('sellos_meta') ?? SELLOS_META_DEFECTO);
    return max(SELLOS_META_MIN, min(SELLOS_META_MAX, $meta ?: SELLOS_META_DEFECTO));
}

/** Secreto que va grabado en la etiqueta NFC. Se crea la primera vez que se pide. */
function tokenNfc(): string
{
    $token = configuracion('nfc_token');
    return ($token === null || $token === '') ? nuevoTokenNfc() : $token;
}

/** Cambia el secreto: las etiquetas grabadas con el anterior dejan de sumar sellos. */
function nuevoTokenNfc(): string
{
    $token = bin2hex(random_bytes(12));
    guardarConfiguracion('nfc_token', $token);
    return $token;
}

function tokenNfcValido(string $token): bool
{
    return $token !== '' && hash_equals(tokenNfc(), $token);
}

/** Suma un sello. Devuelve false si el cliente ya tenía visita registrada hoy. */
function registrarVisita(int $usuarioId, string $origen = 'nfc'): bool
{
    $consulta = conexion()->prepare('INSERT IGNORE INTO visitas (usuario_id, dia, origen) VALUES (?, ?, ?)');
    $consulta->execute([$usuarioId, date('Y-m-d'), $origen === 'manual' ? 'manual' : 'nfc']);
    return $consulta->rowCount() === 1;
}

/** Estado de la tarjeta a partir de totales (lo usan la página del cliente y el panel). */
function estadoTarjeta(int $visitas, int $canjes, int $meta): array
{
    return [
        'meta' => $meta,
        'visitas' => $visitas,
        'sellos' => $visitas % $meta,
        'recompensas' => max(0, intdiv($visitas, $meta) - $canjes),
        'canjeadas' => $canjes,
    ];
}

function tarjetaCliente(int $usuarioId): array
{
    $db = conexion();
    $totales = $db->prepare(
        'SELECT (SELECT COUNT(*) FROM visitas WHERE usuario_id = ?) AS visitas,
                (SELECT COUNT(*) FROM canjes WHERE usuario_id = ?) AS canjes'
    );
    $totales->execute([$usuarioId, $usuarioId]);
    $fila = $totales->fetch();

    $historial = $db->prepare('SELECT dia, origen FROM visitas WHERE usuario_id = ? ORDER BY dia DESC LIMIT 6');
    $historial->execute([$usuarioId]);

    $hoy = $db->prepare('SELECT 1 FROM visitas WHERE usuario_id = ? AND dia = ?');
    $hoy->execute([$usuarioId, date('Y-m-d')]);

    return estadoTarjeta((int)$fila['visitas'], (int)$fila['canjes'], metaSellos()) + [
        'historial' => $historial->fetchAll(),
        'visitoHoy' => (bool)$hoy->fetchColumn(),
    ];
}

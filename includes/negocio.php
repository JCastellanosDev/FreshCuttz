<?php
// Reglas del negocio compartidas por la tienda, las citas y el panel de administración.
require_once __DIR__ . '/conexion.php';

date_default_timezone_set('America/Mexico_City');

// Deben coincidir con los id de scripts/personal.js.
const BARBEROS = [
    1 => "Mario ''El güero''",
    2 => "Dany ''El matute''",
    3 => "Cristian ''La mune''",
    4 => "Isziee ''El isipisi''",
    5 => "Mario ''El grandpa''",
];

const DIAS_ABIERTOS = [1, 2, 3, 4, 5, 6]; // lunes a sábado (0 = domingo)
const HORA_APERTURA = 10;
const HORA_CIERRE = 20;
const DURACION_CITA_HORAS = 1;
const PRECIO_CORTE_DEFECTO = 250;

const METODOS_PAGO = [
    'efectivo' => 'Efectivo',
    'transferencia' => 'Transferencia',
    'mercado-pago' => 'Mercado Pago',
];

const ESTADOS_PEDIDO = [
    'pendiente' => 'Pendiente de pago',
    'pagado' => 'Pagado',
    'completado' => 'Completado',
    'cancelado' => 'Cancelado',
];

const PATRON_NOMBRE = '/^[A-Za-zÁÉÍÓÚÜÑáéíóúüñ ]+$/u';

function e($valor): string
{
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

function dinero($valor): string
{
    return '$' . number_format((float)$valor, 2);
}

/** Devuelve el nombre limpio o null si no cumple las reglas. */
function nombreValido($nombre): ?string
{
    $nombre = trim(preg_replace('/\s+/u', ' ', (string)$nombre));
    $largo = mb_strlen($nombre, 'UTF-8');
    return ($largo >= 2 && $largo <= 100 && preg_match(PATRON_NOMBRE, $nombre)) ? $nombre : null;
}

function telefonoValido($telefono): ?string
{
    $telefono = (string)$telefono;
    return preg_match('/^[0-9]{10}$/', $telefono) ? $telefono : null;
}

/** Horas de inicio de cita en formato HH:MM. */
function horasCita(): array
{
    $horas = [];
    for ($inicio = HORA_APERTURA; $inicio + DURACION_CITA_HORAS <= HORA_CIERRE; $inicio += DURACION_CITA_HORAS) {
        $horas[] = sprintf('%02d:00', $inicio);
    }
    return $horas;
}

function precioCorte(): float
{
    try {
        $valor = conexion()->query("SELECT valor FROM configuracion WHERE clave = 'precio_corte'")->fetchColumn();
        return $valor !== false ? (float)$valor : PRECIO_CORTE_DEFECTO;
    } catch (PDOException $e) {
        error_log('FreshCuttz precioCorte: ' . $e->getMessage());
        return PRECIO_CORTE_DEFECTO;
    }
}

/** Productos visibles en la tienda, con el formato que usa scripts/carrito.js. */
function productosActivos(): array
{
    $filas = conexion()->query('SELECT id, nombre, precio, stock, foto FROM productos WHERE activo = 1 ORDER BY id')->fetchAll();
    return array_map(fn($p) => [
        'id' => (int)$p['id'],
        'nombre' => $p['nombre'],
        'precio' => (float)$p['precio'],
        'cantidadDisponible' => $p['stock'] === null ? null : (int)$p['stock'],
        'foto' => $p['foto'],
    ], $filas);
}

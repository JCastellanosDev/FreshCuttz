<?php
// API de cuentas: crear cuenta, iniciar sesión, cerrar sesión y consultar la sesión actual.
require_once __DIR__ . '/../includes/sesion.php';
abrirSesion();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/conexion.php';

function responder(int $codigo, array $datos): void
{
    http_response_code($codigo);
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit;
}

function usuarioActual(): ?array
{
    return isset($_SESSION['usuario_id'])
        ? ['id' => $_SESSION['usuario_id'], 'nombre' => $_SESSION['usuario_nombre']]
        : null;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    responder(200, ['usuario' => usuarioActual()]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(405, ['error' => 'Método no permitido.']);
}

$entrada = json_decode(file_get_contents('php://input'), true) ?: [];
$accion = $entrada['accion'] ?? '';

if ($accion === 'salir') {
    $_SESSION = [];
    session_destroy();
    responder(200, ['usuario' => null]);
}

if ($accion !== 'registro' && $accion !== 'login') {
    responder(400, ['error' => 'Acción no válida.']);
}

// Mismas reglas que valida perfil.js en el navegador.
$nombre = trim(preg_replace('/\s+/u', ' ', (string)($entrada['nombre'] ?? '')));
$telefono = (string)($entrada['telefono'] ?? '');
$largoNombre = mb_strlen($nombre, 'UTF-8');

if ($largoNombre < 2 || $largoNombre > 100 || !preg_match('/^[A-Za-zÁÉÍÓÚÜÑáéíóúüñ ]+$/u', $nombre)) {
    responder(422, ['error' => 'El nombre debe tener entre 2 y 100 letras, sin números ni símbolos.', 'campo' => 'nombre']);
}
if (!preg_match('/^[0-9]{10}$/', $telefono)) {
    responder(422, ['error' => 'El teléfono debe tener exactamente 10 números.', 'campo' => 'telefono']);
}

function iniciarSesion(int $id, string $nombre): void
{
    session_regenerate_id(true);
    $_SESSION['usuario_id'] = $id;
    $_SESSION['usuario_nombre'] = $nombre;
    responder(200, ['usuario' => ['id' => $id, 'nombre' => $nombre]]);
}

try {
    $db = conexion();

    if ($accion === 'registro') {
        try {
            $db->prepare('INSERT INTO usuarios (nombre, telefono) VALUES (?, ?)')->execute([$nombre, $telefono]);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                responder(409, ['error' => 'Ese teléfono ya tiene una cuenta. Usa "Ya tengo cuenta" para entrar.', 'campo' => 'telefono']);
            }
            throw $e;
        }
        iniciarSesion((int)$db->lastInsertId(), $nombre);
    }

    // Login: el teléfono identifica la cuenta y el nombre debe coincidir (sin distinguir mayúsculas ni acentos).
    $consulta = $db->prepare('SELECT id, nombre FROM usuarios WHERE telefono = ? AND nombre = ?');
    $consulta->execute([$telefono, $nombre]);
    $usuario = $consulta->fetch();
    if (!$usuario) {
        responder(401, ['error' => 'Nombre o teléfono incorrectos.']);
    }
    iniciarSesion((int)$usuario['id'], $usuario['nombre']);
} catch (PDOException $e) {
    error_log('FreshCuttz cuenta: ' . $e->getMessage());
    responder(500, ['error' => 'No pudimos conectar con la base de datos. Intenta de nuevo más tarde.']);
}

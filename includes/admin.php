<?php
// Sesión, protección CSRF y mensajes del panel de administración.
require_once __DIR__ . '/negocio.php';
require_once __DIR__ . '/sesion.php';

abrirSesion();

function adminActual(): ?array
{
    return isset($_SESSION['admin_id'])
        ? ['id' => (int)$_SESSION['admin_id'], 'usuario' => $_SESSION['admin_usuario']]
        : null;
}

/** Corta la petición si no hay administrador con sesión. */
function exigirAdmin(): array
{
    $admin = adminActual();
    if (!$admin) {
        header('Location: login.php');
        exit;
    }
    return $admin;
}

function tokenCsrf(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function campoCsrf(): string
{
    return '<input type="hidden" name="csrf" value="' . e(tokenCsrf()) . '">';
}

function verificarCsrf(): void
{
    if (!hash_equals(tokenCsrf(), (string)($_POST['csrf'] ?? ''))) {
        http_response_code(403);
        exit('La sesión del formulario expiró. Regresa y vuelve a intentarlo.');
    }
}

/**
 * Ícono del sprite compartido (image/iconos.svg) para el panel.
 * El tamaño se traduce a las clases del sistema: 14 → icono-xs (admin.css), 16 → icono-sm,
 * 18 → icono-md (admin.css), 24 → icono-lg. width/height quedan como respaldo sin CSS.
 */
function icono(string $nombre, int $tamano = 18): string
{
    $alias = ["citas" => "calendario"];
    $simbolo = $alias[$nombre] ?? $nombre;
    $clase = match (true) {
        $tamano <= 14 => "icono icono-xs",
        $tamano <= 16 => "icono icono-sm",
        $tamano >= 24 => "icono icono-lg",
        default => "icono icono-md",
    };
    return "<svg class=\"" . $clase . "\" width=\"" . $tamano . "\" height=\"" . $tamano . "\" aria-hidden=\"true\" focusable=\"false\">"
        . "<use href=\"../image/iconos.svg#" . e($simbolo) . "\"/></svg>";
}

/** Fecha larga en español sin depender de la extensión intl: "martes 3 de noviembre". */
function fechaLarga(string $fecha = 'now'): string
{
    $dias = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
    $meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    $marca = strtotime($fecha);
    return $dias[(int)date('w', $marca)] . ' ' . date('j', $marca) . ' de ' . $meses[(int)date('n', $marca) - 1];
}

function avisar(string $mensaje, string $tipo = 'exito'): void
{
    $_SESSION['aviso'] = ['mensaje' => $mensaje, 'tipo' => $tipo];
}

function tomarAviso(): ?array
{
    $aviso = $_SESSION['aviso'] ?? null;
    unset($_SESSION['aviso']);
    return $aviso;
}

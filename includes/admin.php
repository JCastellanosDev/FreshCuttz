<?php
// Sesión, protección CSRF y mensajes del panel de administración.
require_once __DIR__ . '/negocio.php';

session_start();

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

/** Íconos SVG de trazo uniforme (estilo Lucide) para el panel. */
function icono(string $nombre, int $tamano = 18): string
{
    $trazos = [
        'resumen' => '<rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/>',
        'pedidos' => '<path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 17.5v-11"/>',
        'citas' => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
        'productos' => '<path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>',
        'cuentas' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        'ajustes' => '<path d="M20 7h-9M14 17H5"/><circle cx="17" cy="17" r="3"/><circle cx="7" cy="7" r="3"/>',
        'externo' => '<path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>',
        'salir' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/>',
        'mas' => '<path d="M5 12h14M12 5v14"/>',
        'buscar' => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
        'alerta' => '<path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"/><path d="M12 9v4M12 17h.01"/>',
        'listo' => '<circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/>',
        'error' => '<circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/>',
        'abajo' => '<path d="m6 9 6 6 6-6"/>',
        'borrar' => '<path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>',
    ];
    return '<svg class="icono" width="' . $tamano . '" height="' . $tamano . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" '
        . 'stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">'
        . ($trazos[$nombre] ?? '') . '</svg>';
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

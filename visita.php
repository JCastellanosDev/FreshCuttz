<?php
// Destino de la etiqueta NFC del mostrador: visita.php?t=TOKEN
// Con sesión suma el sello del día; sin sesión lo deja pendiente hasta que el cliente entre.
require_once __DIR__ . '/includes/sesion.php';
require_once __DIR__ . '/includes/lealtad.php';
abrirSesion();

// Sin espacios: al copiar el link o grabarlo en el chip a veces se cuelan al inicio o al final.
$token = trim((string)($_GET['t'] ?? ''));

try {
    if (!tokenNfcValido($token)) {
        $_SESSION['club_aviso'] = ['error', 'Esta etiqueta ya no es válida. Pide en el mostrador que registren tu visita.'];
    } elseif (isset($_SESSION['usuario_id'])) {
        $_SESSION['club_aviso'] = registrarVisita((int)$_SESSION['usuario_id'], 'nfc')
            ? ['sello', '¡Sello agregado! Gracias por tu visita.']
            : ['info', 'Tu visita de hoy ya estaba registrada. Vuelve en tu próximo corte.'];
    } else {
        // Vale 15 minutos: tiempo para crear la cuenta o iniciar sesión.
        $_SESSION['club_visita_pendiente'] = time();
    }
} catch (PDOException $e) {
    error_log('FreshCuttz visita: ' . $e->getMessage());
    $_SESSION['club_aviso'] = ['error', 'No pudimos registrar tu visita. Pide ayuda en el mostrador.'];
}

header('Location: tarjeta.php', true, 303);
exit;

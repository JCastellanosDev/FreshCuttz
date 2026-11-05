<?php
// Sesiones guardadas en MySQL (tabla sesiones) en vez de archivos.
// En Vercel cada instancia tiene su propio disco temporal: con archivos, el cliente
// perdería la sesión al caer en otra instancia. Úsese abrirSesion() en lugar de session_start().
require_once __DIR__ . '/conexion.php';

const SESION_DURACION = 30 * 24 * 3600; // 30 días: la tarjeta Fresh Club recuerda al cliente entre cortes

final class SesionMysql implements SessionHandlerInterface, SessionUpdateTimestampHandlerInterface
{
    public function open(string $ruta, string $nombre): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $id): string
    {
        $consulta = conexion()->prepare('SELECT datos FROM sesiones WHERE id = ? AND expira > ?');
        $consulta->execute([$id, time()]);
        return (string)($consulta->fetchColumn() ?: '');
    }

    public function write(string $id, string $datos): bool
    {
        conexion()->prepare(
            'INSERT INTO sesiones (id, datos, expira) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE datos = VALUES(datos), expira = VALUES(expira)'
        )->execute([$id, $datos, time() + SESION_DURACION]);
        return true;
    }

    public function destroy(string $id): bool
    {
        conexion()->prepare('DELETE FROM sesiones WHERE id = ?')->execute([$id]);
        return true;
    }

    /** Modo estricto: solo se aceptan identificadores que existen (evita fijación de sesión). */
    public function validateId(string $id): bool
    {
        $consulta = conexion()->prepare('SELECT 1 FROM sesiones WHERE id = ? AND expira > ?');
        $consulta->execute([$id, time()]);
        return (bool)$consulta->fetchColumn();
    }

    public function updateTimestamp(string $id, string $datos): bool
    {
        conexion()->prepare('UPDATE sesiones SET expira = ? WHERE id = ?')->execute([time() + SESION_DURACION, $id]);
        return true;
    }

    public function gc(int $vida): int|false
    {
        $consulta = conexion()->prepare('DELETE FROM sesiones WHERE expira < ?');
        $consulta->execute([time()]);
        return $consulta->rowCount();
    }
}

function abrirSesion(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    // Vercel termina HTTPS en su borde y pasa la petición por HTTP: lo indica X-Forwarded-Proto.
    $https = ($_SERVER['HTTPS'] ?? '') === 'on' || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
    ini_set('session.gc_maxlifetime', (string)SESION_DURACION);
    ini_set('session.use_strict_mode', '1');
    session_set_cookie_params([
        'lifetime' => SESION_DURACION,
        'path' => '/',
        'secure' => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_set_save_handler(new SesionMysql(), true);
    session_start();
}

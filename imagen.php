<?php
// Sirve las fotos de productos subidas desde el panel, guardadas en la tabla imagenes.
// Llega aquí por reescritura (Caddyfile en Vercel, image/productos/.htaccess en XAMPP)
// cuando /image/productos/producto-xxxx.ext no existe como archivo.
require_once __DIR__ . '/includes/conexion.php';

$ruta = rawurldecode((string)parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH));
// En XAMPP el sitio vive en /FreshCuttz/: nos quedamos con la parte desde image/productos/.
$ruta = './' . (string)strstr($ruta, 'image/productos/');

if (!preg_match('#^\./image/productos/producto-[a-f0-9]{12}\.(png|webp|jpg)$#', $ruta)) {
    http_response_code(404);
    exit;
}

try {
    $consulta = conexion()->prepare('SELECT tipo, datos FROM imagenes WHERE ruta = ?');
    $consulta->execute([$ruta]);
    $imagen = $consulta->fetch();
} catch (PDOException $e) {
    error_log('FreshCuttz imagen: ' . $e->getMessage());
    http_response_code(503);
    exit;
}

if (!$imagen) {
    http_response_code(404);
    exit;
}

// El nombre es aleatorio y nunca se reutiliza: se puede guardar en caché por un año.
header('Content-Type: ' . $imagen['tipo']);
header('Content-Length: ' . strlen($imagen['datos']));
header('Cache-Control: public, max-age=31536000, immutable');
header('X-Content-Type-Options: nosniff');
echo $imagen['datos'];

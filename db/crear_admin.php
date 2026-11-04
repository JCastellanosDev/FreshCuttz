<?php
// Crea un administrador o cambia su contraseña. Solo desde la terminal:
//   /Applications/XAMPP/xamppfiles/bin/php db/crear_admin.php usuario contraseña
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Solo disponible desde la terminal.');
}

require_once __DIR__ . '/../includes/conexion.php';

[$script, $usuario, $clave] = array_pad($argv, 3, '');
if (!preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $usuario) || strlen($clave) < 8) {
    fwrite(STDERR, "Uso: php db/crear_admin.php usuario contraseña\n");
    fwrite(STDERR, "El usuario: 3-50 letras, números, _ . -   La contraseña: mínimo 8 caracteres.\n");
    exit(1);
}

conexion()->prepare(
    'INSERT INTO administradores (usuario, clave_hash) VALUES (?, ?)
     ON DUPLICATE KEY UPDATE clave_hash = VALUES(clave_hash)'
)->execute([$usuario, password_hash($clave, PASSWORD_DEFAULT)]);

echo "Administrador '$usuario' listo.\n";

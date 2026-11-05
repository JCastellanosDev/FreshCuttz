<?php
// Conexión a MySQL. En Vercel los datos vienen de variables de entorno;
// sin ellas se usan los valores de XAMPP para desarrollo local.

function entorno(string $nombre, string $defecto = ''): string
{
    $valor = getenv($nombre);
    return ($valor === false || $valor === '') ? $defecto : $valor;
}

function conexion(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $opciones = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            // CURRENT_TIMESTAMP en hora del centro de México, igual que PHP (sin horario de verano desde 2022).
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '-06:00'",
        ];
        // Los MySQL en la nube (Aiven, TiDB, Railway…) exigen conexión cifrada.
        if (entorno('DB_SSL') === '1') {
            $opciones[PDO::MYSQL_ATTR_SSL_CA] = entorno('DB_SSL_CA', '/etc/ssl/certs/ca-certificates.crt');
            $opciones[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
        }
        $pdo = new PDO(
            sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                entorno('DB_HOST', '127.0.0.1'),
                entorno('DB_PORT', '3306'),
                entorno('DB_NAME', 'freshcuttz')
            ),
            entorno('DB_USER', 'root'),
            entorno('DB_PASSWORD'),
            $opciones
        );
    }
    return $pdo;
}

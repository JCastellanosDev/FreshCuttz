<?php
// Conexión a MySQL/MariaDB de XAMPP
const DB_HOST = '127.0.0.1';
const DB_NOMBRE = 'freshcuttz';
const DB_USUARIO = 'root';
const DB_CLAVE = '';

function conexion(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NOMBRE . ';charset=utf8mb4',
            DB_USUARIO,
            DB_CLAVE,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    }
    return $pdo;
}

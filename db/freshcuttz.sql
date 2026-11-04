-- Base de datos de Fresh Cuttz
-- Importar en phpMyAdmin o con: /Applications/XAMPP/xamppfiles/bin/mysql -u root < db/freshcuttz.sql
-- Se puede ejecutar varias veces: no borra datos existentes.

CREATE DATABASE IF NOT EXISTS freshcuttz
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE freshcuttz;

-- Clientes que crean cuenta desde el sitio.
CREATE TABLE IF NOT EXISTS usuarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    telefono CHAR(10) NOT NULL,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_usuarios_telefono (telefono)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cuentas del panel de administración (contraseña con password_hash).
CREATE TABLE IF NOT EXISTS administradores (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL,
    clave_hash VARCHAR(255) NOT NULL,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_administradores_usuario (usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Productos de la tienda. stock NULL = sin límite / por definir.
CREATE TABLE IF NOT EXISTS productos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    stock INT UNSIGNED NULL,
    foto VARCHAR(255) NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ajustes editables desde el panel (por ejemplo, el precio del corte).
CREATE TABLE IF NOT EXISTS configuracion (
    clave VARCHAR(50) PRIMARY KEY,
    valor VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cada compra de productos o pago de corte es un pedido.
CREATE TABLE IF NOT EXISTS pedidos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NULL,
    cliente_nombre VARCHAR(100) NOT NULL,
    cliente_telefono CHAR(10) NOT NULL,
    tipo ENUM('productos', 'cita') NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    metodo_pago ENUM('efectivo', 'transferencia', 'mercado-pago') NOT NULL,
    estado ENUM('pendiente', 'pagado', 'completado', 'cancelado') NOT NULL DEFAULT 'pendiente',
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_pedidos_estado (estado),
    CONSTRAINT fk_pedidos_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Productos de cada pedido. Guarda nombre y precio del momento de la compra.
CREATE TABLE IF NOT EXISTS pedido_productos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT UNSIGNED NOT NULL,
    producto_id INT UNSIGNED NULL,
    nombre VARCHAR(100) NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    cantidad INT UNSIGNED NOT NULL,
    CONSTRAINT fk_pp_pedido FOREIGN KEY (pedido_id) REFERENCES pedidos (id) ON DELETE CASCADE,
    CONSTRAINT fk_pp_producto FOREIGN KEY (producto_id) REFERENCES productos (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Citas de corte. "activa" impide reservar dos veces el mismo barbero, día y hora
-- (las citas canceladas quedan en NULL y liberan el horario).
CREATE TABLE IF NOT EXISTS citas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT UNSIGNED NOT NULL,
    barbero_id TINYINT UNSIGNED NOT NULL,
    barbero_nombre VARCHAR(100) NOT NULL,
    fecha DATE NOT NULL,
    hora TIME NOT NULL,
    cancelada TINYINT(1) NOT NULL DEFAULT 0,
    activa TINYINT(1) AS (IF(cancelada = 1, NULL, 1)) PERSISTENT,
    UNIQUE KEY uq_citas_pedido (pedido_id),
    UNIQUE KEY uq_citas_horario (barbero_id, fecha, hora, activa),
    KEY idx_citas_fecha (fecha),
    CONSTRAINT fk_citas_pedido FOREIGN KEY (pedido_id) REFERENCES pedidos (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Datos iniciales (no duplica si ya existen).
INSERT IGNORE INTO configuracion (clave, valor) VALUES ('precio_corte', '250');

INSERT IGNORE INTO productos (id, nombre, precio, stock, foto) VALUES
    (1, 'Cera', 250, NULL, './image/productos/cera-sin-fondo.png'),
    (2, 'Suavecito', 120, NULL, './image/productos/sua-sin-fondo.png'),
    (3, 'CeraGel', 300, NULL, './image/productos/ceraGel-sin-fondo.png'),
    (5, 'Mousse', 100, NULL, './image/productos/mousse-sin-fondo.png'),
    (6, 'Pomada', 70, NULL, './image/productos/pomada-sin-fondo.png'),
    (7, 'Crema', 80, NULL, './image/productos/crema-sin-fondo.png');

-- AlejoFest Vol.21 — Esquema de base de datos
-- Importar con: mysql -u root -p < db.sql

CREATE DATABASE IF NOT EXISTS alejofest CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE alejofest;

CREATE TABLE photos (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    filename VARCHAR(64) NOT NULL,          -- nombre aleatorio .jpg dentro de /uploads
    width SMALLINT UNSIGNED NOT NULL,
    height SMALLINT UNSIGNED NOT NULL,
    orientation ENUM('horizontal','vertical','cuadrada') NOT NULL DEFAULT 'vertical',
    visible TINYINT(1) NOT NULL DEFAULT 1,  -- 0 = oculta por admin (no se borra el archivo)
    uploader_ip VARBINARY(16) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_filename (filename),
    KEY idx_visible_created (visible, created_at)
) ENGINE=InnoDB;

-- Cola FIFO para la pantalla grande. Cada subida inserta una fila;
-- el admin puede "volver a reproducir" insertando otra fila para la misma foto.
CREATE TABLE screen_queue (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    photo_id INT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_photo (photo_id),
    CONSTRAINT fk_queue_photo FOREIGN KEY (photo_id) REFERENCES photos(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE admins (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    username VARCHAR(40) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_username (username)
) ENGINE=InnoDB;

-- Intentos de login fallidos (throttling del panel admin)
CREATE TABLE login_attempts (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    ip VARBINARY(16) NOT NULL,
    attempted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_ip_time (ip, attempted_at)
) ENGINE=InnoDB;

-- El usuario admin se crea con: php tools/crear_admin.php usuario contraseña

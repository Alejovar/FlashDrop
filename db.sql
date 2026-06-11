-- FLASHDROP — Esquema de base de datos
-- Importar con: mysql -u flashdrop_app -p flashdrop < db.sql
-- O en deploy automatizado: mysql < db.sql (si está configurado en config.php)

CREATE DATABASE IF NOT EXISTS flashdrop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE flashdrop;

-- ============================================================
-- Tabla principal de fotos (con marca de agua estampada)
-- ============================================================
CREATE TABLE photos (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    filename VARCHAR(64) NOT NULL UNIQUE,
    width SMALLINT UNSIGNED NOT NULL,
    height SMALLINT UNSIGNED NOT NULL,
    orientation ENUM('horizontal','vertical','cuadrada') NOT NULL DEFAULT 'vertical',
    visible TINYINT(1) NOT NULL DEFAULT 1,
    uploader_ip VARBINARY(16) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_filename (filename),
    KEY idx_visible_created (visible, created_at),
    KEY idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Cola FIFO para la pantalla grande
-- Cada subida inserta una fila; el admin puede re-encolar con "Reproducir"
-- ============================================================
CREATE TABLE screen_queue (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    photo_id INT UNSIGNED NOT NULL,
    enqueued_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_photo (photo_id),
    KEY idx_enqueued (enqueued_at),
    CONSTRAINT fk_queue_photo FOREIGN KEY (photo_id) REFERENCES photos(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Tabla de administradores
-- ============================================================
CREATE TABLE admins (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    username VARCHAR(40) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,      -- password_hash(password, PASSWORD_DEFAULT)
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_login DATETIME,
    PRIMARY KEY (id),
    KEY idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Throttling: intentos de login fallidos por IP
-- ============================================================
CREATE TABLE login_attempts (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    ip VARBINARY(16) NOT NULL,
    attempted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_ip_time (ip, attempted_at),
    -- Purgar entradas más viejas de 30 minutos regularmente
    CONSTRAINT chk_recent CHECK (attempted_at > DATE_SUB(NOW(), INTERVAL 1 HOUR))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Índices y triggers para mantenimiento
-- ============================================================

-- Trigger: limpiar screen_queue después de procesar (opcional, para mantener tabla pequeña)
-- DELIMITER //
-- CREATE TRIGGER cleanup_old_queue AFTER INSERT ON screen_queue
-- FOR EACH ROW BEGIN
--   DELETE FROM screen_queue WHERE enqueued_at < DATE_SUB(NOW(), INTERVAL 24 HOUR);
-- END//
-- DELIMITER ;

-- ============================================================
-- El usuario admin se crea con:
--   php tools/crear_admin.php usuario contraseña_fuerte
-- ============================================================

-- Datos iniciales (opcional, comentado por defecto)
-- INSERT INTO admins (username, password_hash) VALUES ('admin', '$2y$10$...');  -- generar con password_hash()

-- CREATE USER 'flashdrop_app'@'localhost' IDENTIFIED BY 'contraseña_fuerte';
-- GRANT SELECT, INSERT, UPDATE ON flashdrop.* TO 'flashdrop_app'@'localhost';
-- FLUSH PRIVILEGES;

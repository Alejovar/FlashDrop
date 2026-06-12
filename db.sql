-- FLASHDROP v2 — Esquema de base de datos
-- Importar con: mysql -u flashdrop_app -p flashdrop < db.sql

CREATE DATABASE IF NOT EXISTS flashdrop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE flashdrop;

-- ============================================================
-- Tabla principal de fotos — guarda el original sin marca de agua
-- ============================================================
CREATE TABLE IF NOT EXISTS photos (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    filename VARCHAR(64) NOT NULL UNIQUE,          -- archivo original en /uploads/originals/
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
-- ============================================================
CREATE TABLE IF NOT EXISTS screen_queue (
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
CREATE TABLE IF NOT EXISTS admins (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    username VARCHAR(40) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_login DATETIME,
    PRIMARY KEY (id),
    KEY idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Throttling: intentos de login fallidos por IP
-- ============================================================
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    ip VARBINARY(16) NOT NULL,
    attempted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_ip_time (ip, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Historial de logros (hitos automáticos cada 15 fotos)
-- ============================================================
CREATE TABLE IF NOT EXISTS milestones (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    quantity INT UNSIGNED NOT NULL,          -- 15, 30, 45, 60 …
    photo_id INT UNSIGNED NOT NULL,          -- foto protagonista
    achieved_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_quantity (quantity),
    KEY idx_photo (photo_id),
    CONSTRAINT fk_milestone_photo FOREIGN KEY (photo_id) REFERENCES photos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- El usuario admin se crea con:
--   php tools/crear_admin.php usuario contraseña_fuerte
-- ============================================================

-- ============================================================
-- Eventos de prueba para el admin (dispara animaciones en pantalla.php)
-- ============================================================
CREATE TABLE IF NOT EXISTS test_events (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    type ENUM('toast','milestone') NOT NULL,
    photo_id INT UNSIGNED,          -- NULL = usar foto aleatoria
    quantity INT UNSIGNED,          -- solo para type=milestone
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

<?php
// ============================================================
//  FLASHDROP v2 — Configuración
//  Las credenciales de BD viven en config_db.php,
//  generado automáticamente por el pipeline con variables de GitLab.
//  config_db.php NUNCA se sube al repositorio (.gitignore).
// ============================================================

// --- Base de datos (generado por el pipeline, no en el repo) ---
require_once __DIR__ . '/config_db.php';

// --- Subidas ---
define('MAX_UPLOAD_MB', 12);
define('MAX_DIMENSION', 1920);
define('JPEG_QUALITY', 92);
define('UPLOADS_DIR',   __DIR__ . '/uploads/originals');
define('UPLOADS_URL',   'uploads/originals');

// --- Logo para Polaroid ---
define('LOGO_PATH',     __DIR__ . '/assets/logo.png');
define('EVENT_NAME',    'AlejoFest Vol.21');

// --- Rate limit de subidas ---
define('UPLOADS_PER_MINUTE_PER_IP', 4);

// --- Pantalla grande ---
define('FEED_POLL_SECONDS', 3);
define('TOAST_SECONDS',     10);
define('FEATURE_SECONDS',   10);

// --- Logros (milestones) ---
define('MILESTONE_EVERY', 15);

// --- Sesión / seguridad ---
define('SESSION_NAME',            'ALEJOFEST_SESS');
define('ADMIN_MAX_ATTEMPTS',      6);
define('ADMIN_ATTEMPT_WINDOW_MIN', 10);
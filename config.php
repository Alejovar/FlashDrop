<?php
// ============================================================
//  FLASHDROP v2 — Configuración
//  NUNCA subas config.php a un repositorio público.
// ============================================================

// --- Base de datos ---
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'flashdrop');
define('DB_USER', 'flashdrop_app');
define('DB_PASS', 'WDo6MCOT5FNkLGrbANS++A==');

// --- Subidas ---
define('MAX_UPLOAD_MB', 12);
define('MAX_DIMENSION', 1920);
define('JPEG_QUALITY', 92);                         // calidad alta para conservar originales
define('UPLOADS_DIR',   __DIR__ . '/uploads/originals');   // originales sin marca de agua
define('UPLOADS_URL',   'uploads/originals');              // ruta pública relativa

// --- Logo para Polaroid ---
define('LOGO_PATH',     __DIR__ . '/assets/logo.png');
define('EVENT_NAME',    'AlejoFest Vol.21');

// --- Rate limit de subidas ---
define('UPLOADS_PER_MINUTE_PER_IP', 4);

// --- Pantalla grande ---
define('FEED_POLL_SECONDS', 3);
define('TOAST_SECONDS',     10);    // ventana MSN nueva foto
define('FEATURE_SECONDS',   10);    // ventana destacada visible

// --- Logros (milestones) ---
define('MILESTONE_EVERY', 15);      // cada N fotos se activa un logro

// --- Sesión / seguridad ---
define('SESSION_NAME',            'ALEJOFEST_SESS');
define('ADMIN_MAX_ATTEMPTS',      6);
define('ADMIN_ATTEMPT_WINDOW_MIN', 10);

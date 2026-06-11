<?php
// ============================================================
//  AlejoFest Vol.21 — Configuración
//  Copia este archivo como config.php y ajusta tus credenciales.
//  NUNCA subas config.php a un repositorio público.
// ============================================================

// --- Base de datos ---
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'alejofest');
define('DB_USER', 'alejofest_app');      // usuario con permisos SOLO sobre esta BD
define('DB_PASS', 'CAMBIA_ESTA_CONTRASENA');

// --- Subidas ---
define('MAX_UPLOAD_MB', 12);             // tamaño máximo del archivo original
define('MAX_DIMENSION', 1920);           // lado mayor al que se reescala la foto
define('JPEG_QUALITY', 88);
define('UPLOADS_DIR', __DIR__ . '/uploads');
define('UPLOADS_URL', 'uploads');        // ruta pública relativa

// --- Marca de agua ---
// PNG con transparencia que se estampa sobre cada foto.
define('WATERMARK_PATH', __DIR__ . '/assets/logo.png');
define('WATERMARK_SCALE', 0.42);         // ancho de la marca = 42% del ancho de la foto
define('WATERMARK_MARGIN', 0.035);       // margen respecto al borde (proporcional)

// --- Rate limit de subidas ---
define('UPLOADS_PER_MINUTE_PER_IP', 4);

// --- Pantalla grande ---
define('FEED_POLL_SECONDS', 3);          // cada cuánto consulta la pantalla por fotos nuevas
define('TOAST_SECONDS', 5);              // notificación estilo MSN
define('FEATURE_SECONDS', 9);            // foto destacada (8–10 s)

// --- Sesión / seguridad ---
define('SESSION_NAME', 'ALEJOFEST_SESS');
define('ADMIN_MAX_ATTEMPTS', 6);         // intentos de login por IP
define('ADMIN_ATTEMPT_WINDOW_MIN', 10);  // en esta ventana de minutos

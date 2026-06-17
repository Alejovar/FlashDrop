<?php
// api/loop_cambiar.php — admin cambia el video activo en pantalla.php.

require_once dirname(__DIR__) . '/inc/auth.php';
af_security_headers();
af_require_admin_api();

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    af_json(['ok' => false, 'error' => 'Método no permitido'], 405);
}
af_csrf_require();

$archivo = trim($_POST['archivo'] ?? '');

// Validar formato y que exista en video/loops/
if (!preg_match('/^[a-zA-Z0-9_\-\.]+\.(mp4|webm|mov)$/i', $archivo)) {
    af_json(['ok' => false, 'error' => 'Nombre de archivo inválido'], 400);
}

$ruta = dirname(__DIR__) . '/video/loops/' . $archivo;
if (!file_exists($ruta)) {
    af_json(['ok' => false, 'error' => 'El video no existe en el servidor'], 404);
}

$db = af_db();
$stmt = $db->prepare(
    'INSERT INTO sistema_config (clave, valor) VALUES (?, ?)
     ON DUPLICATE KEY UPDATE valor = ?'
);
$stmt->execute(['loop_actual', $archivo, $archivo]);

af_json(['ok' => true, 'loop_actual' => $archivo]);

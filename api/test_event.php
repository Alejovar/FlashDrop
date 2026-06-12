<?php
// api/test_event.php — dispara una animación de prueba en pantalla.php.
// Solo admins. POST { action: 'toast'|'milestone', quantity: N (opcional) }

require_once dirname(__DIR__) . '/inc/auth.php';
af_security_headers();
af_require_admin_api();

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    af_json(['ok' => false, 'error' => 'Método no permitido'], 405);
}
af_csrf_require();

$type     = $_POST['type']     ?? '';
$quantity = (int)($_POST['quantity'] ?? 15);

if (!in_array($type, ['toast', 'milestone'], true)) {
    af_json(['ok' => false, 'error' => 'Tipo inválido'], 400);
}

$db = af_db();

// Elegir una foto aleatoria visible para la animación
$photo = $db->query(
    'SELECT id, filename, orientation FROM photos WHERE visible = 1 ORDER BY RAND() LIMIT 1'
)->fetch();

if (!$photo) {
    af_json(['ok' => false, 'error' => 'No hay fotos disponibles para la prueba'], 404);
}

$stmt = $db->prepare(
    'INSERT INTO test_events (type, photo_id, quantity) VALUES (?, ?, ?)'
);
$stmt->execute([$type, (int)$photo['id'], $quantity]);

af_json(['ok' => true, 'type' => $type, 'photoId' => (int)$photo['id']]);

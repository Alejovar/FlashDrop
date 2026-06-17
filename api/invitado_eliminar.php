<?php
// api/invitado_eliminar.php — admin elimina permanentemente un invitado.

require_once dirname(__DIR__) . '/inc/auth.php';
af_security_headers();
af_require_admin_api();

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    af_json(['ok' => false, 'error' => 'Método no permitido'], 405);
}
af_csrf_require();

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    af_json(['ok' => false, 'error' => 'ID inválido'], 400);
}

$db = af_db();

$check = $db->prepare('SELECT id FROM invitados WHERE id = ? LIMIT 1');
$check->execute([$id]);
if (!$check->fetch()) {
    af_json(['ok' => false, 'error' => 'Invitado no encontrado'], 404);
}

$stmt = $db->prepare('DELETE FROM invitados WHERE id = ?');
$stmt->execute([$id]);

af_json(['ok' => true, 'id' => $id]);

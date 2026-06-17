<?php
// api/invitado_toggle.php — admin habilita/deshabilita un invitado.

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

$check = $db->prepare('SELECT habilitado FROM invitados WHERE id = ? LIMIT 1');
$check->execute([$id]);
$row = $check->fetch();

if (!$row) {
    af_json(['ok' => false, 'error' => 'Invitado no encontrado'], 404);
}

$nuevoEstado = $row['habilitado'] ? 0 : 1;
$stmt = $db->prepare('UPDATE invitados SET habilitado = ? WHERE id = ?');
$stmt->execute([$nuevoEstado, $id]);

af_json(['ok' => true, 'id' => $id, 'habilitado' => (bool)$nuevoEstado]);

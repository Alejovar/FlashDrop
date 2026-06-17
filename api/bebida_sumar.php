<?php
// api/bebida_sumar.php — incrementa el contador de bebidas de un invitado.
// Sin auth: lo usa la tablet pública en la mesa de bebidas.

require_once dirname(__DIR__) . '/inc/helpers.php';
af_security_headers();

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    af_json(['ok' => false, 'error' => 'Método no permitido'], 405);
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    af_json(['ok' => false, 'error' => 'ID inválido'], 400);
}

$db = af_db();

$check = $db->prepare('SELECT id, habilitado FROM invitados WHERE id = ? LIMIT 1');
$check->execute([$id]);
$row = $check->fetch();

if (!$row) {
    af_json(['ok' => false, 'error' => 'Invitado no encontrado'], 404);
}
if (!$row['habilitado']) {
    af_json(['ok' => false, 'error' => 'Invitado deshabilitado'], 403);
}

$stmt = $db->prepare('UPDATE invitados SET bebidas = bebidas + 1 WHERE id = ?');
$stmt->execute([$id]);

$res = $db->prepare('SELECT bebidas FROM invitados WHERE id = ?');
$res->execute([$id]);
$bebidas = (int)$res->fetchColumn();

af_json(['ok' => true, 'id' => $id, 'bebidas' => $bebidas]);

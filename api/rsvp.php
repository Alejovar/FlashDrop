<?php
// api/rsvp.php — confirmar asistencia desde la invitación. Público, sin auth.

require_once dirname(__DIR__) . '/inc/helpers.php';
af_security_headers();

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    af_json(['ok' => false, 'error' => 'Método no permitido'], 405);
}

$nombre = trim($_POST['nombre'] ?? '');

if ($nombre === '') {
    af_json(['ok' => false, 'error' => 'El nombre es requerido'], 400);
}
if (mb_strlen($nombre) > 80) {
    af_json(['ok' => false, 'error' => 'Nombre demasiado largo'], 400);
}

$db = af_db();

// Evitar duplicados exactos (mismo nombre, case-insensitive)
$check = $db->prepare('SELECT id, habilitado FROM invitados WHERE LOWER(nombre) = LOWER(?) LIMIT 1');
$check->execute([$nombre]);
$existing = $check->fetch();

if ($existing) {
    // Si ya existía pero estaba deshabilitado, lo reactivamos
    if (!$existing['habilitado']) {
        $upd = $db->prepare('UPDATE invitados SET habilitado = 1 WHERE id = ?');
        $upd->execute([(int)$existing['id']]);
    }
    af_json(['ok' => true, 'id' => (int)$existing['id'], 'yaExistia' => true]);
}

$stmt = $db->prepare('INSERT INTO invitados (nombre, confirmado, habilitado) VALUES (?, 1, 1)');
$stmt->execute([$nombre]);

af_json(['ok' => true, 'id' => (int)$db->lastInsertId(), 'yaExistia' => false]);

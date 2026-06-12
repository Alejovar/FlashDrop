<?php
// api/admin_action.php v2 — ocultar, restaurar, eliminar, replay.

require_once dirname(__DIR__) . '/inc/auth.php';
af_security_headers();
af_require_admin_api();

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    af_json(['ok' => false, 'error' => 'Método no permitido'], 405);
}
af_csrf_require();

$action  = $_POST['action']   ?? '';
$photoId = (int)($_POST['photo_id'] ?? 0);
if ($photoId <= 0) {
    af_json(['ok' => false, 'error' => 'Foto inválida'], 400);
}

$db = af_db();
$row = $db->prepare('SELECT id, filename FROM photos WHERE id = ?');
$row->execute([$photoId]);
$photo = $row->fetch();
if (!$photo) {
    af_json(['ok' => false, 'error' => 'La foto no existe'], 404);
}

switch ($action) {
    case 'hide':
        $db->prepare('UPDATE photos SET visible = 0 WHERE id = ?')->execute([$photoId]);
        af_json(['ok' => true, 'visible' => 0]);

    case 'restore':
        $db->prepare('UPDATE photos SET visible = 1 WHERE id = ?')->execute([$photoId]);
        af_json(['ok' => true, 'visible' => 1]);

    case 'delete':
        // Eliminar permanentemente
        $db->prepare('DELETE FROM photos WHERE id = ?')->execute([$photoId]);
        $filePath = rtrim(UPLOADS_DIR, '/') . '/' . $photo['filename'];
        if (file_exists($filePath)) @unlink($filePath);
        af_json(['ok' => true, 'deleted' => true]);

    case 'replay':
        $db->prepare('INSERT INTO screen_queue (photo_id) VALUES (?)')->execute([$photoId]);
        af_json(['ok' => true, 'queued' => true]);

    default:
        af_json(['ok' => false, 'error' => 'Acción desconocida'], 400);
}

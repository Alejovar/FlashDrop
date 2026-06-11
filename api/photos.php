<?php
// api/photos.php — lista de fotos visibles para la galería (paginada, más recientes primero).
require_once dirname(__DIR__) . '/inc/helpers.php';
af_security_headers();

$db = af_db();
$before = isset($_GET['before']) ? max(0, (int)$_GET['before']) : 0;
$limit  = 24;

if ($before > 0) {
    $stmt = $db->prepare('SELECT id, filename, orientation FROM photos WHERE visible = 1 AND id < ? ORDER BY id DESC LIMIT ' . $limit);
    $stmt->execute([$before]);
} else {
    $stmt = $db->query('SELECT id, filename, orientation FROM photos WHERE visible = 1 ORDER BY id DESC LIMIT ' . $limit);
}

$rows = [];
foreach ($stmt->fetchAll() as $r) {
    $rows[] = [
        'id'  => (int)$r['id'],
        'url' => UPLOADS_URL . '/' . $r['filename'],
        'orientation' => $r['orientation'],
    ];
}
af_json(['ok' => true, 'photos' => $rows, 'hasMore' => count($rows) === $limit]);

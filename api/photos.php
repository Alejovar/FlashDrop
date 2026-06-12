<?php
// api/photos.php v2 — lista fotos visibles. Soporta ?before= y ?after= para paginación.
require_once dirname(__DIR__) . '/inc/helpers.php';
af_security_headers();

$db     = af_db();
$limit  = 24;

if (isset($_GET['after'])) {
    // Fotos nuevas más recientes que ?after=id
    $after = max(0, (int)$_GET['after']);
    $stmt  = $db->prepare('SELECT id, filename, orientation FROM photos WHERE visible = 1 AND id > ? ORDER BY id ASC LIMIT ' . $limit);
    $stmt->execute([$after]);
    $rows  = [];
    foreach ($stmt->fetchAll() as $r) {
        $rows[] = ['id' => (int)$r['id'], 'url' => UPLOADS_URL . '/' . $r['filename'], 'orientation' => $r['orientation']];
    }
    af_json(['ok' => true, 'photos' => $rows]);
} elseif (isset($_GET['before']) && (int)$_GET['before'] > 0) {
    $before = (int)$_GET['before'];
    $stmt   = $db->prepare('SELECT id, filename, orientation FROM photos WHERE visible = 1 AND id < ? ORDER BY id DESC LIMIT ' . $limit);
    $stmt->execute([$before]);
    $rows   = [];
    foreach ($stmt->fetchAll() as $r) {
        $rows[] = ['id' => (int)$r['id'], 'url' => UPLOADS_URL . '/' . $r['filename'], 'orientation' => $r['orientation']];
    }
    af_json(['ok' => true, 'photos' => $rows, 'hasMore' => count($rows) === $limit]);
} else {
    $stmt = $db->query('SELECT id, filename, orientation FROM photos WHERE visible = 1 ORDER BY id DESC LIMIT ' . $limit);
    $rows = [];
    foreach ($stmt->fetchAll() as $r) {
        $rows[] = ['id' => (int)$r['id'], 'url' => UPLOADS_URL . '/' . $r['filename'], 'orientation' => $r['orientation']];
    }
    af_json(['ok' => true, 'photos' => $rows, 'hasMore' => count($rows) === $limit]);
}

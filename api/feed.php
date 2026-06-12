<?php
// api/feed.php v2 — cola FIFO + milestones pendientes.
// Devuelve nuevas fotos E informa si hay un logro nuevo para la pantalla.

require_once dirname(__DIR__) . '/inc/helpers.php';
af_security_headers();

$db    = af_db();
$after = isset($_GET['after']) ? max(0, (int)$_GET['after']) : 0;

if ($after === 0) {
    $row = $db->query('SELECT COALESCE(MAX(id),0) m FROM screen_queue')->fetch();
    // También devolver el total actual para el HUD inicial
    $total = (int)$db->query('SELECT COUNT(*) FROM photos WHERE visible = 1')->fetchColumn();
    af_json(['ok' => true, 'cursor' => (int)$row['m'], 'items' => [], 'total' => $total]);
}

$stmt = $db->prepare(
    'SELECT q.id qid, p.id pid, p.filename, p.orientation, p.width, p.height
     FROM screen_queue q
     JOIN photos p ON p.id = q.photo_id
     WHERE q.id > ? AND p.visible = 1
     ORDER BY q.id ASC
     LIMIT 20'
);
$stmt->execute([$after]);

$items  = [];
$cursor = $after;
foreach ($stmt->fetchAll() as $r) {
    $cursor  = (int)$r['qid'];
    $items[] = [
        'queueId'     => (int)$r['qid'],
        'photoId'     => (int)$r['pid'],
        'url'         => UPLOADS_URL . '/' . $r['filename'],
        'orientation' => $r['orientation'],
        'width'       => (int)$r['width'],
        'height'      => (int)$r['height'],
    ];
}

// Total actualizado (para HUD)
$total = (int)$db->query('SELECT COUNT(*) FROM photos WHERE visible = 1')->fetchColumn();

// Milestone pendiente de mostrar en pantalla:
// El más reciente que fue disparado DESPUÉS del cursor actual
// (lo identificamos porque el photo_id coincide con algún item nuevo)
$milestone = null;
if (!empty($items)) {
    $newPhotoIds = array_column($items, 'photoId');
    $placeholders = implode(',', array_fill(0, count($newPhotoIds), '?'));
    $mStmt = $db->prepare(
        'SELECT quantity, photo_id FROM milestones WHERE photo_id IN (' . $placeholders . ') ORDER BY quantity DESC LIMIT 1'
    );
    $mStmt->execute($newPhotoIds);
    $mRow = $mStmt->fetch();
    if ($mRow) {
        // Devolver la URL del original para la animación
        $pStmt = $db->prepare('SELECT filename, orientation FROM photos WHERE id = ?');
        $pStmt->execute([(int)$mRow['photo_id']]);
        $pRow = $pStmt->fetch();
        if ($pRow) {
            $milestone = [
                'quantity'    => (int)$mRow['quantity'],
                'photoId'     => (int)$mRow['photo_id'],
                'url'         => UPLOADS_URL . '/' . $pRow['filename'],
                'orientation' => $pRow['orientation'],
            ];
        }
    }
}

af_json([
    'ok'       => true,
    'cursor'   => $cursor,
    'items'    => $items,
    'total'    => $total,
    'milestone' => $milestone,
]);

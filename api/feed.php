<?php
// api/feed.php v2 — cola FIFO + milestones + test events del admin.

require_once dirname(__DIR__) . '/inc/helpers.php';
af_security_headers();

$db    = af_db();
$after = isset($_GET['after']) ? max(0, (int)$_GET['after']) : 0;

// Cursor para test_events (separado del cursor de screen_queue)
$afterTest = isset($_GET['afterTest']) ? max(0, (int)$_GET['afterTest']) : 0;

if ($after === 0) {
    $row      = $db->query('SELECT COALESCE(MAX(id),0) m FROM screen_queue')->fetch();
    $rowTest  = $db->query('SELECT COALESCE(MAX(id),0) m FROM test_events')->fetch();
    $total    = (int)$db->query('SELECT COUNT(*) FROM photos WHERE visible = 1')->fetchColumn();
    af_json([
        'ok'         => true,
        'cursor'     => (int)$row['m'],
        'cursorTest' => (int)$rowTest['m'],
        'items'      => [],
        'total'      => $total,
    ]);
}

// --- Nuevas fotos en cola ---
$stmt = $db->prepare(
    'SELECT q.id qid, p.id pid, p.filename, p.orientation, p.width, p.height
     FROM screen_queue q
     JOIN photos p ON p.id = q.photo_id
     WHERE q.id > ? AND p.visible = 1
     ORDER BY q.id ASC LIMIT 20'
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

$total = (int)$db->query('SELECT COUNT(*) FROM photos WHERE visible = 1')->fetchColumn();

// --- Milestone real (foto nueva que coincide con hito) ---
$milestone = null;
if (!empty($items)) {
    $newPhotoIds  = array_column($items, 'photoId');
    $placeholders = implode(',', array_fill(0, count($newPhotoIds), '?'));
    $mStmt = $db->prepare(
        'SELECT quantity, photo_id FROM milestones WHERE photo_id IN (' . $placeholders . ') ORDER BY quantity DESC LIMIT 1'
    );
    $mStmt->execute($newPhotoIds);
    $mRow = $mStmt->fetch();
    if ($mRow) {
        $pStmt = $db->prepare('SELECT filename, orientation FROM photos WHERE id = ?');
        $pStmt->execute([(int)$mRow['photo_id']]);
        $pRow = $pStmt->fetch();
        if ($pRow) {
            $milestone = [
                'quantity'    => (int)$mRow['quantity'],
                'photoId'     => (int)$mRow['photo_id'],
                'url'         => UPLOADS_URL . '/' . $pRow['filename'],
                'orientation' => $pRow['orientation'],
                'isTest'      => false,
            ];
        }
    }
}

// --- Test events del admin (pruebas de animación) ---
$testEvent   = null;
$cursorTest  = $afterTest;
if ($afterTest >= 0) {
    $tStmt = $db->prepare(
        'SELECT te.id, te.type, te.quantity, p.filename, p.orientation
         FROM test_events te
         JOIN photos p ON p.id = te.photo_id
         WHERE te.id > ?
         ORDER BY te.id ASC LIMIT 1'
    );
    $tStmt->execute([$afterTest]);
    $tRow = $tStmt->fetch();
    if ($tRow) {
        $cursorTest = (int)$tRow['id'];
        $testEvent  = [
            'type'        => $tRow['type'],
            'quantity'    => (int)($tRow['quantity'] ?? 15),
            'url'         => UPLOADS_URL . '/' . $tRow['filename'],
            'orientation' => $tRow['orientation'],
            'isTest'      => true,
        ];
    }
}

// Si hay test milestone, tiene prioridad sobre el real en este ciclo
if ($testEvent && $testEvent['type'] === 'milestone') {
    $milestone = $testEvent;
} elseif ($testEvent && $testEvent['type'] === 'toast' && empty($items)) {
    // Inyectar como item de cola MSN
    $items[] = [
        'queueId'     => 0,
        'photoId'     => 0,
        'url'         => $testEvent['url'],
        'orientation' => $testEvent['orientation'],
        'width'       => 0,
        'height'      => 0,
        'isTest'      => true,
    ];
}

af_json([
    'ok'         => true,
    'cursor'     => $cursor,
    'cursorTest' => $cursorTest,
    'items'      => $items,
    'total'      => $total,
    'milestone'  => $milestone,
]);

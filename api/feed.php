<?php
// api/feed.php — cola FIFO de la pantalla grande.
// La pantalla manda ?after=<último id de cola visto> y recibe lo nuevo EN ORDEN DE LLEGADA.
require_once dirname(__DIR__) . '/inc/helpers.php';
af_security_headers();

$db = af_db();
$after = isset($_GET['after']) ? max(0, (int)$_GET['after']) : 0;

if ($after === 0) {
    // Primera carga de la pantalla: no re-reproducir todo el historial,
    // solo devolver el último id para empezar a escuchar desde ahí.
    $row = $db->query('SELECT COALESCE(MAX(id),0) m FROM screen_queue')->fetch();
    af_json(['ok' => true, 'cursor' => (int)$row['m'], 'items' => []]);
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
    $cursor = (int)$r['qid'];
    $items[] = [
        'queueId'     => (int)$r['qid'],
        'photoId'     => (int)$r['pid'],
        'url'         => UPLOADS_URL . '/' . $r['filename'],
        'orientation' => $r['orientation'],
        'width'       => (int)$r['width'],
        'height'      => (int)$r['height'],
    ];
}
af_json(['ok' => true, 'cursor' => $cursor, 'items' => $items]);

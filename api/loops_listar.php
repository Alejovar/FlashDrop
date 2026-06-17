<?php
// api/loops_listar.php — lista los videos disponibles en video/loops/ + el actual.

require_once dirname(__DIR__) . '/inc/helpers.php';
af_security_headers();

$db = af_db();

$stmt = $db->prepare('SELECT valor FROM sistema_config WHERE clave = ? LIMIT 1');
$stmt->execute(['loop_actual']);
$actual = $stmt->fetchColumn() ?: 'loop.mp4';

$dir = dirname(__DIR__) . '/video/loops';
$videos = [];

if (is_dir($dir)) {
    foreach (scandir($dir) as $f) {
        if (preg_match('/\.(mp4|webm|mov)$/i', $f)) {
            $videos[] = $f;
        }
    }
    sort($videos);
}

af_json([
    'ok'     => true,
    'actual' => $actual,
    'videos' => $videos,
]);

<?php
// api/download.php — descarga el original. Solo para administradores.
require_once dirname(__DIR__) . '/inc/auth.php';
af_security_headers();
af_require_admin_api();

$photoId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($photoId <= 0) { http_response_code(400); exit; }

$db   = af_db();
$stmt = $db->prepare('SELECT filename FROM photos WHERE id = ?');
$stmt->execute([$photoId]);
$photo = $stmt->fetch();
if (!$photo) { http_response_code(404); exit; }

$path = rtrim(UPLOADS_DIR, '/') . '/' . $photo['filename'];
if (!file_exists($path)) { http_response_code(404); exit; }

header('Content-Type: image/jpeg');
header('Content-Disposition: attachment; filename="AlejoFest_Vol21_' . $photoId . '_original.jpg"');
header('Content-Length: ' . filesize($path));
header('Cache-Control: no-store');
readfile($path);
exit;

<?php
// api/upload.php — recibe la foto, la valida, la re-codifica (elimina metadatos
// y cualquier payload incrustado), le estampa la marca de agua y la encola FIFO.

require_once dirname(__DIR__) . '/inc/helpers.php';
af_session_start();
af_security_headers();

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    af_json(['ok' => false, 'error' => 'Método no permitido'], 405);
}
af_csrf_require();

// ---------- Rate limit por IP ----------
$db = af_db();
$stmt = $db->prepare('SELECT COUNT(*) c FROM photos WHERE uploader_ip = ? AND created_at > (NOW() - INTERVAL 1 MINUTE)');
$stmt->execute([af_client_ip_bin()]);
if ((int)$stmt->fetch()['c'] >= UPLOADS_PER_MINUTE_PER_IP) {
    af_json(['ok' => false, 'error' => 'Tranqui 😅 estás subiendo muy rápido. Espera un minuto.'], 429);
}

// ---------- Validación del archivo ----------
if (empty($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
    af_json(['ok' => false, 'error' => 'No se recibió ninguna foto.'], 400);
}
$file = $_FILES['photo'];

if ($file['size'] > MAX_UPLOAD_MB * 1024 * 1024) {
    af_json(['ok' => false, 'error' => 'La foto pesa más de ' . MAX_UPLOAD_MB . ' MB.'], 413);
}
if (!is_uploaded_file($file['tmp_name'])) {
    af_json(['ok' => false, 'error' => 'Subida inválida.'], 400);
}

// MIME real (no confiar en la extensión ni en el header del cliente)
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime  = $finfo->file($file['tmp_name']);
$allowed = ['image/jpeg', 'image/png', 'image/webp'];
if (!in_array($mime, $allowed, true)) {
    af_json(['ok' => false, 'error' => 'Formato no soportado. Usa JPG, PNG o WebP.'], 415);
}
$info = @getimagesize($file['tmp_name']);
if ($info === false) {
    af_json(['ok' => false, 'error' => 'El archivo no es una imagen válida.'], 415);
}

// ---------- Cargar imagen ----------
switch ($mime) {
    case 'image/jpeg': $img = @imagecreatefromjpeg($file['tmp_name']); break;
    case 'image/png':  $img = @imagecreatefrompng($file['tmp_name']);  break;
    case 'image/webp': $img = @imagecreatefromwebp($file['tmp_name']); break;
    default:           $img = false;
}
if (!$img) {
    af_json(['ok' => false, 'error' => 'No se pudo procesar la imagen.'], 415);
}

// Rotación según EXIF (fotos de celular en vertical)
if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
    $exif = @exif_read_data($file['tmp_name']);
    if (!empty($exif['Orientation'])) {
        switch ((int)$exif['Orientation']) {
            case 3: $img = imagerotate($img, 180, 0); break;
            case 6: $img = imagerotate($img, -90, 0); break;
            case 8: $img = imagerotate($img,  90, 0); break;
        }
    }
}

// ---------- Reescalar (lado mayor = MAX_DIMENSION) ----------
$w = imagesx($img);
$h = imagesy($img);
if (max($w, $h) > MAX_DIMENSION) {
    $ratio = MAX_DIMENSION / max($w, $h);
    $nw = (int)round($w * $ratio);
    $nh = (int)round($h * $ratio);
    $resized = imagecreatetruecolor($nw, $nh);
    imagecopyresampled($resized, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
    imagedestroy($img);
    $img = $resized;
    $w = $nw; $h = $nh;
}

// ---------- Marca de agua ----------
if (!file_exists(WATERMARK_PATH)) {
    imagedestroy($img);
    af_json(['ok' => false, 'error' => 'Falta el PNG de marca de agua en el servidor.'], 500);
}
$wm = @imagecreatefrompng(WATERMARK_PATH);
if (!$wm) {
    imagedestroy($img);
    af_json(['ok' => false, 'error' => 'No se pudo cargar la marca de agua.'], 500);
}
imagealphablending($wm, true);
imagesavealpha($wm, true);

$wmW = imagesx($wm);
$wmH = imagesy($wm);
$targetW = (int)round($w * WATERMARK_SCALE);
$targetH = (int)round($wmH * ($targetW / $wmW));

// Abajo al centro, con margen proporcional
$margin = (int)round(min($w, $h) * WATERMARK_MARGIN);
$dstX = (int)round(($w - $targetW) / 2);
$dstY = $h - $targetH - $margin;

$wmScaled = imagecreatetruecolor($targetW, $targetH);
imagealphablending($wmScaled, false);
imagesavealpha($wmScaled, true);
$transparent = imagecolorallocatealpha($wmScaled, 0, 0, 0, 127);
imagefill($wmScaled, 0, 0, $transparent);
imagecopyresampled($wmScaled, $wm, 0, 0, 0, 0, $targetW, $targetH, $wmW, $wmH);
imagedestroy($wm);

imagealphablending($img, true);
imagecopy($img, $wmScaled, $dstX, $dstY, 0, 0, $targetW, $targetH);
imagedestroy($wmScaled);

// ---------- Guardar con nombre aleatorio (re-codificado a JPEG limpio) ----------
$filename = bin2hex(random_bytes(16)) . '.jpg';
$destPath = rtrim(UPLOADS_DIR, '/') . '/' . $filename;
if (!imagejpeg($img, $destPath, JPEG_QUALITY)) {
    imagedestroy($img);
    af_json(['ok' => false, 'error' => 'No se pudo guardar la foto.'], 500);
}
imagedestroy($img);
@chmod($destPath, 0644);

$orientation = $w > $h ? 'horizontal' : ($w < $h ? 'vertical' : 'cuadrada');

// ---------- Registrar y encolar (FIFO para la pantalla) ----------
$db->beginTransaction();
try {
    $db->prepare('INSERT INTO photos (filename, width, height, orientation, uploader_ip) VALUES (?,?,?,?,?)')
       ->execute([$filename, $w, $h, $orientation, af_client_ip_bin()]);
    $photoId = (int)$db->lastInsertId();
    $db->prepare('INSERT INTO screen_queue (photo_id) VALUES (?)')->execute([$photoId]);
    $db->commit();
} catch (Throwable $t) {
    $db->rollBack();
    @unlink($destPath);
    af_json(['ok' => false, 'error' => 'Error al registrar la foto.'], 500);
}

af_json([
    'ok'  => true,
    'url' => UPLOADS_URL . '/' . $filename,
    'orientation' => $orientation,
]);

<?php
// api/upload.php v2 — recibe la foto (solo desde cámara), valida, guarda el ORIGINAL
// sin marca de agua, y detecta si se alcanza un milestone.

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
    af_json(['ok' => false, 'error' => 'Estás subiendo muy rápido. Espera un minuto.'], 429);
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

$finfo   = new finfo(FILEINFO_MIME_TYPE);
$mime    = $finfo->file($file['tmp_name']);
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

// Rotación EXIF (fotos de celular)
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
    $nw    = (int)round($w * $ratio);
    $nh    = (int)round($h * $ratio);
    $resized = imagecreatetruecolor($nw, $nh);
    imagecopyresampled($resized, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
    imagedestroy($img);
    $img = $resized;
    $w = $nw; $h = $nh;
}

// ---------- Guardar ORIGINAL (sin marca de agua) ----------
if (!is_dir(UPLOADS_DIR)) {
    mkdir(UPLOADS_DIR, 0775, true);
}

$filename = bin2hex(random_bytes(16)) . '.jpg';
$destPath = rtrim(UPLOADS_DIR, '/') . '/' . $filename;

if (!imagejpeg($img, $destPath, JPEG_QUALITY)) {
    imagedestroy($img);
    af_json(['ok' => false, 'error' => 'No se pudo guardar la foto.'], 500);
}
imagedestroy($img);
@chmod($destPath, 0644);

$orientation = $w > $h ? 'horizontal' : ($w < $h ? 'vertical' : 'cuadrada');

// ---------- Registrar, encolar y detectar milestone ----------
$db->beginTransaction();
try {
    $db->prepare('INSERT INTO photos (filename, width, height, orientation, uploader_ip) VALUES (?,?,?,?,?)')
       ->execute([$filename, $w, $h, $orientation, af_client_ip_bin()]);
    $photoId = (int)$db->lastInsertId();

    // Total de fotos visible → detectar milestone
    $totalRow   = $db->query('SELECT COUNT(*) c FROM photos')->fetch();
    $total      = (int)$totalRow['c'];
    $milestone  = 0;

    if ($total % MILESTONE_EVERY === 0) {
        // Verificar que no esté ya registrado
        $mCheck = $db->prepare('SELECT id FROM milestones WHERE quantity = ?');
        $mCheck->execute([$total]);
        if (!$mCheck->fetch()) {
            $db->prepare('INSERT INTO milestones (quantity, photo_id) VALUES (?,?)')
               ->execute([$total, $photoId]);
            $milestone = $total;
        }
    }

    $db->prepare('INSERT INTO screen_queue (photo_id) VALUES (?)')->execute([$photoId]);
    $db->commit();
} catch (Throwable $t) {
    $db->rollBack();
    @unlink($destPath);
    af_json(['ok' => false, 'error' => 'Error al registrar la foto.'], 500);
}

af_json([
    'ok'          => true,
    'url'         => UPLOADS_URL . '/' . $filename,
    'orientation' => $orientation,
    'photoId'     => $photoId,
    'milestone'   => $milestone,   // 0 = sin logro, N = logro N memories
]);

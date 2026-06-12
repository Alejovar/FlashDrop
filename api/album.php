<?php
// api/album.php — genera AlejoFest_Vol21.zip con /originales/ y /polaroids/
// Solo accesible para administradores autenticados.

require_once dirname(__DIR__) . '/inc/auth.php';
af_security_headers();
af_require_admin_api();

$db = af_db();
$fotos = $db->query('SELECT id, filename, width, height FROM photos WHERE visible = 1 ORDER BY id ASC')->fetchAll();

if (empty($fotos)) {
    af_json(['ok' => false, 'error' => 'No hay fotos visibles para descargar'], 404);
}

// ---------- Crear ZIP en memoria ----------
$tmpZip = tempnam(sys_get_temp_dir(), 'flashdrop_album_');
$zip    = new ZipArchive();
if ($zip->open($tmpZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    af_json(['ok' => false, 'error' => 'No se pudo crear el ZIP'], 500);
}

foreach ($fotos as $photo) {
    $origPath = rtrim(UPLOADS_DIR, '/') . '/' . $photo['filename'];

    // --- Original ---
    if (file_exists($origPath)) {
        $zip->addFile($origPath, 'originales/' . $photo['filename']);
    }

    // --- Polaroid generada al vuelo ---
    $src = @imagecreatefromjpeg($origPath);
    if (!$src) continue;

    $srcW = imagesx($src);
    $srcH = imagesy($src);

    $border  = max(24, (int)round($srcW * 0.035));
    $bottomH = max(80, (int)round($srcH * 0.18));
    $polW    = $srcW + $border * 2;
    $polH    = $srcH + $border + $bottomH;

    $polaroid = imagecreatetruecolor($polW, $polH);
    $white    = imagecolorallocate($polaroid, 255, 255, 255);
    imagefill($polaroid, 0, 0, $white);
    imagecopyresampled($polaroid, $src, $border, $border, 0, 0, $srcW, $srcH, $srcW, $srcH);
    imagedestroy($src);

    if (file_exists(LOGO_PATH)) {
        $logo = @imagecreatefrompng(LOGO_PATH);
        if ($logo) {
            imagealphablending($logo, true);
            imagesavealpha($logo, true);
            $logoW = imagesx($logo);
            $logoH = imagesy($logo);
            $targetLogoW = (int)round($polW * 0.40);
            $targetLogoH = (int)round($logoH * ($targetLogoW / $logoW));
            $logoScaled = imagecreatetruecolor($targetLogoW, $targetLogoH);
            imagealphablending($logoScaled, false);
            imagesavealpha($logoScaled, true);
            $trans = imagecolorallocatealpha($logoScaled, 255, 255, 255, 127);
            imagefill($logoScaled, 0, 0, $trans);
            imagecopyresampled($logoScaled, $logo, 0, 0, 0, 0, $targetLogoW, $targetLogoH, $logoW, $logoH);
            imagedestroy($logo);
            $logoX = (int)round(($polW - $targetLogoW) / 2);
            $logoY = $srcH + $border + (int)round(($bottomH - $targetLogoH) / 2);
            imagealphablending($polaroid, true);
            imagecopy($polaroid, $logoScaled, $logoX, $logoY, 0, 0, $targetLogoW, $targetLogoH);
            imagedestroy($logoScaled);
        }
    }

    ob_start();
    imagejpeg($polaroid, null, 94);
    $polBytes = ob_get_clean();
    imagedestroy($polaroid);

    $polName = pathinfo($photo['filename'], PATHINFO_FILENAME) . '_polaroid.jpg';
    $zip->addFromString('polaroids/' . $polName, $polBytes);
}

$zip->close();

$zipSize = filesize($tmpZip);
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="AlejoFest_Vol21.zip"');
header('Content-Length: ' . $zipSize);
header('Cache-Control: no-store');
readfile($tmpZip);
@unlink($tmpZip);
exit;

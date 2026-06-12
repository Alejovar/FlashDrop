<?php
// api/polaroid.php — genera y devuelve una imagen Polaroid en tiempo real.
// GET ?id=<photo_id>          → para invitados (solo si visible=1)
// GET ?id=<photo_id>&admin=1  → para admins (cualquier foto)
//
// La Polaroid se genera dinámicamente, NO se almacena en disco.

require_once dirname(__DIR__) . '/inc/helpers.php';
require_once dirname(__DIR__) . '/inc/auth.php';
af_security_headers();

$photoId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($photoId <= 0) {
    http_response_code(400);
    exit('ID inválido');
}

$isAdmin = af_admin_logged();
$db      = af_db();

$stmt = $db->prepare('SELECT filename, width, height FROM photos WHERE id = ?' . ($isAdmin ? '' : ' AND visible = 1'));
$stmt->execute([$photoId]);
$photo = $stmt->fetch();

if (!$photo) {
    http_response_code(404);
    exit('Foto no encontrada');
}

$origPath = rtrim(UPLOADS_DIR, '/') . '/' . $photo['filename'];
if (!file_exists($origPath)) {
    http_response_code(404);
    exit('Archivo no encontrado');
}

// ---------- Cargar imagen original ----------
$src = @imagecreatefromjpeg($origPath);
if (!$src) {
    http_response_code(500);
    exit('No se pudo cargar la imagen');
}

$srcW = imagesx($src);
$srcH = imagesy($src);

// ---------- Dimensiones Polaroid ----------
// El marco superior es cuadrado; margen lateral y superior uniforme,
// margen inferior más grande para el texto del evento.
$border   = max(24, (int)round($srcW * 0.035));
$bottomH  = max(80, (int)round($srcH * 0.18));

// Usamos el ancho real de la foto como referencia; la imagen va dentro al 100%
$polW = $srcW + $border * 2;
$polH = $srcH + $border + $bottomH;

$polaroid = imagecreatetruecolor($polW, $polH);

// Fondo blanco tipo Polaroid
$white  = imagecolorallocate($polaroid, 255, 255, 255);
$shadow = imagecolorallocatealpha($polaroid, 0, 0, 0, 80);
imagefill($polaroid, 0, 0, $white);

// Pegar foto
imagecopyresampled($polaroid, $src, $border, $border, 0, 0, $srcW, $srcH, $srcW, $srcH);
imagedestroy($src);

// ---------- Logo en el área inferior ----------
if (file_exists(LOGO_PATH)) {
    $logo = @imagecreatefrompng(LOGO_PATH);
    if ($logo) {
        imagealphablending($logo, true);
        imagesavealpha($logo, true);

        $logoW = imagesx($logo);
        $logoH = imagesy($logo);

        // Logo ocupa ~40% del ancho de la Polaroid, centrado en el bloque inferior
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

// ---------- Enviar como descarga ----------
$downloadName = 'AlejoFest_Vol21_' . $photoId . '_polaroid.jpg';
header('Content-Type: image/jpeg');
header('Content-Disposition: attachment; filename="' . $downloadName . '"');
header('Cache-Control: no-store');
imagejpeg($polaroid, null, 94);
imagedestroy($polaroid);
exit;

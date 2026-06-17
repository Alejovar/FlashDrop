<?php
require_once __DIR__ . '/inc/helpers.php';
af_session_start();

// CSP personalizado para permitir Leaflet
header("Content-Security-Policy: default-src 'self'; script-src 'self' https://cdnjs.cloudflare.com 'unsafe-inline'; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; img-src 'self' data: https:; font-src 'self';");
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');

$evento = [
    'nombre'    => 'AlejoFest Vol.21',
    'fecha'     => 'Domingo 2 de Agosto',
    'hora'      => '8:00 PM',
    'dress'     => 'Y2K Mode ON',
    'direccion' => 'Francisco Márquez 119, Saltillo, Coahuila 25084',
    'lat'       => 25.4290,
    'lng'       => -101.0032,
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $evento['nombre'] ?> — Invitación</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
<link rel="stylesheet" href="assets/css/y2k.css">
<link rel="stylesheet" href="assets/css/invitacion.css">
</head>
<body class="inv-body">

<!-- VIDEO LOOP ARRIBA -->
<div class="inv-video-section">
    <video class="inv-video" src="video/loop.mp4" autoplay muted loop playsinline></video>
</div>

<!-- IMAGEN INVITACIÓN -->
<div class="inv-container">
    <div class="inv-image-wrapper">
        <img src="assets/img/invitacion.png" alt="Invitación AlejoFest Vol.21" class="inv-image">
    </div>

    <!-- MAPA CON LOGO COMO MARCADOR -->
    <div class="inv-map-section">
        <div id="inv-map" class="inv-map"></div>
    </div>

    <!-- BOTÓN CÓMO LLEGAR -->
    <div class="inv-action">
        <button class="btn inv-btn" id="btn-como-llegar" type="button">
            CÓMO LLEGAR
        </button>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<script src="assets/js/invitacion.js"></script>
<script>
    const evento = {
        lat: <?= $evento['lat'] ?>,
        lng: <?= $evento['lng'] ?>,
        nombre: '<?= $evento['nombre'] ?>',
        direccion: '<?= $evento['direccion'] ?>'
    };
    inicializarMapa(evento);
</script>
</body>
</html>
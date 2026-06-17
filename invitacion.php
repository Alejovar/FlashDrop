<?php
require_once __DIR__ . '/inc/helpers.php';
af_session_start();

// CSP personalizado para permitir Google Maps
header("Content-Security-Policy: default-src 'self'; frame-src 'self' https://www.google.com; style-src 'self' 'unsafe-inline'; script-src 'self'; img-src 'self' data: https:;");
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

    <!-- UBICACIÓN -->
    <div class="inv-location-section">
        <h2 class="inv-section-title">UBICACIÓN</h2>
        <p class="inv-address"><?= $evento['direccion'] ?></p>
    </div>

    <!-- MAPA -->
    <div class="inv-map-section">
        <iframe class="inv-map" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3750.5789063563776!2d-101.00319!3d25.429!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8662bf37cc7bbbbb%3A0x1234567890!2sFrancisco%20M%C3%A1rquez%20119!5e0!3m2!1ses!2smx!4v1234567890" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>

    <!-- BOTÓN CÓMO LLEGAR -->
    <div class="inv-action">
        <button class="btn inv-btn" id="btn-como-llegar" type="button">
            CÓMO LLEGAR
        </button>
    </div>
</div>

<script src="assets/js/invitacion.js"></script>
</body>
</html>

<?php
// pantalla.php — pantalla grande: visual en loop infinito + notificaciones MSN
// de fotos nuevas en cola FIFO (primera que entra, primera que sale).
require_once __DIR__ . '/inc/helpers.php';
af_session_start();
af_security_headers();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="poll-seconds"    content="<?= (int)FEED_POLL_SECONDS ?>">
<meta name="toast-seconds"   content="<?= (int)TOAST_SECONDS ?>">
<meta name="feature-seconds" content="<?= (int)FEATURE_SECONDS ?>">
<title>Pantalla — AlejoFest Vol.21</title>
<link rel="stylesheet" href="assets/css/y2k.css">
<link rel="stylesheet" href="assets/css/pantalla.css">
</head>
<body class="pantalla-body">

<!-- Visual en loop: NUNCA se detiene -->
<video id="visual" class="visual-loop" src="video/loop.mp4" autoplay muted loop playsinline></video>

<!-- Logo fijo -->
<img src="assets/logo.png" alt="AlejoFest Vol.21" class="logo-pantalla">

<!-- Notificación estilo MSN (mini preview, 5 s) -->
<div id="toast" class="msn-toast" hidden>
    <div class="msn-titlebar">
        <span class="titulo">📨 ¡Nueva foto recibida!</span>
    </div>
    <div class="toast-cuerpo">
        <img id="toast-img" src="" alt="Nueva foto">
        <span class="toast-texto">Alguien acaba de subir<br>una foto a la fiesta ✨</span>
    </div>
</div>

<!-- Foto destacada (ventana superior derecha, 8–10 s) -->
<div id="feature" class="feature-window" hidden>
    <div class="msn-titlebar">
        <span class="titulo">AlejoFest Vol.21 — Foto en vivo 🔴</span>
        <span class="controles"><span>_</span><span>□</span><span>✕</span></span>
    </div>
    <img id="feature-img" src="" alt="Foto destacada">
</div>

<!-- Overlay de inicio (necesario para autoplay/fullscreen) -->
<div id="inicio" class="overlay-inicio">
    <img src="assets/logo.png" alt="AlejoFest Vol.21">
    <button class="btn" id="btn-iniciar" type="button">▶ Iniciar pantalla</button>
</div>

<script src="assets/js/pantalla.js"></script>
</body>
</html>

<?php
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
<title>Pantalla &mdash; FLASHDROP</title>
<link rel="stylesheet" href="assets/css/y2k.css">
<link rel="stylesheet" href="assets/css/pantalla.css">
</head>
<body class="pantalla-body">

<!-- VIDEO EN LOOP — NUNCA SE DETIENE. Todo lo demás es overlay. -->
<video id="visual" class="visual-loop" src="video/loop.mp4" autoplay muted loop playsinline></video>

<!-- Logo fijo (z-index 10) -->
<img src="assets/logo.png" alt="FLASHDROP" class="logo-pantalla">

<!-- HUD permanente (esquina superior derecha) -->
<div id="hud" class="hud">
    <div class="hud-sep">━━━━━━━━━━━━━━━━━</div>
    <div class="hud-row"><span class="hud-num" id="hud-total">0</span><span class="hud-label">PHOTOS</span></div>
    <div class="hud-row"><span class="hud-num" id="hud-online">1</span><span class="hud-label">ONLINE</span></div>
    <div class="hud-row hud-live"><span class="hud-dot"></span><span class="hud-label">LIVE</span></div>
    <div class="hud-sep">━━━━━━━━━━━━━━━━━</div>
</div>

<!-- Ventana MSN nueva foto (slide desde esquina inferior derecha) -->
<div id="toast" class="msn-toast" hidden>
    <div class="msn-titlebar">
        <span class="titulo">FLASHDROP — Nueva foto</span>
        <span class="controles"><span>_</span><span>□</span><span>X</span></span>
    </div>
    <div class="toast-cuerpo">
        <img id="toast-img" src="" alt="Nueva foto">
        <span class="toast-texto">Nueva foto en la fiesta</span>
    </div>
</div>

<!-- Overlay de logro (milestone) — cubre toda la pantalla pero el video sigue -->
<div id="logro-overlay" class="logro-overlay" hidden>
    <div class="logro-inner">
        <img id="logro-img" class="logro-foto" src="" alt="Foto del logro">
        <div id="logro-texto" class="logro-texto"></div>
    </div>
    <!-- Canvas para partículas Y2K y scanlines -->
    <canvas id="logro-canvas" class="logro-canvas" hidden></canvas>
</div>

<!-- Overlay de inicio -->
<div id="inicio" class="overlay-inicio">
    <img src="assets/logo.png" alt="FLASHDROP">
    <button class="btn" id="btn-iniciar" type="button">INICIAR PANTALLA</button>
</div>

<script src="assets/js/pantalla.js"></script>
</body>
</html>

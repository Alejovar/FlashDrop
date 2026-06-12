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
<meta name="poll-seconds"  content="<?= (int)FEED_POLL_SECONDS ?>">
<meta name="toast-seconds" content="<?= (int)TOAST_SECONDS ?>">
<title>Pantalla &mdash; FLASHDROP</title>
<link rel="stylesheet" href="assets/css/y2k.css">
<link rel="stylesheet" href="assets/css/pantalla.css">
</head>
<body class="pantalla-body">

<video id="visual" class="visual-loop" src="video/loop.mp4" autoplay muted loop playsinline></video>

<!-- HUD -->
<div id="hud" class="hud">
    <div class="hud-sep">━━━━━━━━━━━━━━━━━</div>
    <div class="hud-row"><span class="hud-num" id="hud-total">0</span><span class="hud-label">PHOTOS</span></div>
    <div class="hud-row"><span class="hud-num" id="hud-online">1</span><span class="hud-label">ONLINE</span></div>
    <div class="hud-row hud-live"><span class="hud-dot"></span><span class="hud-label">LIVE</span></div>
    <div class="hud-sep">━━━━━━━━━━━━━━━━━</div>
</div>

<!-- Ventana MSN nueva foto -->
<div id="msn-window" class="msn-window-notif" hidden>
    <div class="msn-win-titlebar">
        <div class="msn-win-titlebar-left">
            <span class="msn-win-icon">&#9679;</span>
            <span class="msn-win-title">FLASHDROP &mdash; Nueva foto</span>
        </div>
        <div class="msn-win-controls">
            <span class="msn-btn-min">_</span>
            <span class="msn-btn-max">&#9633;</span>
            <span class="msn-btn-close">&#10005;</span>
        </div>
    </div>
    <div class="msn-win-body">
        <img id="msn-foto" src="" alt="Nueva foto">
        <div class="msn-win-footer">
            <span class="msn-win-label">NUEVA FOTO EN LA FIESTA</span>
        </div>
    </div>
</div>

<!-- ============================================================
     OVERLAY DE LOGRO — estilo show/videojuego
     ============================================================ -->
<div id="logro-overlay" class="logro-overlay" hidden>

    <!-- Canvas partículas de fondo -->
    <canvas id="logro-canvas" class="logro-canvas" hidden></canvas>

    <!-- Capa de destellos de estrellas -->
    <div class="logro-estrellas" id="logro-estrellas"></div>

    <!-- Contenido principal -->
    <div class="logro-inner">

        <!-- Etiqueta superior "LOGRO LEGENDARIO" -->
        <div class="logro-badge" id="logro-badge">
            <span class="logro-badge-line"></span>
            <span class="logro-badge-text">LOGRO LEGENDARIO</span>
            <span class="logro-badge-line"></span>
        </div>

        <!-- Número grande: columna izquierda -->
        <div class="logro-titulo" id="logro-titulo"></div>

        <!-- Marco de la foto: columna central -->
        <div class="logro-foto-wrap" id="logro-foto-wrap">
            <div class="logro-marco-brillo"></div>
            <img id="logro-img" class="logro-foto" src="" alt="Foto del logro">
            <div class="logro-marco-esquinas">
                <span class="esq tl"></span>
                <span class="esq tr"></span>
                <span class="esq bl"></span>
                <span class="esq br"></span>
            </div>
        </div>

        <!-- Subtítulo + record: columna derecha -->
        <div class="logro-derecha">
            <div class="logro-sub" id="logro-sub">
                <span class="logro-sub-top">LA FIESTA ESTA EN SU MEJOR MOMENTO</span>
                <span class="logro-sub-bot">ALEJOFEST VOL.21</span>
            </div>
            <div class="logro-record" id="logro-record">
                <span class="logro-record-line"></span>
                <span class="logro-record-text">RECORD ALCANZADO</span>
            </div>
        </div>

    </div>
</div>

<!-- Overlay de inicio -->
<div id="inicio" class="overlay-inicio">
    <img src="assets/logo.png" alt="FLASHDROP">
    <button class="btn" id="btn-iniciar" type="button">INICIAR PANTALLA</button>
</div>

<script src="assets/js/pantalla.js"></script>
</body>
</html>

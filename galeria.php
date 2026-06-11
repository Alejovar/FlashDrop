<?php
// galeria.php — galería pública de fotos (responsiva, con lightbox).
require_once __DIR__ . '/inc/helpers.php';
af_session_start();
af_security_headers();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>Galería — AlejoFest Vol.21</title>
<link rel="stylesheet" href="assets/css/y2k.css">
</head>
<body>
<span class="estrella" style="top:6%; left:6%;">✦</span>
<span class="estrella" style="top:12%; right:10%; animation-delay:.7s;">✧</span>

<div class="contenedor ancha">
    <a class="nav-volver" href="index.php">⬅ Volver al menú</a>
    <img src="assets/logo.png" alt="AlejoFest Vol.21" class="logo-cabecera">
    <p class="subtitulo">★ Galería de la fiesta ★</p>

    <div class="msn-window">
        <div class="msn-titlebar">
            <span class="titulo">Fotos compartidas (<span id="contador">…</span>)</span>
            <span class="controles"><span>_</span><span>□</span><span>✕</span></span>
        </div>
        <div class="msn-cuerpo">
            <div class="galeria" id="galeria"></div>
            <p class="aviso" id="vacio" hidden>Aún no hay fotos… ¡sé la primera persona en subir una! 📸</p>
            <div class="menu-opciones" style="margin-top:14px;">
                <button class="btn secundario" id="btn-mas" type="button" hidden>⬇ Cargar más</button>
                <a class="btn" href="subir.php">📸 Subir mi foto</a>
            </div>
        </div>
    </div>
</div>

<div class="lightbox" id="lightbox" role="dialog" aria-label="Foto ampliada">
    <img id="lightbox-img" src="" alt="Foto ampliada">
</div>

<img src="assets/monito.png" alt="" class="monito-flotante">
<script src="assets/js/galeria.js"></script>
</body>
</html>

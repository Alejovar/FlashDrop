<?php
// index.php — Menú principal
require_once __DIR__ . '/inc/helpers.php';
af_session_start();
af_security_headers();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>AlejoFest Vol.21</title>
<link rel="stylesheet" href="assets/css/y2k.css">
</head>
<body>
<span class="estrella" style="top:8%; left:8%;">✦</span>
<span class="estrella" style="top:16%; right:12%; animation-delay:.8s;">✧</span>
<span class="estrella" style="top:48%; left:5%; animation-delay:1.4s;">✦</span>

<div class="contenedor">
    <img src="assets/logo.png" alt="AlejoFest Vol.21" class="logo-cabecera">
    <p class="subtitulo">★ La fiesta del milenio ★</p>

    <div class="msn-window">
        <div class="msn-titlebar">
            <span class="titulo">AlejoFest Messenger — Conectado</span>
            <span class="controles"><span>_</span><span>□</span><span>✕</span></span>
        </div>
        <div class="msn-cuerpo">
            <div class="menu-opciones">
                <a class="btn" href="subir.php">📸 Tomar / subir mi foto</a>
                <a class="btn secundario" href="galeria.php">🖼️ Ver todas las fotos</a>
            </div>
            <p class="aviso">Tu foto sale en la pantalla grande con la marca oficial del AlejoFest Vol.21 ✨</p>
        </div>
    </div>

    <p class="pie">© 2026 ALEJOFEST VOL.21 · POWERED BY Y2K ENERGY</p>
</div>

<img src="assets/monito.png" alt="" class="monito-flotante">
</body>
</html>

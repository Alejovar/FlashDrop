<?php
require_once __DIR__ . '/inc/helpers.php';
af_session_start();
af_security_headers();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>FLASHDROP</title>
<link rel="stylesheet" href="assets/css/y2k.css">
</head>
<body>
<div class="contenedor">
    <img src="assets/logo.png" alt="FLASHDROP" class="logo-cabecera">
    <p class="subtitulo">ALEJOFEST VOL.21</p>

    <div class="msn-window">
        <div class="msn-titlebar">
            <span class="titulo">AlejoFest Messenger &mdash; Conectado</span>
            <span class="controles"><span>_</span><span>□</span><span>X</span></span>
        </div>
        <div class="msn-cuerpo">
            <div class="menu-opciones">
                <a class="btn" href="subir.php">TOMAR MI FOTO</a>
                <a class="btn secundario" href="galeria.php">VER GALERIA</a>
            </div>
            <p class="aviso" style="margin-top:18px;">Tu foto aparece en la pantalla grande de la fiesta.</p>
        </div>
    </div>

    <p class="pie">2026 ALEJOFEST VOL.21 &middot; POWERED BY Y2K ENERGY</p>
</div>
<img src="assets/monito.png" alt="" class="monito-flotante">
</body>
</html>

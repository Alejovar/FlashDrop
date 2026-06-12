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
<title>Galeria &mdash; FLASHDROP</title>
<link rel="stylesheet" href="assets/css/y2k.css">
</head>
<body>
<div class="contenedor ancha">
    <a class="nav-volver" href="index.php">VOLVER AL MENU</a>
    <img src="assets/logo.png" alt="FLASHDROP" class="logo-cabecera">
    <p class="subtitulo">GALERIA DE LA FIESTA</p>

    <div class="msn-window">
        <div class="msn-titlebar">
            <span class="titulo">Fotos compartidas (<span id="contador">...</span>)</span>
            <span class="controles"><span>_</span><span>□</span><span>X</span></span>
        </div>
        <div class="msn-cuerpo">
            <div class="galeria" id="galeria"></div>
            <p class="aviso" id="vacio" hidden>Aun no hay fotos. Se la primera en subir una.</p>
            <div class="menu-opciones" style="margin-top:14px;">
                <button class="btn secundario" id="btn-mas" type="button" hidden>CARGAR MAS</button>
                <a class="btn" href="subir.php">TOMAR MI FOTO</a>
            </div>
        </div>
    </div>
</div>

<!-- Lightbox — solo muestra la foto, no el link directo al original -->
<div class="lightbox" id="lightbox" role="dialog" aria-label="Foto ampliada">
    <div class="lightbox-acciones">
        <a class="btn mini" id="lb-download-polaroid" href="#" download>DESCARGAR RECUERDO</a>
    </div>
    <img id="lightbox-img" src="" alt="Foto ampliada">
</div>

<script src="assets/js/galeria.js"></script>
</body>
</html>

<?php
// subir.php — tomar o subir foto, ver preview y confirmar (sin opción de borrar).
require_once __DIR__ . '/inc/helpers.php';
af_session_start();
af_security_headers();
$csrf = af_csrf_token();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf" content="<?= e($csrf) ?>">
<title>Subir foto — FLASHDROP</title>
<link rel="stylesheet" href="assets/css/y2k.css">
</head>
<body>
<span class="estrella" style="top:6%; right:8%;">✦</span>
<span class="estrella" style="top:40%; left:4%; animation-delay:1s;">✧</span>

<div class="contenedor">
    <a class="nav-volver" href="index.php">⬅ Volver al menú</a>
    <img src="assets/logo.png" alt="FLASHDROP" class="logo-cabecera">

    <div class="msn-window">
        <div class="msn-titlebar">
            <span class="titulo">Nueva foto — paso <span id="paso">1</span> de 2</span>
            <span class="controles"><span>_</span><span>□</span><span>✕</span></span>
        </div>
        <div class="msn-cuerpo">

            <!-- PASO 1: elegir -->
            <div id="vista-elegir">
                <div class="zona-preview" id="zona">
                    <span style="font-size:42px;">📷</span>
                    <span>Toma una foto frente al espejo<br>o sube una de tu galería</span>
                </div>
                <div class="menu-opciones">
                    <button class="btn" id="btn-camara" type="button">📸 Tomar foto</button>
                    <button class="btn secundario" id="btn-galeria" type="button">🖼️ Subir de mi galería</button>
                </div>
                <input type="file" id="input-camara" accept="image/*" capture="environment" hidden>
                <input type="file" id="input-galeria" accept="image/jpeg,image/png,image/webp" hidden>
            </div>

            <!-- PASO 2: confirmar -->
            <div id="vista-confirmar" hidden>
                <div class="zona-preview" id="zona-confirmar"></div>
                <p class="aviso">⚠️ Al confirmar, tu foto se publica con la marca de agua del AlejoFest y <b>ya no se podrá borrar</b>. ¿Va?</p>
                <div class="menu-opciones">
                    <button class="btn" id="btn-confirmar" type="button">✅ Confirmar y publicar</button>
                    <button class="btn secundario" id="btn-otra" type="button">↩ Elegir otra foto</button>
                </div>
            </div>

            <!-- SUBIENDO -->
            <div id="vista-subiendo" hidden style="text-align:center; padding: 30px 0;">
                <div class="spinner"></div>
                <p class="aviso">Estampando tu foto con la magia Y2K…</p>
            </div>

            <!-- RESULTADO -->
            <div id="vista-resultado" hidden>
                <div class="zona-preview" id="zona-resultado"></div>
                <p class="mensaje ok">✨ ¡Listo! Tu foto ya está en la fiesta y aparecerá en la pantalla grande.</p>
                <div class="menu-opciones">
                    <a class="btn secundario" href="galeria.php">🖼️ Ver la galería</a>
                    <button class="btn" id="btn-otra-mas" type="button">📸 Subir otra</button>
                </div>
            </div>

            <p class="mensaje error" id="msg-error"></p>
        </div>
    </div>
</div>

<img src="assets/monito.png" alt="" class="monito-flotante">
<script src="assets/js/subir.js"></script>
</body>
</html>

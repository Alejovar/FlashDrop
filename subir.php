<?php
// subir.php v2 — SOLO cámara (no galería), preview, confirmar y enviar.
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
<div class="contenedor">
    <a class="nav-volver" href="index.php">VOLVER AL MENU</a>
    <img src="assets/logo.png" alt="FLASHDROP" class="logo-cabecera">

    <div class="msn-window">
        <div class="msn-titlebar">
            <span class="titulo">Nueva foto — paso <span id="paso">1</span> de 2</span>
            <span class="controles"><span>_</span><span>□</span><span>X</span></span>
        </div>
        <div class="msn-cuerpo">

            <!-- PASO 1: Tomar foto -->
            <div id="vista-elegir">
                <div class="zona-preview" id="zona-camara-placeholder">
                    <span style="font-size:52px; opacity:.6;">&#9679;</span>
                    <span class="hud-label">APUNTA Y TOMA TU FOTO</span>
                </div>
                <div class="menu-opciones">
                    <button class="btn" id="btn-camara" type="button">TOMAR FOTO</button>
                </div>
                <!-- capture=environment fuerza cámara trasera en móvil -->
                <input type="file" id="input-camara" accept="image/*" capture="environment" hidden>
            </div>

            <!-- PASO 2: Preview y confirmar -->
            <div id="vista-confirmar" hidden>
                <div class="zona-preview" id="zona-confirmar"></div>
                <p class="aviso">Al confirmar, tu foto se publica en la fiesta y <strong>ya no se podrá modificar ni eliminar</strong>.</p>
                <div class="menu-opciones">
                    <button class="btn" id="btn-confirmar" type="button">CONFIRMAR ENVIO</button>
                    <button class="btn secundario" id="btn-otra" type="button">TOMAR OTRA FOTO</button>
                </div>
            </div>

            <!-- SUBIENDO -->
            <div id="vista-subiendo" hidden style="text-align:center; padding:40px 0;">
                <div class="spinner"></div>
                <p class="aviso" style="margin-top:14px;">PROCESANDO TU FOTO...</p>
            </div>

            <!-- RESULTADO -->
            <div id="vista-resultado" hidden>
                <div class="zona-preview" id="zona-resultado"></div>
                <p class="mensaje ok" id="msg-ok">TU FOTO YA ESTA EN LA FIESTA.</p>
                <div class="menu-opciones">
                    <a class="btn secundario" href="galeria.php">VER GALERIA</a>
                    <button class="btn" id="btn-otra-mas" type="button">SUBIR OTRA FOTO</button>
                </div>
            </div>

            <p class="mensaje error" id="msg-error"></p>
        </div>
    </div>
</div>

<script src="assets/js/subir.js"></script>
</body>
</html>

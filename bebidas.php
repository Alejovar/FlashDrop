<?php
require_once __DIR__ . '/inc/helpers.php';
af_security_headers();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
<title>Bebidas — FLASHDROP</title>
<link rel="stylesheet" href="assets/css/y2k.css">
<link rel="stylesheet" href="assets/css/bebidas.css">
</head>
<body class="bebidas-body">

<div class="bebidas-header">
    <h1>TOMA TU BOLSITA</h1>
    <p class="bebidas-sub">Toca tu nombre cada vez que tomes una</p>
</div>

<div id="bebidas-grid" class="bebidas-grid">
    <p class="bebidas-cargando">Cargando invitados...</p>
</div>

<!-- Confirmación visual al presionar -->
<div id="bebidas-toast" class="bebidas-toast" hidden></div>

<script src="assets/js/bebidas.js"></script>
</body>
</html>

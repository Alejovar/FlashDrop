<?php
require_once __DIR__ . '/inc/helpers.php';
af_security_headers();

// Datos de la invitación
$evento = [
    'nombre'    => 'AlejoFest Vol.21',
    'fecha'     => 'Domingo 2 de Agosto',
    'hora'      => '8:00 PM',
    'dress'     => 'Y2K Mode ON',
    'direccion' => 'Francisco Márquez 119, Saltillo, Coahuila 25084',
    'lat'       => 25.4290,
    'lng'       => -101.0032,
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $evento['nombre'] ?> — Invitación Virtual</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
<link rel="stylesheet" href="assets/css/y2k.css">
<link rel="stylesheet" href="assets/css/invitacion.css">
</head>
<body class="invitacion-body">

<!-- VIDEO EN LOOP — portada -->
<div class="inv-video-container">
    <video class="inv-video" src="video/loop.mp4" autoplay muted loop playsinline></video>
    <div class="inv-video-overlay"></div>
</div>

<!-- INVITACIÓN FIJA -->
<div class="inv-card">
    <div class="inv-header">
        <h1 class="inv-titulo"><?= $evento['nombre'] ?></h1>
    </div>

    <div class="inv-details">
        <div class="inv-detail">
            <span class="inv-icon">📅</span>
            <div>
                <span class="inv-label">FECHA</span>
                <span class="inv-value"><?= $evento['fecha'] ?></span>
            </div>
        </div>

        <div class="inv-detail">
            <span class="inv-icon">🕐</span>
            <div>
                <span class="inv-label">HORA</span>
                <span class="inv-value"><?= $evento['hora'] ?></span>
            </div>
        </div>

        <div class="inv-detail">
            <span class="inv-icon">👗</span>
            <div>
                <span class="inv-label">DRESS CODE</span>
                <span class="inv-value"><?= $evento['dress'] ?></span>
            </div>
        </div>

        <div class="inv-detail">
            <span class="inv-icon">📍</span>
            <div>
                <span class="inv-label">UBICACIÓN</span>
                <span class="inv-value"><?= $evento['direccion'] ?></span>
            </div>
        </div>
    </div>

    <!-- MAPA -->
    <div class="inv-map-section">
        <h2 class="inv-section-title">UBICACIÓN</h2>
        <div id="inv-map" class="inv-map"></div>
    </div>

    <!-- BOTÓN "CÓMO LLEGAR" -->
    <div class="inv-action">
        <button class="btn inv-btn" id="btn-como-llegar" type="button">
            ¿CÓMO LLEGAR?
        </button>

        <!-- Modal para seleccionar app de maps -->
        <div id="maps-modal" class="maps-modal" hidden>
            <div class="maps-modal-content">
                <h3 class="maps-modal-title">ELIGE CÓMO LLEGAR</h3>
                <div class="maps-modal-buttons">
                    <a href="https://www.google.com/maps/search/Francisco+Márquez+119,+Saltillo,+Coahuila" target="_blank" class="btn maps-btn google-maps">
                        🗺️ Google Maps
                    </a>
                    <a href="https://maps.apple.com/?q=Francisco+Márquez+119,+Saltillo,+Coahuila" target="_blank" class="btn maps-btn apple-maps">
                        🗺️ Apple Maps
                    </a>
                </div>
                <button class="btn maps-btn cerrar" id="btn-cerrar-modal" type="button">CERRAR</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<script src="assets/js/invitacion.js"></script>
<script>
    // Inicializar mapa con coordenadas
    const evento = {
        lat: <?= $evento['lat'] ?>,
        lng: <?= $evento['lng'] ?>,
        nombre: '<?= $evento['nombre'] ?>',
        direccion: '<?= $evento['direccion'] ?>'
    };
    inicializarMapa(evento);
</script>
</body>
</html>

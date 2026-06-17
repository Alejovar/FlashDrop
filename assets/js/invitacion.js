// invitacion.js
(function () {
    'use strict';

    // Botón "Cómo llegar" — abre directo las opciones de maps
    const btnComoLlegar = document.getElementById('btn-como-llegar');

    if (btnComoLlegar) {
        btnComoLlegar.addEventListener('click', () => {
            const direccion = 'Francisco Márquez 119, Saltillo, Coahuila 25084';
            const lat = 25.4290;
            const lng = -101.0032;

            // Detectar dispositivo
            const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) ||
                         (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
            const isAndroid = /Android/.test(navigator.userAgent);

            if (isIOS) {
                // iOS — intenta Apple Maps primero, fallback a Google Maps
                const appleMapsURL = `https://maps.apple.com/?q=${encodeURIComponent(direccion)}`;
                const googleMapsURL = `https://maps.google.com/?q=${lat},${lng}`;
                
                // Intentar abrir Apple Maps
                window.location.href = appleMapsURL;
                
                // Si no abre Apple Maps en 2 segundos, intenta Google Maps
                setTimeout(() => {
                    window.location.href = googleMapsURL;
                }, 2000);
            } else {
                // Android y desktop — Google Maps directo
                const googleMapsURL = `https://maps.google.com/?q=${encodeURIComponent(direccion)}`;
                window.open(googleMapsURL, '_blank');
            }
        });
    }

    // Inicializar mapa con Leaflet
    window.inicializarMapa = function(evento) {
        const mapEl = document.getElementById('inv-map');
        if (!mapEl) return;

        const map = L.map('inv-map').setView([evento.lat, evento.lng], 15);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap',
            maxZoom: 18,
        }).addTo(map);

        const marker = L.marker([evento.lat, evento.lng]).addTo(map);
        marker.bindPopup(`<strong>${evento.nombre}</strong><br>${evento.direccion}`);
        marker.openPopup();

        return map;
    };
})();

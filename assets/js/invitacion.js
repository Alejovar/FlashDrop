// invitacion.js
(function () {
    'use strict';

    const btnComoLlegar = document.getElementById('btn-como-llegar');

    if (btnComoLlegar) {
        btnComoLlegar.addEventListener('click', () => {
            const direccion = 'Francisco Márquez 119, Saltillo, Coahuila 25084';

            const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) ||
                         (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);

            if (isIOS) {
                const appleMapsURL = `https://maps.apple.com/?q=${encodeURIComponent(direccion)}`;
                window.location.href = appleMapsURL;
                
                setTimeout(() => {
                    const googleMapsURL = `https://maps.google.com/?q=${encodeURIComponent(direccion)}`;
                    window.location.href = googleMapsURL;
                }, 2000);
            } else {
                const googleMapsURL = `https://maps.google.com/?q=${encodeURIComponent(direccion)}`;
                window.open(googleMapsURL, '_blank');
            }
        });
    }

    // Inicializar mapa con Leaflet
    window.inicializarMapa = function(evento) {
        const mapEl = document.getElementById('inv-map');
        if (!mapEl || typeof L === 'undefined') return;

        const map = L.map('inv-map').setView([evento.lat, evento.lng], 16);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap',
            maxZoom: 18,
        }).addTo(map);

        // Crear icono personalizado con el logo
        const logoIcon = L.icon({
            iconUrl: 'assets/logo.png',
            iconSize: [50, 50],
            iconAnchor: [25, 50],
            popupAnchor: [0, -50],
        });

        const marker = L.marker([evento.lat, evento.lng], { icon: logoIcon }).addTo(map);
        marker.bindPopup(`<strong>${evento.nombre}</strong>`);
        marker.openPopup();

        return map;
    };
})();
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

        const map = L.map('inv-map', {
            zoomControl: true,
        }).setView([evento.lat, evento.lng], 16);

        // CartoDB Dark Voyager — tileset oscuro
        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            attribution: '© OpenStreetMap © CartoDB',
            maxZoom: 19,
            subdomains: 'abcd',
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

        // Aplicar filtro Y2K al mapa
        const mapContainer = document.getElementById('inv-map');
        mapContainer.style.filter = 'saturate(1.3) hue-rotate(200deg) contrast(1.1)';

        return map;
    };
})();
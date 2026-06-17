// invitacion.js
(function () {
    'use strict';

    // --- Modal de selección de maps ---
    const btnComoLlegar = document.getElementById('btn-como-llegar');
    const mapsModal = document.getElementById('maps-modal');
    const btnCerrarModal = document.getElementById('btn-cerrar-modal');

    if (btnComoLlegar) {
        btnComoLlegar.addEventListener('click', () => {
            mapsModal.hidden = false;
        });
    }

    if (btnCerrarModal) {
        btnCerrarModal.addEventListener('click', () => {
            mapsModal.hidden = true;
        });
    }

    // Cerrar modal al hacer clic afuera
    if (mapsModal) {
        mapsModal.addEventListener('click', (e) => {
            if (e.target === mapsModal) {
                mapsModal.hidden = true;
            }
        });
    }

    // --- Inicializar mapa con Leaflet ---
    window.inicializarMapa = function(evento) {
        const mapEl = document.getElementById('inv-map');
        if (!mapEl) return;

        // Crear mapa centrado en la ubicación
        const map = L.map('inv-map').setView([evento.lat, evento.lng], 15);

        // Layer de OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 18,
        }).addTo(map);

        // Crear un marcador personalizado con el logo del evento
        // Si tienes un logo, lo puedes usar; por ahora usamos un marcador estándar
        const marker = L.marker([evento.lat, evento.lng], {
            title: evento.nombre,
        }).addTo(map);

        // Popup con la información
        marker.bindPopup(`
            <div style="text-align:center; font-family: Tahoma; color: #333;">
                <strong style="font-size:14px; color:#1f56ff;">${evento.nombre}</strong><br>
                <span style="font-size:12px; color:#666;">${evento.direccion}</span>
            </div>
        `);

        // Abrir el popup por defecto
        marker.openPopup();

        return map;
    };
})();

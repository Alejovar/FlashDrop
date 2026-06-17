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

    // --- RSVP: confirmar asistencia ---
    const form     = document.getElementById('rsvp-form');
    const input    = document.getElementById('rsvp-nombre');
    const msg      = document.getElementById('rsvp-msg');
    const formWrap = document.getElementById('rsvp-form-wrap');
    const contador = document.getElementById('rsvp-contador');

    async function cargarContador() {
        if (!contador) return;
        try {
            const res  = await fetch('api/invitados_contador.php', { cache: 'no-store' });
            const data = await res.json();
            if (data.ok) {
                const texto = data.total === 1
                    ? '1 persona ya confirmó su asistencia'
                    : data.total + ' personas ya confirmaron su asistencia';
                contador.textContent = texto;
            }
        } catch (e) {}
    }

    cargarContador();

    if (form) {
        const yaConfirmado = localStorage.getItem('af_rsvp_confirmado');
        if (yaConfirmado) {
            formWrap.innerHTML = '<p class="rsvp-msg ok">Ya confirmaste tu asistencia como <strong>' + yaConfirmado + '</strong></p>';
        } else {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const nombre = input.value.trim();
                if (!nombre) return;

                const btn = form.querySelector('button[type="submit"]');
                btn.disabled = true;
                btn.textContent = 'ENVIANDO...';

                try {
                    const fd = new FormData();
                    fd.append('nombre', nombre);
                    const res  = await fetch('api/rsvp.php', { method: 'POST', body: fd });
                    const data = await res.json();

                    if (data.ok) {
                        localStorage.setItem('af_rsvp_confirmado', nombre);
                        formWrap.innerHTML = '<p class="rsvp-msg ok">Asistencia confirmada, ' + nombre + '. Te esperamos.</p>';
                        cargarContador();
                    } else {
                        msg.textContent = data.error || 'No se pudo confirmar. Intenta de nuevo.';
                        msg.className = 'rsvp-msg error';
                        btn.disabled = false;
                        btn.textContent = 'CONFIRMAR';
                    }
                } catch (err) {
                    msg.textContent = 'Sin conexión con el servidor.';
                    msg.className = 'rsvp-msg error';
                    btn.disabled = false;
                    btn.textContent = 'CONFIRMAR';
                }
            });
        }
    }
})();
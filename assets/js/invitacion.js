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
})();
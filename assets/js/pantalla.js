// pantalla.js — el corazón de la pantalla grande.
// Ciclo por foto (FIFO estricto, una a la vez):
//   1) Toast MSN con mini preview ......... TOAST_SECONDS (5 s)
//   2) Ventana destacada arriba derecha ... FEATURE_SECONDS (8–10 s)
//   3) Desaparece suavemente y pasa a la siguiente de la cola.
// El video en loop NUNCA se detiene.
(function () {
    'use strict';

    const POLL_MS    = (parseInt(document.querySelector('meta[name="poll-seconds"]').content, 10)    || 3) * 1000;
    const TOAST_MS   = (parseInt(document.querySelector('meta[name="toast-seconds"]').content, 10)   || 5) * 1000;
    const FEATURE_MS = (parseInt(document.querySelector('meta[name="feature-seconds"]').content, 10) || 9) * 1000;
    const FADE_MS    = 650;

    const toast      = document.getElementById('toast');
    const toastImg   = document.getElementById('toast-img');
    const feature    = document.getElementById('feature');
    const featureImg = document.getElementById('feature-img');
    const video      = document.getElementById('visual');
    const inicio     = document.getElementById('inicio');

    let cursor = 0;          // último id de cola procesado
    let cola = [];           // FIFO
    let mostrando = false;

    const espera = ms => new Promise(r => setTimeout(r, ms));

    function precargar(url) {
        return new Promise(resolve => {
            const img = new Image();
            img.onload = () => resolve(true);
            img.onerror = () => resolve(false);
            img.src = url;
        });
    }

    // ---------- Secuencia de una foto ----------
    async function mostrarFoto(item) {
        const ok = await precargar(item.url);
        if (!ok) return;

        // 1) Toast MSN
        toastImg.src = item.url;
        toast.hidden = false;
        // forzar reflow para que la transición corra
        void toast.offsetWidth;
        toast.classList.add('visible');
        await espera(TOAST_MS);
        toast.classList.remove('visible');
        await espera(500);
        toast.hidden = true;

        // 2) Ventana destacada (tamaño según orientación)
        feature.classList.remove('vertical', 'horizontal', 'cuadrada', 'saliendo');
        feature.classList.add(item.orientation);
        featureImg.src = item.url;
        feature.hidden = false;
        void feature.offsetWidth;
        feature.classList.add('visible');
        await espera(FEATURE_MS);

        // 3) Salida suave
        feature.classList.add('saliendo');
        feature.classList.remove('visible');
        await espera(FADE_MS);
        feature.hidden = true;
        featureImg.src = '';
    }

    async function procesarCola() {
        if (mostrando) return;
        mostrando = true;
        while (cola.length > 0) {
            const item = cola.shift();   // FIFO: primera que entra, primera que sale
            try { await mostrarFoto(item); } catch (e) { /* seguir con la siguiente */ }
            await espera(400);           // respiro entre fotos
        }
        mostrando = false;
    }

    // ---------- Polling del feed ----------
    async function consultarFeed() {
        try {
            const res = await fetch('api/feed.php?after=' + cursor, { cache: 'no-store' });
            const data = await res.json();
            if (data.ok) {
                cursor = data.cursor;
                if (data.items && data.items.length) {
                    cola.push(...data.items);
                    procesarCola();
                }
            }
        } catch (e) { /* sin red momentánea: reintenta en el siguiente ciclo */ }
    }

    // ---------- Vigilar que el video nunca se detenga ----------
    setInterval(() => {
        if (video.paused) video.play().catch(() => {});
    }, 4000);

    // ---------- Inicio (gesto del usuario para fullscreen + autoplay) ----------
    document.getElementById('btn-iniciar').addEventListener('click', async () => {
        inicio.classList.add('oculto');
        try { await document.documentElement.requestFullscreen(); } catch (e) {}
        video.play().catch(() => {});
        await consultarFeed();                    // fija el cursor (no repite historial)
        setInterval(consultarFeed, POLL_MS);
    });
})();

// galeria.js v2
(function () {
    'use strict';

    const POLL_MS  = 5000;

    const galeria  = document.getElementById('galeria');
    const btnMas   = document.getElementById('btn-mas');
    const vacio    = document.getElementById('vacio');
    const contador = document.getElementById('contador');
    const lightbox = document.getElementById('lightbox');
    const lbImg    = document.getElementById('lightbox-img');
    const lbDl     = document.getElementById('lb-download-polaroid');

    let menorId  = 0;
    let mayorId  = 0;
    let total    = 0;
    const loaded = new Set();
    let fotoActual = null;  // { id, url, orientation }

    function agregarFoto(p, alFinal) {
        if (loaded.has(p.id)) return;
        loaded.add(p.id);
        total++;

        const wrap = document.createElement('div');
        wrap.className  = 'galeria-item';
        wrap.dataset.id = p.id;

        const img = new Image();
        img.src       = p.url;
        img.alt       = 'Foto de la fiesta';
        img.className = 'foto';
        img.loading   = 'lazy';
        img.addEventListener('click', () => abrirLightbox(p));

        wrap.appendChild(img);
        if (alFinal) galeria.appendChild(wrap);
        else         galeria.prepend(wrap);

        if (menorId === 0 || p.id < menorId) menorId = p.id;
        if (p.id > mayorId) mayorId = p.id;
        contador.textContent = total;
        vacio.hidden = true;
    }

    function abrirLightbox(p) {
        fotoActual = p;
        lbImg.src  = p.url;
        lightbox.classList.add('abierto');
    }

    // ---------- Descarga — compatible con Safari/iPhone ----------
    // iOS no permite descargas de blobs. Solución: mostrar la Polaroid
    // directamente en el lightbox para que el usuario la mantenga presionada.
    const lbHint = document.getElementById('lb-hint');

    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) ||
                  (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);

    // En iOS cambiar el texto del botón desde el inicio
    if (isIOS) {
        lbDl.textContent = 'VER POLAROID';
    }

    lbDl.addEventListener('click', async function (e) {
        e.preventDefault();
        if (!fotoActual) return;

        const textoOriginal = lbDl.textContent;
        lbDl.textContent = 'GENERANDO...';
        lbDl.style.opacity = '0.6';
        lbDl.style.pointerEvents = 'none';
        if (lbHint) lbHint.hidden = true;

        try {
            const res = await fetch('api/polaroid.php?id=' + fotoActual.id);
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const blob = await res.blob();
            const url  = URL.createObjectURL(blob);

            if (isIOS) {
                // Mostrar la Polaroid dentro del lightbox
                // El usuario la mantiene presionada → "Guardar en Fotos"
                lbImg.src = url;
                lbDl.textContent = 'MANTÉN PRESIONADA PARA GUARDAR';
                lbDl.style.opacity = '';
                lbDl.style.pointerEvents = '';
                if (lbHint) {
                    lbHint.hidden = false;
                    lbHint.textContent = 'Mantén presionada la imagen de arriba y elige "Guardar en Fotos"';
                }
                setTimeout(() => URL.revokeObjectURL(url), 30000);
                return; // no restaurar botón — el hint guía al usuario
            } else {
                // Chrome / Android / desktop — descarga directa
                const a = document.createElement('a');
                a.href = url;
                a.download = 'AlejoFest_Vol21_recuerdo.jpg';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                setTimeout(() => URL.revokeObjectURL(url), 10000);
            }
        } catch (err) {
            if (lbHint) {
                lbHint.hidden = false;
                lbHint.textContent = 'No se pudo generar. Mantén presionada la foto y elige "Guardar en Fotos".';
            }
        } finally {
            if (!isIOS) {
                lbDl.textContent = textoOriginal;
                lbDl.style.opacity = '';
                lbDl.style.pointerEvents = '';
            }
        }
    });

    // Cerrar lightbox
    lightbox.addEventListener('click', e => {
        if (e.target === lightbox || e.target === lbImg) {
            lightbox.classList.remove('abierto');
            lbImg.src  = '';
            fotoActual = null;
        }
    });
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            lightbox.classList.remove('abierto');
            fotoActual = null;
        }
    });

    // ---------- Carga paginada ----------
    async function cargar(before) {
        const url  = 'api/photos.php' + (before ? ('?before=' + before) : '');
        const res  = await fetch(url);
        const data = await res.json();
        if (!data.ok) return;
        data.photos.forEach(p => agregarFoto(p, true));
        btnMas.hidden = !data.hasMore;
        if (!data.hasMore && total === 0) vacio.hidden = false;
    }

    // ---------- Polling en tiempo real ----------
    async function pollNuevas() {
        if (mayorId === 0) return;
        try {
            const res  = await fetch('api/photos.php?after=' + mayorId);
            const data = await res.json();
            if (!data.ok || !data.photos) return;
            [...data.photos].reverse().forEach(p => agregarFoto(p, false));
        } catch (e) {}
    }

    btnMas.addEventListener('click', () => cargar(menorId));

    cargar(0).then(() => {
        setInterval(pollNuevas, POLL_MS);
    });
})();

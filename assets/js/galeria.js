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

    // ---------- Descarga compatible con Safari / iPhone ----------
    // Safari ignora el atributo download en links. La única forma fiable
    // es hacer fetch del binario, crear un blob URL y abrirlo.
    lbDl.addEventListener('click', async function (e) {
        e.preventDefault();
        if (!fotoActual) return;

        const btn = lbDl;
        const textoOriginal = btn.textContent;
        btn.textContent  = 'DESCARGANDO...';
        btn.style.opacity = '0.6';
        btn.style.pointerEvents = 'none';

        try {
            const res = await fetch('api/polaroid.php?id=' + fotoActual.id);
            if (!res.ok) throw new Error('Error HTTP ' + res.status);
            const blob = await res.blob();
            const url  = URL.createObjectURL(blob);

            // En iOS/Safari la única forma de descargar es abrir en nueva pestaña
            // (el usuario hace "Guardar imagen" desde ahí).
            // En Chrome/Firefox el link con download funciona directo.
            const isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent)
                          || /iPad|iPhone|iPod/.test(navigator.userAgent);

            if (isSafari) {
                // Abrir el blob en nueva pestaña — iOS permite "Guardar en fotos" desde ahí
                window.open(url, '_blank');
            } else {
                const a = document.createElement('a');
                a.href     = url;
                a.download = 'AlejoFest_Vol21_recuerdo.jpg';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            }

            setTimeout(() => URL.revokeObjectURL(url), 10000);
        } catch (err) {
            alert('No se pudo descargar la foto. Intenta mantener presionada la imagen y "Guardar".');
        } finally {
            btn.textContent  = textoOriginal;
            btn.style.opacity = '';
            btn.style.pointerEvents = '';
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

// galeria.js v2 — carga paginada + polling en tiempo real + lightbox con Polaroid.
(function () {
    'use strict';

    const POLL_MS  = 5000;  // actualizar galería cada 5 s

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
    const loaded = new Set();  // IDs ya mostradas

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
        else galeria.prepend(wrap);

        if (menorId === 0 || p.id < menorId) menorId = p.id;
        if (p.id > mayorId) mayorId = p.id;
        contador.textContent = total;
        vacio.hidden = true;
    }

    function abrirLightbox(p) {
        lbImg.src  = p.url;
        // Descarga como Polaroid, sin exponer la URL del original
        lbDl.href  = 'api/polaroid.php?id=' + p.id;
        lbDl.setAttribute('download', 'AlejoFest_Vol21_' + p.id + '_recuerdo.jpg');
        lightbox.classList.add('abierto');
    }

    // Carga inicial (paginada hacia atrás)
    async function cargar(before) {
        const url  = 'api/photos.php' + (before ? ('?before=' + before) : '');
        const res  = await fetch(url);
        const data = await res.json();
        if (!data.ok) return;
        data.photos.forEach(p => agregarFoto(p, true));
        btnMas.hidden = !data.hasMore;
        if (!data.hasMore && total === 0) vacio.hidden = false;
    }

    // Polling — solo fotos nuevas (mayor ID que el máximo conocido)
    async function pollNuevas() {
        if (mayorId === 0) return;
        const res  = await fetch('api/photos.php?after=' + mayorId);
        const data = await res.json();
        if (!data.ok || !data.photos) return;
        // Las fotos nuevas van arriba (prepend)
        [...data.photos].reverse().forEach(p => agregarFoto(p, false));
    }

    btnMas.addEventListener('click', () => cargar(menorId));

    lightbox.addEventListener('click', e => {
        if (e.target === lightbox || e.target === lbImg) {
            lightbox.classList.remove('abierto');
            lbImg.src = '';
        }
    });
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') lightbox.classList.remove('abierto');
    });

    cargar(0).then(() => {
        setInterval(pollNuevas, POLL_MS);
    });
})();

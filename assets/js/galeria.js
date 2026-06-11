// galeria.js — carga paginada de fotos + lightbox.
(function () {
    'use strict';

    const galeria  = document.getElementById('galeria');
    const btnMas   = document.getElementById('btn-mas');
    const vacio    = document.getElementById('vacio');
    const contador = document.getElementById('contador');
    const lightbox = document.getElementById('lightbox');
    const lbImg    = document.getElementById('lightbox-img');

    let menorId = 0;       // id más pequeño cargado (para paginar hacia atrás)
    let total   = 0;

    function agregarFoto(p, alFinal) {
        const img = new Image();
        img.src = p.url;
        img.alt = 'Foto de la fiesta';
        img.className = 'foto';
        img.loading = 'lazy';
        img.addEventListener('click', () => {
            lbImg.src = p.url;
            lightbox.classList.add('abierto');
        });
        if (alFinal) galeria.appendChild(img);
        else galeria.prepend(img);
    }

    async function cargar(before) {
        const url = 'api/photos.php' + (before ? ('?before=' + before) : '');
        const res = await fetch(url);
        const data = await res.json();
        if (!data.ok) return;

        data.photos.forEach(p => {
            agregarFoto(p, true);
            if (menorId === 0 || p.id < menorId) menorId = p.id;
            total++;
        });
        contador.textContent = total;
        vacio.hidden = total > 0;
        btnMas.hidden = !data.hasMore;
    }

    btnMas.addEventListener('click', () => cargar(menorId));

    lightbox.addEventListener('click', () => {
        lightbox.classList.remove('abierto');
        lbImg.src = '';
    });
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') lightbox.classList.remove('abierto');
    });

    cargar(0);
})();

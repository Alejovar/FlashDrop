// pantalla.js v2 — HUD permanente + ventana MSN de nuevas fotos + animación de logros.
// El video NUNCA se detiene. Todo es overlay encima del video.
(function () {
    'use strict';

    const POLL_MS    = (parseInt(document.querySelector('meta[name="poll-seconds"]').content, 10) || 3) * 1000;
    const TOAST_MS   = (parseInt(document.querySelector('meta[name="toast-seconds"]').content, 10) || 10) * 1000;
    const MILESTONE_MS = 12000;  // duración total de animación de logro
    const FADE_MS    = 700;

    const video    = document.getElementById('visual');
    const inicio   = document.getElementById('inicio');

    // HUD
    const hudTotal  = document.getElementById('hud-total');
    const hudOnline = document.getElementById('hud-online');

    // Toast MSN nueva foto
    const toast     = document.getElementById('toast');
    const toastImg  = document.getElementById('toast-img');

    // Logro
    const logro        = document.getElementById('logro-overlay');
    const logroImg     = document.getElementById('logro-img');
    const logroTexto   = document.getElementById('logro-texto');
    const logroCanvas  = document.getElementById('logro-canvas');

    let cursor     = 0;
    let colaMSN    = [];  // fotos para toast MSN
    let mostrando  = false;
    let totalFotos = 0;
    let milestoneQueue = [];  // logros pendientes

    const espera = ms => new Promise(r => setTimeout(r, ms));

    function precargar(url) {
        return new Promise(resolve => {
            const img = new Image();
            img.onload  = () => resolve(true);
            img.onerror = () => resolve(false);
            img.src = url;
        });
    }

    // ---------- HUD ----------
    function actualizarHUD(total, online) {
        if (total !== undefined) { totalFotos = total; hudTotal.textContent = total; }
        if (online !== undefined) hudOnline.textContent = online;
    }

    async function pollStats() {
        try {
            const res  = await fetch('api/stats.php', { cache: 'no-store' });
            const data = await res.json();
            if (data.ok) actualizarHUD(data.total, data.online);
        } catch (e) {}
    }

    // ---------- Toast MSN — ventana nueva foto ----------
    async function mostrarToast(item) {
        const ok = await precargar(item.url);
        if (!ok) return;

        // Orientación de la ventana
        toast.classList.remove('vertical', 'horizontal', 'cuadrada');
        toast.classList.add(item.orientation || 'vertical');

        toastImg.src = item.url;
        toast.hidden = false;
        void toast.offsetWidth;
        toast.classList.add('visible');
        await espera(TOAST_MS);
        toast.classList.remove('visible');
        await espera(FADE_MS);
        toast.hidden = true;
        toastImg.src = '';
    }

    async function procesarColaMSN() {
        if (mostrando) return;
        mostrando = true;
        while (colaMSN.length > 0) {
            const item = colaMSN.shift();
            try { await mostrarToast(item); } catch (e) {}
            await espera(500);
        }
        mostrando = false;
    }

    // ---------- Animación de Logro ----------
    function iniciarParticulas(canvas) {
        canvas.hidden = false;
        const ctx = canvas.getContext('2d');
        const W   = canvas.width  = window.innerWidth;
        const H   = canvas.height = window.innerHeight;

        const colores = ['#1f56ff', '#6fb3ff', '#ff2aff', '#ffe600', '#00ffe5', '#ffffff'];
        const particulas = Array.from({ length: 120 }, () => ({
            x: Math.random() * W,
            y: Math.random() * H * 0.6,
            vx: (Math.random() - 0.5) * 3,
            vy: Math.random() * 2 + 1,
            size: Math.random() * 5 + 2,
            color: colores[Math.floor(Math.random() * colores.length)],
            alpha: 1,
        }));

        let raf;
        function frame() {
            ctx.clearRect(0, 0, W, H);

            // Scanlines Y2K
            ctx.fillStyle = 'rgba(0,0,0,0.04)';
            for (let y = 0; y < H; y += 4) {
                ctx.fillRect(0, y, W, 2);
            }

            particulas.forEach(p => {
                p.x  += p.vx;
                p.y  += p.vy;
                p.vy += 0.05;
                p.alpha = Math.max(0, p.alpha - 0.008);

                ctx.globalAlpha = p.alpha;
                ctx.fillStyle   = p.color;

                // Pixel art square particles
                const s = Math.round(p.size);
                ctx.fillRect(Math.round(p.x), Math.round(p.y), s, s);

                // Neon glow
                ctx.shadowBlur  = 10;
                ctx.shadowColor = p.color;
                ctx.fillRect(Math.round(p.x), Math.round(p.y), s, s);
                ctx.shadowBlur  = 0;
            });

            ctx.globalAlpha = 1;
            raf = requestAnimationFrame(frame);
        }
        raf = requestAnimationFrame(frame);
        return () => { cancelAnimationFrame(raf); canvas.hidden = true; ctx.clearRect(0, 0, W, H); };
    }

    async function mostrarLogro(milestone) {
        // Paso 1: overlay oscuro (sin parar video)
        logro.classList.remove('visible', 'saliendo');
        logroImg.src   = '';
        logroTexto.textContent = '';

        const ok = await precargar(milestone.url);
        if (!ok) return;

        logroImg.src = milestone.url;
        logroImg.className = 'logro-foto ' + (milestone.orientation || 'vertical');

        // Paso 2: mostrar overlay
        logro.hidden = false;
        void logro.offsetWidth;
        logro.classList.add('visible');

        // Paso 3: texto
        await espera(600);
        logroTexto.textContent = milestone.quantity + ' MEMORIES';
        logroTexto.classList.add('visible');

        // Paso 4: partículas
        await espera(400);
        const detenerParticulas = iniciarParticulas(logroCanvas);

        // Paso 5: duración
        await espera(MILESTONE_MS - 1000);

        // Paso 6: fade out
        detenerParticulas();
        logroTexto.classList.remove('visible');
        logro.classList.add('saliendo');
        logro.classList.remove('visible');
        await espera(FADE_MS);
        logro.hidden   = true;
        logroImg.src   = '';
    }

    async function procesarMilestones() {
        while (milestoneQueue.length > 0) {
            const m = milestoneQueue.shift();
            try { await mostrarLogro(m); } catch (e) {}
            await espera(800);
        }
    }

    // ---------- Polling del feed ----------
    async function consultarFeed() {
        try {
            const res  = await fetch('api/feed.php?after=' + cursor, { cache: 'no-store' });
            const data = await res.json();
            if (!data.ok) return;

            cursor = data.cursor;

            if (data.total !== undefined) actualizarHUD(data.total);

            if (data.items && data.items.length) {
                colaMSN.push(...data.items);
                procesarColaMSN();
            }

            if (data.milestone) {
                milestoneQueue.push(data.milestone);
                if (milestoneQueue.length === 1) procesarMilestones();
            }
        } catch (e) {}
    }

    // Vigilar que el video NUNCA se detenga
    setInterval(() => {
        if (video.paused || video.ended) video.play().catch(() => {});
    }, 3000);

    // ---------- Inicio ----------
    document.getElementById('btn-iniciar').addEventListener('click', async () => {
        inicio.classList.add('oculto');
        try { await document.documentElement.requestFullscreen(); } catch (e) {}
        video.play().catch(() => {});
        await consultarFeed();   // establece el cursor sin repetir historial
        await pollStats();
        setInterval(consultarFeed, POLL_MS);
        setInterval(pollStats, 15000);
    });
})();

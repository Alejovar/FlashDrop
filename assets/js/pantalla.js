// pantalla.js v2
(function () {
    'use strict';

    function metaInt(name, fallback) {
        const el = document.querySelector('meta[name="' + name + '"]');
        return el ? (parseInt(el.content, 10) || fallback) : fallback;
    }

    const POLL_MS      = metaInt('poll-seconds',  3)  * 1000;
    const TOAST_MS     = metaInt('toast-seconds', 10) * 1000;
    const MILESTONE_MS = 12000;
    const FADE_MS      = 700;

    const video      = document.getElementById('visual');
    const inicio     = document.getElementById('inicio');
    const hudTotal   = document.getElementById('hud-total');
    const hudOnline  = document.getElementById('hud-online');
    const toast      = document.getElementById('toast');
    const toastImg   = document.getElementById('toast-img');
    const logro        = document.getElementById('logro-overlay');
    const logroImg     = document.getElementById('logro-img');
    const logroTexto   = document.getElementById('logro-texto');
    const logroCanvas  = document.getElementById('logro-canvas');

    let cursor      = 0;
    let cursorTest  = 0;   // cursor separado para test_events
    let colaMSN     = [];
    let mostrando   = false;
    let milestoneQueue = [];
    let totalFotos  = 0;

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
        if (total  !== undefined && hudTotal)  { totalFotos = total; hudTotal.textContent  = total; }
        if (online !== undefined && hudOnline) { hudOnline.textContent = online; }
    }

    async function pollStats() {
        try {
            const res  = await fetch('api/stats.php', { cache: 'no-store' });
            const data = await res.json();
            if (data.ok) actualizarHUD(data.total, data.online);
        } catch (e) {}
    }

    // ---------- Toast MSN ----------
    async function mostrarToast(item) {
        const ok = await precargar(item.url);
        if (!ok) return;

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

    // ---------- Partículas Y2K ----------
    function iniciarParticulas(canvas) {
        canvas.hidden = false;
        const ctx = canvas.getContext('2d');
        canvas.width  = window.innerWidth;
        canvas.height = window.innerHeight;
        const W = canvas.width;
        const H = canvas.height;

        const colores = ['#1f56ff','#6fb3ff','#ff2aff','#ffe600','#00ffe5','#ffffff'];
        const particulas = Array.from({ length: 120 }, () => ({
            x:     Math.random() * W,
            y:     Math.random() * H * 0.5,
            vx:    (Math.random() - 0.5) * 3,
            vy:    Math.random() * 2 + 1,
            size:  Math.random() * 5 + 2,
            color: colores[Math.floor(Math.random() * colores.length)],
            alpha: 1,
        }));

        let raf;
        (function frame() {
            ctx.clearRect(0, 0, W, H);
            ctx.fillStyle = 'rgba(0,0,0,0.04)';
            for (let y = 0; y < H; y += 4) ctx.fillRect(0, y, W, 2);

            particulas.forEach(p => {
                p.x    += p.vx;
                p.y    += p.vy;
                p.vy   += 0.05;
                p.alpha = Math.max(0, p.alpha - 0.008);
                ctx.globalAlpha = p.alpha;
                ctx.fillStyle   = p.color;
                ctx.shadowBlur  = 10;
                ctx.shadowColor = p.color;
                const s = Math.round(p.size);
                ctx.fillRect(Math.round(p.x), Math.round(p.y), s, s);
                ctx.shadowBlur = 0;
            });
            ctx.globalAlpha = 1;
            raf = requestAnimationFrame(frame);
        })();

        return function detener() {
            cancelAnimationFrame(raf);
            canvas.hidden = true;
            ctx.clearRect(0, 0, W, H);
        };
    }

    // ---------- Animación de Logro ----------
    async function mostrarLogro(milestone) {
        const ok = await precargar(milestone.url);
        if (!ok) return;

        logroImg.src       = milestone.url;
        logroImg.className = 'logro-foto ' + (milestone.orientation || 'vertical');
        logroTexto.textContent = '';
        logroTexto.classList.remove('visible');

        logro.classList.remove('visible', 'saliendo');
        logro.hidden = false;
        void logro.offsetWidth;
        logro.classList.add('visible');

        await espera(700);
        logroTexto.textContent = milestone.quantity + ' MEMORIES';
        logroTexto.classList.add('visible');

        await espera(500);
        const detener = iniciarParticulas(logroCanvas);

        await espera(MILESTONE_MS - 1400);

        detener();
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

    // ---------- Polling feed ----------
    async function consultarFeed() {
        try {
            const url  = 'api/feed.php?after=' + cursor + '&afterTest=' + cursorTest;
            const res  = await fetch(url, { cache: 'no-store' });
            const data = await res.json();
            if (!data.ok) return;

            cursor = data.cursor;
            if (data.cursorTest !== undefined) cursorTest = data.cursorTest;

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

    // Video nunca se detiene
    setInterval(() => {
        if (video && (video.paused || video.ended)) video.play().catch(() => {});
    }, 3000);

    // ---------- Inicio ----------
    const btnIniciar = document.getElementById('btn-iniciar');
    if (btnIniciar) {
        btnIniciar.addEventListener('click', async () => {
            inicio.classList.add('oculto');
            try { await document.documentElement.requestFullscreen(); } catch (e) {}
            if (video) video.play().catch(() => {});
            await consultarFeed();
            await pollStats();
            setInterval(consultarFeed, POLL_MS);
            setInterval(pollStats, 15000);
        });
    }
})();

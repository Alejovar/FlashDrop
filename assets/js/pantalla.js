// pantalla.js v5 — temas de logro variables por hito
(function () {
    'use strict';

    function metaInt(name, fallback) {
        const el = document.querySelector('meta[name="' + name + '"]');
        return el ? (parseInt(el.content, 10) || fallback) : fallback;
    }

    const POLL_MS      = metaInt('poll-seconds',  3) * 1000;
    const TOAST_MS     = metaInt('toast-seconds', 8) * 1000;
    const MILESTONE_MS = 14000;
    const FADE_MS      = 650;

    // ============================================================
    //  TEMAS DE LOGRO — uno por cada múltiplo de 15
    //  Se ciclan: logro 120 usa el tema del 120%7 = tema[0], etc.
    // ============================================================
    const TEMAS = [
        // 0 → qty % (TEMAS.length * 15) === 15  → logro 15, 120, 225...
        {
            badge:        'PRIMERA CHISPA',
            record:       'EL VIAJE COMIENZA',
            subtop:       'LA NOCHE APENAS EMPIEZA',
            subbotSuffix: '',
            overlay:      'rgba(2,3,12,.82)',
            titulo: {
                gradient: 'linear-gradient(180deg,#fff 0%,#ffe0a0 20%,#ffaa00 45%,#ff6600 60%,#ffbb44 80%,#fff 100%)',
                glow1:    'rgba(255,180,30,.95)',
                glow2:    'rgba(255,100,0,.7)',
            },
            marco:  'conic-gradient(from 0deg,#ff6600,#ffcc00,#ff9900,#fff,#ffcc00,#ff6600)',
            esq:    '#ffcc44',
            particulas: ['#ff6600','#ffcc00','#fff','#ffaa33','#ff9900','#ffe066'],
            estrellas:  ['#ffcc44','#fff','#ff9900','#ffee88'],
            marcoVel:   '2.5s',
        },
        // 1 → 30, 135, 240...
        {
            badge:        'SUBIENDO EL NIVEL',
            record:       'EL RITMO NO PARA',
            subtop:       'LA FIESTA ESTA PRENDIDA',
            subbotSuffix: '',
            overlay:      'rgba(3,2,14,.84)',
            titulo: {
                gradient: 'linear-gradient(180deg,#fff 0%,#e0c0ff 20%,#aa55ff 45%,#7700ff 60%,#cc88ff 80%,#fff 100%)',
                glow1:    'rgba(180,80,255,.95)',
                glow2:    'rgba(120,0,255,.7)',
            },
            marco:  'conic-gradient(from 0deg,#aa00ff,#ff00cc,#7700ff,#fff,#ff00cc,#aa00ff)',
            esq:    '#dd88ff',
            particulas: ['#aa00ff','#ff00cc','#fff','#cc55ff','#ff44ee','#9900ff'],
            estrellas:  ['#dd88ff','#fff','#ff00cc','#cc55ff'],
            marcoVel:   '3s',
        },
        // 2 → 45, 150, 255...
        {
            badge:        'EN LLAMAS',
            record:       'TEMPERATURA MAXIMA',
            subtop:       'NADIE SE PUEDE IR TODAVIA',
            subbotSuffix: '',
            overlay:      'rgba(10,2,2,.84)',
            titulo: {
                gradient: 'linear-gradient(180deg,#fff 0%,#ffc0c0 20%,#ff4444 45%,#cc0000 60%,#ff8888 80%,#fff 100%)',
                glow1:    'rgba(255,60,60,.95)',
                glow2:    'rgba(200,0,0,.7)',
            },
            marco:  'conic-gradient(from 0deg,#ff0000,#ff6600,#ff0033,#fff,#ff6600,#ff0000)',
            esq:    '#ff6666',
            particulas: ['#ff2200','#ff6600','#fff','#ff4444','#ff8800','#ffcc00'],
            estrellas:  ['#ff6666','#fff','#ff3300','#ff9944'],
            marcoVel:   '2s',
        },
        // 3 → 60, 165, 270...
        {
            badge:        'PUNTO DE NO RETORNO',
            record:       'HISTORIA EN CONSTRUCCION',
            subtop:       'ESTO YA ES UN CLASICO',
            subbotSuffix: '',
            overlay:      'rgba(0,6,14,.85)',
            titulo: {
                gradient: 'linear-gradient(180deg,#fff 0%,#a0e8ff 20%,#00ccff 45%,#0066cc 60%,#44ddff 80%,#fff 100%)',
                glow1:    'rgba(0,200,255,.95)',
                glow2:    'rgba(0,100,200,.7)',
            },
            marco:  'conic-gradient(from 0deg,#00ccff,#0066ff,#00ffee,#fff,#0066ff,#00ccff)',
            esq:    '#44ddff',
            particulas: ['#00ccff','#0066ff','#fff','#00ffee','#44aaff','#aaeeff'],
            estrellas:  ['#44ddff','#fff','#00ccff','#aaeeff'],
            marcoVel:   '3.5s',
        },
        // 4 → 75, 180, 285...
        {
            badge:        'MOMENTO EPICO',
            record:       'LA LEYENDA CRECE',
            subtop:       'CADA FOTO UNA HISTORIA',
            subbotSuffix: '',
            overlay:      'rgba(2,8,4,.85)',
            titulo: {
                gradient: 'linear-gradient(180deg,#fff 0%,#aaffcc 20%,#00ee88 45%,#007744 60%,#55ffaa 80%,#fff 100%)',
                glow1:    'rgba(0,220,120,.95)',
                glow2:    'rgba(0,130,70,.7)',
            },
            marco:  'conic-gradient(from 0deg,#00ee88,#00ffcc,#44ff88,#fff,#00ffcc,#00ee88)',
            esq:    '#55ffaa',
            particulas: ['#00ee88','#00ffcc','#fff','#44ff88','#aaffdd','#00cc66'],
            estrellas:  ['#55ffaa','#fff','#00ee88','#aaffdd'],
            marcoVel:   '2.8s',
        },
        // 5 → 90, 195, 300...
        {
            badge:        'REINO DE LA NOCHE',
            record:       'NIVEL INALCANZABLE',
            subtop:       'LA FIESTA ES HISTORIA VIVA',
            subbotSuffix: '',
            overlay:      'rgba(8,4,2,.86)',
            titulo: {
                gradient: 'linear-gradient(180deg,#fff 0%,#ffd0a0 20%,#ff8833 45%,#cc4400 60%,#ffaa55 80%,#fff 100%)',
                glow1:    'rgba(255,140,30,.95)',
                glow2:    'rgba(200,70,0,.7)',
            },
            marco:  'conic-gradient(from 0deg,#ff8800,#ffcc00,#ff4400,#fff,#ffcc44,#ff8800)',
            esq:    '#ffaa55',
            particulas: ['#ff8800','#ffcc00','#fff','#ff5500','#ffaa33','#ffe066'],
            estrellas:  ['#ffaa55','#fff','#ff8800','#ffe088'],
            marcoVel:   '2.2s',
        },
        // 6 → 105, 210, 315...
        {
            badge:        'MODO LEGENDARIO',
            record:       'INMORTALIZADO',
            subtop:       'LA FIESTA ESTA EN SU MEJOR MOMENTO',
            subbotSuffix: '',
            overlay:      'rgba(2,3,12,.87)',
            titulo: {
                gradient: 'linear-gradient(180deg,#fff 0%,#c8d8ff 20%,#7eb3ff 40%,#3366ff 55%,#1144dd 65%,#88aaff 80%,#fff 100%)',
                glow1:    'rgba(100,160,255,.95)',
                glow2:    'rgba(31,86,255,.75)',
            },
            marco:  'conic-gradient(from 0deg,#ff00ff,#0066ff,#00ffff,#ffff00,#ff0099,#0066ff,#ff00ff)',
            esq:    '#7eb3ff',
            particulas: ['#1f56ff','#6fb3ff','#ff2aff','#ffe600','#00ffe5','#ff6b00','#ff0066','#ffffff'],
            estrellas:  ['#fff','#7eb3ff','#ffcc44','#ff66cc','#00ffee'],
            marcoVel:   '1.8s',
        },
    ];

    function getTema(quantity) {
        // Cicla los temas según el índice del múltiplo de 15
        const idx = Math.floor(quantity / 15) - 1;
        return TEMAS[idx % TEMAS.length];
    }

    // ============================================================
    const video   = document.getElementById('visual');
    const inicio  = document.getElementById('inicio');
    const hudTotal  = document.getElementById('hud-total');
    const hudOnline = document.getElementById('hud-online');
    const msnWindow = document.getElementById('msn-window');
    const msnFoto   = document.getElementById('msn-foto');
    const logro          = document.getElementById('logro-overlay');
    const logroCanvas    = document.getElementById('logro-canvas');
    const logroEstrellas = document.getElementById('logro-estrellas');
    const logroImg       = document.getElementById('logro-img');
    const logroBadge     = document.getElementById('logro-badge');
    const logroBadgeText = document.querySelector('#logro-badge .logro-badge-text');
    const logroTitulo    = document.getElementById('logro-titulo');
    const logroFotoWrap  = document.getElementById('logro-foto-wrap');
    const logroSub       = document.getElementById('logro-sub');
    const logroSubTop    = document.querySelector('#logro-sub .logro-sub-top');
    const logroSubBot    = document.querySelector('#logro-sub .logro-sub-bot');
    const logroRecord    = document.getElementById('logro-record');
    const logroRecordText= document.querySelector('#logro-record .logro-record-text');
    const logroMarcoBrillo = document.querySelector('.logro-marco-brillo');

    let cursor = 0, cursorTest = 0;
    let colaMSN = [], mostrando = false, milestoneQueue = [];

    const espera    = ms  => new Promise(r => setTimeout(r, ms));
    const precargar = url => new Promise(resolve => {
        const i = new Image();
        i.onload = () => resolve(true); i.onerror = () => resolve(false); i.src = url;
    });

    // ---------- HUD ----------
    function actualizarHUD(total, online) {
        if (total  !== undefined && hudTotal)  hudTotal.textContent  = total;
        if (online !== undefined && hudOnline) hudOnline.textContent = online;
    }
    async function pollStats() {
        try { const d = await (await fetch('api/stats.php',{cache:'no-store'})).json(); if(d.ok) actualizarHUD(d.total,d.online); } catch(e){}
    }

    // ---------- Ventana MSN ----------
    async function mostrarMSN(item) {
        const ok = await precargar(item.url);
        if (!ok) return;
        msnWindow.classList.remove('vertical','horizontal','cuadrada','visible','saliendo');
        msnWindow.classList.add(item.orientation || 'vertical');
        msnFoto.src  = item.url;
        msnWindow.hidden = false;
        void msnWindow.offsetWidth;
        msnWindow.classList.add('visible');
        await espera(TOAST_MS);
        msnWindow.classList.remove('visible');
        msnWindow.classList.add('saliendo');
        await espera(FADE_MS);
        msnWindow.hidden = true;
        msnWindow.classList.remove('saliendo');
        msnFoto.src = '';
    }
    async function procesarColaMSN() {
        if (mostrando) return;
        mostrando = true;
        while (colaMSN.length > 0) { try { await mostrarMSN(colaMSN.shift()); } catch(e){} await espera(400); }
        mostrando = false;
    }

    // ---------- Partículas ----------
    function iniciarParticulas(canvas, paleta) {
        canvas.hidden = false;
        const ctx = canvas.getContext('2d');
        canvas.width = window.innerWidth; canvas.height = window.innerHeight;
        const W = canvas.width, H = canvas.height;
        const pts = Array.from({length:180}, () => ({
            x: Math.random()*W, y: Math.random()*H,
            vx:(Math.random()-.5)*4, vy:-(Math.random()*4+1),
            grav:Math.random()*.1+.04,
            size:Math.random()*7+2,
            color:paleta[Math.floor(Math.random()*paleta.length)],
            alpha:1, decay:Math.random()*.005+.003,
        }));
        let raf;
        (function frame() {
            ctx.clearRect(0,0,W,H);
            ctx.fillStyle='rgba(0,0,0,0.04)';
            for(let y=0;y<H;y+=4) ctx.fillRect(0,y,W,1);
            pts.forEach(p=>{
                p.x+=p.vx; p.y+=p.vy; p.vy+=p.grav;
                p.alpha=Math.max(0,p.alpha-p.decay);
                if(p.alpha<=0){ p.x=Math.random()*W; p.y=H+10; p.vy=-(Math.random()*4+1); p.alpha=1; p.color=paleta[Math.floor(Math.random()*paleta.length)]; }
                ctx.globalAlpha=p.alpha; ctx.fillStyle=p.color;
                ctx.shadowBlur=14; ctx.shadowColor=p.color;
                const s=Math.round(p.size); ctx.fillRect(Math.round(p.x),Math.round(p.y),s,s);
                ctx.shadowBlur=0;
            });
            ctx.globalAlpha=1;
            raf=requestAnimationFrame(frame);
        })();
        return ()=>{ cancelAnimationFrame(raf); canvas.hidden=true; ctx.clearRect(0,0,W,H); };
    }

    // ---------- Estrellas ----------
    function lanzarEstrellas(container, colores) {
        container.innerHTML='';
        if (!document.getElementById('kf-estrella')) {
            const st=document.createElement('style'); st.id='kf-estrella';
            st.textContent=`@keyframes estrella-ap{0%{opacity:0;transform:scale(.3) rotate(0deg)}50%{opacity:1;transform:scale(1.3) rotate(180deg)}100%{opacity:.3;transform:scale(.7) rotate(360deg)}}`;
            document.head.appendChild(st);
        }
        for(let i=0;i<28;i++){
            const el=document.createElement('div'); el.className='estrella';
            const size=Math.random()*24+8;
            el.style.cssText=`width:${size}px;height:${size}px;left:${Math.random()*100}%;top:${Math.random()*100}%;background:${colores[Math.floor(Math.random()*colores.length)]};filter:drop-shadow(0 0 ${size*.4}px ${colores[0]});animation:estrella-ap ${Math.random()*1.5+.8}s ease-in-out ${Math.random()*2.5}s infinite alternate;`;
            container.appendChild(el);
        }
        return ()=>{ container.innerHTML=''; };
    }

    // ---------- Aplicar tema al overlay ----------
    function aplicarTema(tema) {
        const r = logro.style;

        // Fondo del overlay
        // (se aplica vía clase .visible, pero podemos sobreescribir con CSS inline)
        logro.style.setProperty('--logro-overlay-bg', tema.overlay);

        // Gradiente y glow del título
        logroTitulo.style.backgroundImage  = tema.titulo.gradient;
        logroTitulo.style.setProperty('--glow1', tema.titulo.glow1);
        logroTitulo.style.setProperty('--glow2', tema.titulo.glow2);
        // Filter directo porque las CSS vars en filter son complejas
        logroTitulo.style.filter = `drop-shadow(0 0 18px ${tema.titulo.glow1}) drop-shadow(0 0 45px ${tema.titulo.glow2})`;

        // Marco giratorio
        if (logroMarcoBrillo) {
            logroMarcoBrillo.style.background       = tema.marco;
            logroMarcoBrillo.style.animationDuration = tema.marcoVel;
        }

        // Color esquinas
        document.querySelectorAll('.esq').forEach(e => {
            e.style.borderColor = tema.esq;
            e.style.boxShadow   = `0 0 10px ${tema.esq}`;
        });

        // Textos variables
        if (logroBadgeText)  logroBadgeText.textContent  = tema.badge;
        if (logroSubTop)     logroSubTop.textContent      = tema.subtop;
        if (logroSubBot)     logroSubBot.textContent      = 'ALEJOFEST VOL.21' + (tema.subbotSuffix || '');
        if (logroRecordText) logroRecordText.textContent  = tema.record;
    }

    // ---------- Reset ----------
    function resetLogro() {
        [logroBadge, logroTitulo, logroFotoWrap, logroSub, logroRecord].forEach(el => el && el.classList.remove('visible'));
        if (logroImg) logroImg.src = '';
        // Limpiar estilos inline del tema anterior
        if (logroTitulo) { logroTitulo.style.backgroundImage=''; logroTitulo.style.filter=''; }
        if (logroMarcoBrillo) { logroMarcoBrillo.style.background=''; logroMarcoBrillo.style.animationDuration=''; }
        document.querySelectorAll('.esq').forEach(e => { e.style.borderColor=''; e.style.boxShadow=''; });
    }

    // ---------- Animación de logro ----------
    async function mostrarLogro(milestone) {
        const ok = await precargar(milestone.url);
        if (!ok) return;

        resetLogro();

        const tema = getTema(milestone.quantity);

        // Preparar contenido
        logroImg.src = milestone.url;
        logroImg.className = 'logro-foto ' + (milestone.orientation || 'vertical');
        logroTitulo.textContent = milestone.quantity + ' MEMORIES';

        // Aplicar tema visual
        aplicarTema(tema);

        // Overlay oscuro
        logro.classList.remove('visible','saliendo');
        logro.hidden = false;
        void logro.offsetWidth;
        logro.classList.add('visible');
        await espera(350);

        // Efectos de fondo
        const detenerParticulas = iniciarParticulas(logroCanvas, tema.particulas);
        const detenerEstrellas  = lanzarEstrellas(logroEstrellas, tema.estrellas);

        // Stagger de elementos (badge → título → foto → sub → record)
        await espera(150);  logroBadge.classList.add('visible');
        await espera(280);  logroTitulo.classList.add('visible');
        await espera(420);  logroFotoWrap.classList.add('visible');
        await espera(550);  logroSub.classList.add('visible');
        await espera(280);  logroRecord.classList.add('visible');

        // Duración del show
        await espera(MILESTONE_MS - 2100);

        // Fade out
        detenerParticulas();
        detenerEstrellas();
        logro.classList.add('saliendo');
        logro.classList.remove('visible');
        await espera(800);
        logro.hidden = true;
        logro.classList.remove('saliendo');
        resetLogro();
    }

    async function procesarMilestones() {
        while (milestoneQueue.length > 0) {
            try { await mostrarLogro(milestoneQueue.shift()); } catch(e){}
            await espera(600);
        }
    }

    // ---------- Polling ----------
    async function consultarFeed() {
        try {
            const d = await (await fetch('api/feed.php?after='+cursor+'&afterTest='+cursorTest,{cache:'no-store'})).json();
            if(!d.ok) return;
            cursor=d.cursor;
            if(d.cursorTest!==undefined) cursorTest=d.cursorTest;
            if(d.total!==undefined) actualizarHUD(d.total);
            if(d.items&&d.items.length){ colaMSN.push(...d.items); procesarColaMSN(); }
            if(d.milestone){ milestoneQueue.push(d.milestone); if(milestoneQueue.length===1) procesarMilestones(); }
        } catch(e){}
    }

    // Video nunca se detiene
    setInterval(()=>{ if(video&&(video.paused||video.ended)) video.play().catch(()=>{}); },3000);

    // Inicio
    const btnIniciar = document.getElementById('btn-iniciar');
    if(btnIniciar){
        btnIniciar.addEventListener('click', async ()=>{
            inicio.classList.add('oculto');
            try{ await document.documentElement.requestFullscreen(); }catch(e){}
            if(video) video.play().catch(()=>{});
            await consultarFeed();
            await pollStats();
            setInterval(consultarFeed, POLL_MS);
            setInterval(pollStats, 15000);
        });
    }
})();

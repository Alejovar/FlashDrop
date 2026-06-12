// admin.js v2 — ocultar, restaurar, eliminar, reproducir en pantalla.
// También actualiza estadísticas en tiempo real.
(function () {
    'use strict';

    const csrf = document.querySelector('meta[name="csrf"]').content;
    const msg  = document.getElementById('msg-admin');

    function aviso(texto, ok) {
        msg.textContent = texto;
        msg.className   = 'mensaje ' + (ok ? 'ok' : 'error');
        clearTimeout(aviso._t);
        aviso._t = setTimeout(() => { msg.textContent = ''; }, 3500);
    }

    async function accion(action, photoId) {
        const fd = new FormData();
        fd.append('action', action);
        fd.append('photo_id', photoId);
        const res = await fetch('../api/admin_action.php', {
            method:  'POST',
            body:    fd,
            headers: { 'X-CSRF-Token': csrf },
        });
        return res.json();
    }

    document.querySelectorAll('.admin-card').forEach(card => {
        const id        = card.dataset.id;
        const btnToggle = card.querySelector('.btn-toggle');
        const btnReplay = card.querySelector('.btn-replay');
        const btnDelete = card.querySelector('.btn-delete');
        const estado    = card.querySelector('.estado');

        // Ocultar / Restaurar
        btnToggle.addEventListener('click', async () => {
            const ocultar = !card.classList.contains('oculta');
            btnToggle.disabled = true;
            try {
                const data = await accion(ocultar ? 'hide' : 'restore', id);
                if (!data.ok) { aviso(data.error || 'Error', false); return; }
                card.classList.toggle('oculta', ocultar);
                estado.textContent = ocultar ? 'OCULTA' : 'VISIBLE';
                estado.className   = 'estado ' + (ocultar ? 'no-visible' : 'visible');
                btnToggle.textContent = ocultar ? 'RESTAURAR' : 'OCULTAR';
                btnToggle.classList.toggle('peligro', !ocultar);
                btnReplay.disabled = ocultar;
                aviso(ocultar ? 'Foto oculta.' : 'Foto restaurada.', true);
            } catch (e) {
                aviso('Sin conexion con el servidor.', false);
            } finally {
                btnToggle.disabled = false;
            }
        });

        // Reproducir en pantalla
        btnReplay.addEventListener('click', async () => {
            btnReplay.disabled = true;
            try {
                const data = await accion('replay', id);
                if (data.ok) aviso('Foto encolada para la pantalla grande.', true);
                else aviso(data.error || 'Error', false);
            } catch (e) {
                aviso('Sin conexion con el servidor.', false);
            } finally {
                btnReplay.disabled = card.classList.contains('oculta');
            }
        });

        // Eliminar permanentemente
        if (btnDelete) {
            btnDelete.addEventListener('click', async () => {
                if (!confirm('Eliminar permanentemente esta foto? Esta accion no se puede deshacer.')) return;
                btnDelete.disabled = true;
                try {
                    const data = await accion('delete', id);
                    if (data.ok) {
                        card.remove();
                        aviso('Foto eliminada permanentemente.', true);
                    } else {
                        aviso(data.error || 'Error', false);
                        btnDelete.disabled = false;
                    }
                } catch (e) {
                    aviso('Sin conexion con el servidor.', false);
                    btnDelete.disabled = false;
                }
            });
        }
    });

    // --- Actualizar estadísticas en tiempo real ---
    async function actualizarStats() {
        try {
            const res  = await fetch('../api/stats.php', { cache: 'no-store' });
            const data = await res.json();
            if (!data.ok) return;
            const el = (id) => document.getElementById(id);
            if (el('stat-total'))    el('stat-total').textContent    = data.total;
            if (el('stat-online'))   el('stat-online').textContent   = data.online;
            if (el('stat-lasthour')) el('stat-lasthour').textContent = data.lastHour;
            if (el('stat-last'))     el('stat-last').textContent     = data.lastPhoto || 'ninguna';
        } catch (e) {}
    }
    actualizarStats();
    setInterval(actualizarStats, 10000);
})();

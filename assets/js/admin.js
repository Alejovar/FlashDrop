// admin.js — acciones del dashboard: ocultar / restaurar / reproducir en pantalla.
(function () {
    'use strict';

    const csrf = document.querySelector('meta[name="csrf"]').content;
    const msg  = document.getElementById('msg-admin');

    function aviso(texto, ok) {
        msg.textContent = texto;
        msg.className = 'mensaje ' + (ok ? 'ok' : 'error');
        clearTimeout(aviso._t);
        aviso._t = setTimeout(() => { msg.textContent = ''; }, 3500);
    }

    async function accion(action, photoId) {
        const fd = new FormData();
        fd.append('action', action);
        fd.append('photo_id', photoId);
        const res = await fetch('../api/admin_action.php', {
            method: 'POST',
            body: fd,
            headers: { 'X-CSRF-Token': csrf },
        });
        return res.json();
    }

    document.querySelectorAll('.admin-card').forEach(card => {
        const id        = card.dataset.id;
        const btnToggle = card.querySelector('.btn-toggle');
        const btnReplay = card.querySelector('.btn-replay');
        const estado    = card.querySelector('.estado');

        btnToggle.addEventListener('click', async () => {
            const ocultar = !card.classList.contains('oculta');
            btnToggle.disabled = true;
            try {
                const data = await accion(ocultar ? 'hide' : 'restore', id);
                if (!data.ok) { aviso(data.error || 'Error', false); return; }

                card.classList.toggle('oculta', ocultar);
                estado.textContent = ocultar ? '● Oculta' : '● Visible';
                estado.className = 'estado ' + (ocultar ? 'no-visible' : 'visible');
                btnToggle.textContent = ocultar ? '♻ Restaurar' : '🚫 Ocultar';
                btnToggle.classList.toggle('peligro', !ocultar);
                btnReplay.disabled = ocultar;
                aviso(ocultar ? 'Foto oculta de la galería y la pantalla.' : 'Foto restaurada.', true);
            } catch (e) {
                aviso('Sin conexión con el servidor.', false);
            } finally {
                btnToggle.disabled = false;
            }
        });

        btnReplay.addEventListener('click', async () => {
            btnReplay.disabled = true;
            try {
                const data = await accion('replay', id);
                if (data.ok) aviso('Foto encolada: saldrá en la pantalla grande en unos segundos. 📺', true);
                else aviso(data.error || 'Error', false);
            } catch (e) {
                aviso('Sin conexión con el servidor.', false);
            } finally {
                btnReplay.disabled = card.classList.contains('oculta');
            }
        });
    });
})();

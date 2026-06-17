// admin.js v2
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

    // --- Moderación por foto ---
    document.querySelectorAll('.admin-card').forEach(card => {
        const id        = card.dataset.id;
        const btnToggle = card.querySelector('.btn-toggle');
        const btnReplay = card.querySelector('.btn-replay');
        const btnDelete = card.querySelector('.btn-delete');
        const estado    = card.querySelector('.estado');

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

    // --- Stats en tiempo real ---
    async function actualizarStats() {
        try {
            const res  = await fetch('../api/stats.php', { cache: 'no-store' });
            const data = await res.json();
            if (!data.ok) return;
            const el = id => document.getElementById(id);
            if (el('stat-total'))    el('stat-total').textContent    = data.total;
            if (el('stat-online'))   el('stat-online').textContent   = data.online;
            if (el('stat-lasthour')) el('stat-lasthour').textContent = data.lastHour;
            if (el('stat-last'))     el('stat-last').textContent     = data.lastPhoto || 'ninguna';
        } catch (e) {}
    }
    actualizarStats();
    setInterval(actualizarStats, 10000);

    // --- Test de animaciones ---
    const msgTest          = document.getElementById('msg-test');
    const btnTestToast     = document.getElementById('btn-test-toast');
    const btnTestMilestone = document.getElementById('btn-test-milestone');
    const selQty           = document.getElementById('sel-milestone-qty');

    function avisoTest(texto, ok) {
        if (!msgTest) return;
        msgTest.textContent = texto;
        msgTest.className   = 'mensaje ' + (ok ? 'ok' : 'error');
        clearTimeout(avisoTest._t);
        avisoTest._t = setTimeout(() => { msgTest.textContent = ''; }, 4000);
    }

    async function dispararTest(type, quantity) {
        const fd = new FormData();
        fd.append('type',     type);
        fd.append('quantity', quantity || 15);
        try {
            const res  = await fetch('../api/test_event.php', {
                method:  'POST',
                body:    fd,
                headers: { 'X-CSRF-Token': csrf },
            });
            const data = await res.json();
            if (data.ok) {
                avisoTest('Evento enviado. Aparece en pantalla.php en ~3 segundos.', true);
            } else {
                avisoTest(data.error || 'Error al disparar el evento.', false);
            }
        } catch (e) {
            avisoTest('Sin conexion con el servidor.', false);
        }
    }

    if (btnTestToast) {
        btnTestToast.addEventListener('click', () => {
            btnTestToast.disabled = true;
            dispararTest('toast').finally(() => { btnTestToast.disabled = false; });
        });
    }

    if (btnTestMilestone) {
        btnTestMilestone.addEventListener('click', () => {
            const qty = selQty ? parseInt(selQty.value, 10) : 15;
            btnTestMilestone.disabled = true;
            dispararTest('milestone', qty).finally(() => { btnTestMilestone.disabled = false; });
        });
    }

    // --- Invitados / RSVP toggle ---
    const msgInvitados = document.getElementById('msg-invitados');

    function avisoInvitados(texto, ok) {
        if (!msgInvitados) return;
        msgInvitados.textContent = texto;
        msgInvitados.className   = 'mensaje ' + (ok ? 'ok' : 'error');
        clearTimeout(avisoInvitados._t);
        avisoInvitados._t = setTimeout(() => { msgInvitados.textContent = ''; }, 3500);
    }

    document.querySelectorAll('.btn-toggle-invitado').forEach(btn => {
        btn.addEventListener('click', async () => {
            const row = btn.closest('.invitado-row');
            const id  = row.dataset.id;

            btn.disabled = true;
            try {
                const fd = new FormData();
                fd.append('id', id);
                const res  = await fetch('../api/invitado_toggle.php', {
                    method:  'POST',
                    body:    fd,
                    headers: { 'X-CSRF-Token': csrf },
                });
                const data = await res.json();

                if (data.ok) {
                    row.classList.toggle('deshabilitado', !data.habilitado);
                    row.querySelector('.invitado-estado').textContent =
                        data.habilitado ? 'CONFIRMADO' : 'DESHABILITADO';
                    btn.textContent = data.habilitado ? 'DESHABILITAR' : 'HABILITAR';
                    avisoInvitados(
                        data.habilitado ? 'Invitado habilitado.' : 'Invitado deshabilitado.',
                        true
                    );
                } else {
                    avisoInvitados(data.error || 'Error', false);
                }
            } catch (e) {
                avisoInvitados('Sin conexion con el servidor.', false);
            } finally {
                btn.disabled = false;
            }
        });
    });

    // --- Selector de video loop ---
    const loopsLista = document.getElementById('loops-lista');
    const msgLoop    = document.getElementById('msg-loop');

    function avisoLoop(texto, ok) {
        if (!msgLoop) return;
        msgLoop.textContent = texto;
        msgLoop.className   = 'mensaje ' + (ok ? 'ok' : 'error');
        clearTimeout(avisoLoop._t);
        avisoLoop._t = setTimeout(() => { msgLoop.textContent = ''; }, 3500);
    }

    function escapeHtmlAdmin(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function renderLoops(videos, actual) {
        if (!loopsLista) return;
        if (!videos.length) {
            loopsLista.innerHTML = '<p style="text-align:center; color:var(--texto-suave); padding:14px 0;">No hay videos en video/loops/</p>';
            return;
        }
        loopsLista.innerHTML = videos.map(v => `
            <div class="loop-row ${v === actual ? 'activo' : ''}" data-archivo="${escapeHtmlAdmin(v)}">
                <span class="loop-nombre">${escapeHtmlAdmin(v)}</span>
                ${v === actual
                    ? '<span class="loop-estado">EN USO</span>'
                    : '<button class="btn mini btn-usar-loop" type="button">USAR ESTE</button>'}
            </div>
        `).join('');

        loopsLista.querySelectorAll('.btn-usar-loop').forEach(btn => {
            btn.addEventListener('click', async () => {
                const row = btn.closest('.loop-row');
                const archivo = row.dataset.archivo;
                btn.disabled = true;
                btn.textContent = 'CAMBIANDO...';
                try {
                    const fd = new FormData();
                    fd.append('archivo', archivo);
                    const res  = await fetch('../api/loop_cambiar.php', {
                        method:  'POST',
                        body:    fd,
                        headers: { 'X-CSRF-Token': csrf },
                    });
                    const data = await res.json();
                    if (data.ok) {
                        avisoLoop('Video cambiado. Se actualizará en pantalla.php en unos segundos.', true);
                        cargarLoops();
                    } else {
                        avisoLoop(data.error || 'Error', false);
                        btn.disabled = false;
                        btn.textContent = 'USAR ESTE';
                    }
                } catch (e) {
                    avisoLoop('Sin conexion con el servidor.', false);
                    btn.disabled = false;
                    btn.textContent = 'USAR ESTE';
                }
            });
        });
    }

    async function cargarLoops() {
        if (!loopsLista) return;
        try {
            const res  = await fetch('../api/loops_listar.php', { cache: 'no-store' });
            const data = await res.json();
            if (data.ok) renderLoops(data.videos, data.actual);
        } catch (e) {
            loopsLista.innerHTML = '<p style="text-align:center; color:var(--texto-suave); padding:14px 0;">Sin conexion con el servidor.</p>';
        }
    }

    cargarLoops();

    // --- Eliminar invitado permanentemente ---
    document.querySelectorAll('.btn-eliminar-invitado').forEach(btn => {
        btn.addEventListener('click', async () => {
            const row    = btn.closest('.invitado-row');
            const id     = row.dataset.id;
            const nombre = row.querySelector('.invitado-nombre').textContent;

            if (!confirm('Eliminar permanentemente a "' + nombre + '"? Esta accion no se puede deshacer.')) return;

            btn.disabled = true;
            try {
                const fd = new FormData();
                fd.append('id', id);
                const res  = await fetch('../api/invitado_eliminar.php', {
                    method:  'POST',
                    body:    fd,
                    headers: { 'X-CSRF-Token': csrf },
                });
                const data = await res.json();

                if (data.ok) {
                    row.remove();
                    avisoInvitados('Invitado eliminado permanentemente.', true);
                } else {
                    avisoInvitados(data.error || 'Error', false);
                    btn.disabled = false;
                }
            } catch (e) {
                avisoInvitados('Sin conexion con el servidor.', false);
                btn.disabled = false;
            }
        });
    });

})();
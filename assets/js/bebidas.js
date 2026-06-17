// bebidas.js — tablet en la mesa de bebidas
(function () {
    'use strict';

    const grid  = document.getElementById('bebidas-grid');
    const toast = document.getElementById('bebidas-toast');

    let toastTimer;
    let invitadosActuales = {};

    function mostrarToast(texto) {
        toast.textContent = texto;
        toast.hidden = false;
        void toast.offsetWidth;
        toast.classList.add('visible');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => {
            toast.classList.remove('visible');
            setTimeout(() => { toast.hidden = true; }, 300);
        }, 1500);
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function renderInvitados(invitados) {
        if (!invitados.length) {
            grid.innerHTML = '<p class="bebidas-cargando">No hay invitados confirmados todavía.</p>';
            return;
        }

        grid.innerHTML = '';
        invitados.forEach(inv => {
            const card = document.createElement('div');
            card.className = 'bebida-card';
            card.dataset.id = inv.id;
            card.innerHTML = `
                <span class="bebida-nombre">${escapeHtml(inv.nombre)}</span>
                <span class="bebida-contador" data-contador>${inv.bebidas}</span>
                <span class="bebida-contador-label">BOLSITAS</span>
            `;
            card.addEventListener('click', () => sumarBebida(inv.id, card));
            grid.appendChild(card);
            invitadosActuales[inv.id] = inv.bebidas;
        });
    }

    function actualizarContadores(invitados) {
        let huboNuevos = false;
        invitados.forEach(inv => {
            if (invitadosActuales[inv.id] === undefined) { huboNuevos = true; return; }
            const card = grid.querySelector(`.bebida-card[data-id="${inv.id}"] [data-contador]`);
            if (card && Number(card.textContent) !== inv.bebidas) {
                card.textContent = inv.bebidas;
            }
            invitadosActuales[inv.id] = inv.bebidas;
        });
        if (huboNuevos) renderInvitados(invitados);
    }

    let sumando = false;
    async function sumarBebida(id, card) {
        if (sumando) return;
        sumando = true;

        card.classList.add('pulso');
        setTimeout(() => card.classList.remove('pulso'), 400);

        try {
            const fd = new FormData();
            fd.append('id', id);
            const res  = await fetch('api/bebida_sumar.php', { method: 'POST', body: fd });
            const data = await res.json();

            if (data.ok) {
                const contadorEl = card.querySelector('[data-contador]');
                contadorEl.textContent = data.bebidas;
                invitadosActuales[id] = data.bebidas;
                mostrarToast('¡Listo! Llevas ' + data.bebidas);
            } else {
                mostrarToast(data.error || 'Error al registrar');
            }
        } catch (e) {
            mostrarToast('Sin conexión con el servidor');
        } finally {
            sumando = false;
        }
    }

    async function cargarInvitados(esPrimera) {
        try {
            const res  = await fetch('api/invitados.php', { cache: 'no-store' });
            const data = await res.json();
            if (!data.ok) return;
            if (esPrimera) renderInvitados(data.invitados);
            else actualizarContadores(data.invitados);
        } catch (e) {
            if (esPrimera) grid.innerHTML = '<p class="bebidas-cargando">Sin conexión con el servidor.</p>';
        }
    }

    cargarInvitados(true);
    setInterval(() => cargarInvitados(false), 8000);
})();

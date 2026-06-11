// subir.js — flujo: elegir → confirmar → subir → resultado con marca de agua.
(function () {
    'use strict';

    const csrf = document.querySelector('meta[name="csrf"]').content;
    const MAX_MB = 12;

    const vistas = {
        elegir:    document.getElementById('vista-elegir'),
        confirmar: document.getElementById('vista-confirmar'),
        subiendo:  document.getElementById('vista-subiendo'),
        resultado: document.getElementById('vista-resultado'),
    };
    const paso     = document.getElementById('paso');
    const msgError = document.getElementById('msg-error');

    let archivoSeleccionado = null;
    let subiendo = false;

    function mostrar(nombre) {
        Object.entries(vistas).forEach(([k, el]) => { el.hidden = (k !== nombre); });
        paso.textContent = (nombre === 'elegir') ? '1' : '2';
        msgError.textContent = '';
    }

    function elegirArchivo(file) {
        if (!file) return;
        if (!/^image\/(jpeg|png|webp)$/.test(file.type)) {
            msgError.textContent = 'Formato no soportado. Usa JPG, PNG o WebP.';
            return;
        }
        if (file.size > MAX_MB * 1024 * 1024) {
            msgError.textContent = 'La foto pesa más de ' + MAX_MB + ' MB.';
            return;
        }
        archivoSeleccionado = file;
        const url = URL.createObjectURL(file);
        const zona = document.getElementById('zona-confirmar');
        zona.innerHTML = '';
        const img = new Image();
        img.src = url;
        img.alt = 'Tu foto';
        img.onload = () => URL.revokeObjectURL(url);
        zona.appendChild(img);
        mostrar('confirmar');
    }

    // Botones de selección
    const inputCamara  = document.getElementById('input-camara');
    const inputGaleria = document.getElementById('input-galeria');
    document.getElementById('btn-camara').addEventListener('click', () => inputCamara.click());
    document.getElementById('btn-galeria').addEventListener('click', () => inputGaleria.click());
    inputCamara.addEventListener('change',  e => elegirArchivo(e.target.files[0]));
    inputGaleria.addEventListener('change', e => elegirArchivo(e.target.files[0]));

    document.getElementById('btn-otra').addEventListener('click', () => {
        archivoSeleccionado = null;
        inputCamara.value = '';
        inputGaleria.value = '';
        mostrar('elegir');
    });
    document.getElementById('btn-otra-mas').addEventListener('click', () => {
        archivoSeleccionado = null;
        inputCamara.value = '';
        inputGaleria.value = '';
        mostrar('elegir');
    });

    // Confirmar y subir
    document.getElementById('btn-confirmar').addEventListener('click', async () => {
        if (!archivoSeleccionado || subiendo) return;
        subiendo = true;
        mostrar('subiendo');

        const fd = new FormData();
        fd.append('photo', archivoSeleccionado);

        try {
            const res = await fetch('api/upload.php', {
                method: 'POST',
                body: fd,
                headers: { 'X-CSRF-Token': csrf },
            });
            const data = await res.json();

            if (!data.ok) {
                mostrar('confirmar');
                msgError.textContent = data.error || 'Algo salió mal. Intenta de nuevo.';
                return;
            }

            const zona = document.getElementById('zona-resultado');
            zona.innerHTML = '';
            const img = new Image();
            img.src = data.url + '?v=' + Date.now();
            img.alt = 'Tu foto con marca de agua';
            zona.appendChild(img);
            archivoSeleccionado = null;
            mostrar('resultado');
        } catch (err) {
            mostrar('confirmar');
            msgError.textContent = 'Sin conexión con el servidor. Revisa tu señal e intenta otra vez.';
        } finally {
            subiendo = false;
        }
    });
})();

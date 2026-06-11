# 🌊⚡ FLASHDROP

**La app de fotos instantáneas para tu fiesta**

Repo: AlejoFest Vol.21 (transformado a FLASHDROP)

---

## 📱 ¿Qué es FLASHDROP?

Una app web Y2K donde tus invitados:
- 📸 Toman o suben una foto desde su celular
- ✨ Se estampa automáticamente con la marca de FLASHDROP
- 🖼️ Aparece al instante en la galería pública
- 📺 Sale en la pantalla grande con notificación MSN Messenger

Todo en tiempo real, cola FIFO (primero que entra, primero que sale).

---

## 🚀 Setup rápido

**Lee esto primero:** `QUICK_START_CI_CD.md`

En resumen:
1. Genera SSH key
2. Ejecuta `setup-server.sh` en el Droplet
3. Configura 6 variables en GitLab
4. Push y deploy automático

---

## 🎨 Customización (TODO ES TUYO)

- **Logo:** reemplaza `assets/logo.png`
- **Personaje:** reemplaza `assets/monito.png`
- **Colores:** edita `assets/css/y2k.css` (paleta azul/cromo)
- **Animación:** reemplaza `video/loop.mp4`
- **Marca de agua:** mismo logo.png o crea uno custom en `config.php`

---

## 📚 Archivos importantes

```
QUICK_START_CI_CD.md      ← Comienza aquí (5 minutos)
CI_CD_DEPLOY.md           ← Explicación completa
GITLAB_VARIABLES.md       ← Variables de GitLab
INSTALACION_MANUAL.md     ← Setup manual (sin scripts)
```

---

## ✨ Features

- ✅ Tomar/subir foto con preview
- ✅ Marca de agua automática con GD
- ✅ Galería responsiva con lightbox
- ✅ Pantalla grande con video en loop + notificaciones MSN
- ✅ Panel admin (ocultar, restaurar, reproducir fotos)
- ✅ Cola FIFO estricta (una foto a la vez)
- ✅ Deploy automatizado con GitLab CI/CD
- ✅ MySQL + Nginx + PHP 8.3
- ✅ Rate limiting + CSRF + headers de seguridad

---

## 🔒 Seguridad lista para producción

- SQL injection: PDO + prepared statements
- Re-codificación de fotos (elimina EXIF/payloads)
- Uploads no ejecutan PHP
- CSRF token en todo POST
- Sesiones HttpOnly/SameSite/Secure
- Throttling de login
- XSS mitigation con CSP
- Rate limit de subidas

---

## 📞 Necesitas ayuda?

Revisa:
- **Errores de deploy:** CI_CD_DEPLOY.md → Troubleshooting
- **Setup manual:** INSTALACION_MANUAL.md
- **Variables GitLab:** GITLAB_VARIABLES.md

---

**¡Listo para llevarte las fotos de tu fiesta al siguiente nivel!** 📸✨

**Ir a:** QUICK_START_CI_CD.md

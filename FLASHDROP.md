# 🌊⚡ FLASHDROP

**La app de fotos instantáneas para tu fiesta**

---

## 📱 ¿Qué es FLASHDROP?

Una app web donde tus invitados:
- 📸 Toman una foto desde su celular
- ✨ Se estampa automáticamente con la marca de FLASHDROP
- 🖼️ Aparece al instante en la galería pública
- 📺 Sale en la pantalla grande con notificación MSN Messenger

Todo en tiempo real, cola FIFO (primero que entra, primero que sale).


## 🎨 Customización (TODO ES TUYO)

- **Logo:** reemplaza `assets/logo.png`
- **Personaje:** reemplaza `assets/monito.png`
- **Colores:** edita `assets/css/y2k.css` (paleta azul/cromo)
- **Animación:** reemplaza `video/loop.mp4`
- **Marca de agua:** mismo logo.png o crea uno custom en `config.php`


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
 - https://www.instagram.com/imalejovar/

---

**¡Listo para llevarte las fotos de tu fiesta al siguiente nivel!** 📸✨



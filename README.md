# 🌟 FLASHDROP — App de fotos Y2K

App web responsiva (mobile-first) para la fiesta: la gente toma o sube su foto, se le
estampa la marca de agua del AlejoFest automáticamente, entra a la galería pública y
sale en la pantalla grande con una notificación estilo MSN Messenger, en cola FIFO.

## Estructura

```
flashdrop/
├── index.php            Menú principal
├── subir.php            Tomar/subir foto → preview → confirmar (sin borrar)
├── galeria.php          Galería pública responsiva con lightbox
├── pantalla.php         Pantalla grande: video en loop + notificaciones MSN
├── admin/
│   ├── login.php        Acceso admin (con límite de intentos por IP)
│   ├── dashboard.php    Ocultar/restaurar fotos + reproducir en pantalla
│   └── logout.php
├── api/
│   ├── upload.php       Valida, re-codifica, marca de agua, encola FIFO
│   ├── photos.php       Lista de fotos visibles (paginada)
│   ├── feed.php         Cola FIFO para la pantalla
│   └── admin_action.php Acciones del admin (hide/restore/replay)
├── inc/                 Helpers, sesión, CSRF, auth (bloqueado por .htaccess)
├── assets/              ⬅ AQUÍ VAN TUS PNG (no se vectorizan, van tal cual)
│   ├── logo.png         Logo FLASHDROP (se usa también como marca de agua)
│   ├── monito.png       El personaje
│   └── marco.png        (opcional, decorativo)
├── video/loop.mp4       ⬅ AQUÍ VA TU ANIMACIÓN EN LOOP (H.264)
├── uploads/             Fotos ya con marca de agua (PHP deshabilitado aquí)
├── tools/crear_admin.php  Crear admin (solo CLI)
├── config.example.php   Copia → config.php y pon tus credenciales
└── db.sql               Esquema MySQL
```

## Instalación

1. **Base de datos**
   ```bash
   mysql -u root -p < db.sql
   ```
   Crea un usuario MySQL solo para la app (no uses root):
   ```sql
   CREATE USER 'flashdrop_app'@'localhost' IDENTIFIED BY 'una_contraseña_fuerte';
   GRANT SELECT, INSERT, UPDATE ON flashdrop.* TO 'flashdrop_app'@'localhost';
   FLUSH PRIVILEGES;
   ```

2. **Config**
   ```bash
   cp config.example.php config.php
   # edita config.php con tus credenciales
   ```

3. **Assets**
   - Copia tus PNG a `assets/` con los nombres `logo.png`, `monito.png`, `marco.png`.
   - Copia tu animación a `video/loop.mp4`.
   - Si tu PNG de marca de agua es distinto al logo, cambia `WATERMARK_PATH` en `config.php`.

4. **Permisos**
   ```bash
   chown -R www-data:www-data uploads/
   chmod 755 uploads/
   ```

5. **Admin**
   ```bash
   php tools/crear_admin.php alejo TuContraseñaSegura123
   ```

6. **Requisitos PHP**: 8.0+, extensiones `gd` (con soporte JPEG/PNG/WebP), `pdo_mysql`,
   `fileinfo` y de preferencia `exif` (para rotar fotos de celular).
   ```bash
   sudo apt install php-gd php-mysql php-exif   # Debian/Ubuntu
   ```

## Cómo funciona la pantalla grande

1. Abre `pantalla.php` en la compu conectada al proyector/TV y pulsa **Iniciar pantalla**
   (entra en fullscreen y arranca el video en loop — el video **nunca** se detiene).
2. Cada foto nueva entra a la cola **FIFO** (primera que entra, primera que sale, una a la vez):
   - **Toast MSN** abajo a la derecha con mini preview → 5 s
   - **Ventana destacada** arriba a la derecha, dimensionada según si la foto es
     vertical u horizontal → 8–10 s
   - Desaparece suavemente y pasa a la siguiente.
3. Desde el dashboard puedes volver a encolar cualquier foto con **📺 Reproducir en pantalla**.

Tiempos ajustables en `config.php` (`TOAST_SECONDS`, `FEATURE_SECONDS`, `FEED_POLL_SECONDS`).

## Seguridad incluida

- **SQL injection**: todo con PDO + prepared statements, `EMULATE_PREPARES` desactivado.
- **Subidas**: MIME real con `finfo` (no extensión), límite de tamaño, la imagen se
  **re-codifica a JPEG limpio** (elimina EXIF/GPS y cualquier payload incrustado),
  nombre de archivo aleatorio de 32 hex.
- **`uploads/` no ejecuta PHP** (`.htaccess` con engine off + handlers removidos).
- **CSRF**: token de sesión obligatorio en subidas y acciones de admin.
- **Sesiones**: cookies `HttpOnly` + `SameSite=Lax` + `Secure` bajo HTTPS,
  `session_regenerate_id` al iniciar sesión (anti session fixation), `use_strict_mode`.
- **Login admin**: `password_hash`/`password_verify` + throttling (6 intentos / 10 min por IP).
- **Rate limit de subidas**: 4 fotos por minuto por IP.
- **XSS**: salida escapada con `htmlspecialchars` + CSP (`script-src 'self'`, sin JS inline).
- **Headers**: `nosniff`, `X-Frame-Options`, `Referrer-Policy`, `Permissions-Policy`.
- **Archivos sensibles bloqueados**: `config.php`, `db.sql`, `inc/`, `tools/` no son accesibles vía web.
- Las fotos "borradas" por el admin solo se **ocultan** (`visible = 0`): el archivo se
  conserva y se puede restaurar con un clic.

## Checklist para producción

- [ ] Sirve TODO bajo **HTTPS** (Let's Encrypt/certbot) — la cookie `Secure` se activa sola.
- [ ] `display_errors = Off` en `php.ini` de producción.
- [ ] Usuario MySQL dedicado sin `DROP/ALTER` (como en el paso 1).
- [ ] Si usas Nginx en vez de Apache, replica las reglas de `.htaccess`:
  bloquear `config.php`, `db.sql`, `inc/`, `tools/` y `location ~ \.php$ { return 403; }`
  dentro de `/uploads`.
- [ ] Respaldo del folder `uploads/` y dump de la BD después de la fiesta. 📦

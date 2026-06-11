# Variables de GitLab CI/CD para FLASHDROP

Ir a: **Settings → CI/CD → Variables** en tu repositorio de GitLab

## 1. Variables de DEPLOY (obligatorias)

### `DEPLOY_SSH_KEY`
**Tipo:** Variable  
**Protegida:** ✅ Sí  
**Máscara:** ✅ Sí (no mostrar en logs)  

Tu clave SSH **privada** (generada en la máquina local):
```bash
cat ~/.ssh/id_rsa
```

Cópiala completa (incluye `-----BEGIN OPENSSH PRIVATE KEY-----` y `-----END...`).

En GitLab:
```
-----BEGIN OPENSSH PRIVATE KEY-----
b3BlbnNzaC1rZXktdjEAAAAABG5vbmUtbm9uZS1ub25lAAAAAAAAADEAAAAQRUND...
... (múltiples líneas) ...
-----END OPENSSH PRIVATE KEY-----
```

### `DEPLOY_HOST`
**Tipo:** Variable  
**Protegida:** ✅ Sí  

IP pública de tu Droplet:
```
143.244.165.130
```

O tu dominio si ya lo configuraste:
```
flashdrop.ejemplo.com
```

### `DEPLOY_USER`
**Tipo:** Variable  
**Protegida:** ✅ Sí  

Usuario creado en el servidor (default: `deploy`):
```
deploy
```

---

## 2. Variables de BASE DE DATOS (obligatorias)

Se usan en `config.php` durante el deploy.

### `DB_HOST`
```
127.0.0.1
```
(o la IP/dominio del servidor MySQL si está en otra máquina)

### `DB_NAME`
```
flashdrop
```

### `DB_USER`
```
flashdrop_app
```

### `DB_PASS`
**Protegida:** ✅ Sí  
**Máscara:** ✅ Sí  

La contraseña que obtuviste al correr `setup-server.sh`:
```
xxxxxxxxxxxxxxxx  (16 caracteres aleatorios)
```

---

## 3. Variables de APLICACIÓN (opcionales, pero recomendadas)

### `MAX_UPLOAD_MB`
```
12
```
(tamaño máximo de foto en MB)

### `WATERMARK_SCALE`
```
0.42
```
(ancho de la marca como porcentaje del ancho de la foto)

### `FEED_POLL_SECONDS`
```
3
```
(cada cuánto segundos la pantalla grande consulta por fotos nuevas)

### `TOAST_SECONDS`
```
5
```
(duración de la notificación MSN)

### `FEATURE_SECONDS`
```
9
```
(duración de la foto destacada)

---

## 4. Cómo generar la SSH key (si no la tienes)

```bash
# En tu máquina local
ssh-keygen -t ed25519 -C "flashdrop-deploy"
# Presiona Enter para usar la ubicación default (~/.ssh/id_ed25519)
# Presiona Enter para no poner passphrase (o pon una si prefieres)

# Ver la clave privada:
cat ~/.ssh/id_ed25519

# Ver la clave pública (para autorizar en el servidor):
cat ~/.ssh/id_ed25519.pub
```

---

## 5. Script de setup del servidor

En el Droplet, como root:

```bash
# Descarga el repo
git clone https://gitlab.com/tu-usuario/flashdrop.git /tmp/flashdrop-setup
cd /tmp/flashdrop-setup

# Ejecuta el setup
bash scripts/setup-server.sh

# Guarda la contraseña de MySQL que se muestra
```

Esto crea:
- Usuario `deploy`
- Directorios `/var/www/flashdrop`
- Nginx + PHP-FPM configurados
- MySQL con BD `flashdrop` y usuario `flashdrop_app`
- SSH key y sudoers para el deploy

---

## 6. Flujo del deploy

1. **Push a GitLab:** `git push origin FlashDrop`

2. **GitLab CI/CD dispara:**
   - ✅ `validate_php` — lint de PHP
   - ✅ `validate_config` — verifica que existan archivos críticos
   - 📦 `build_artifacts` — genera ZIP
   - 🚀 **`deploy_production` (manual)** — click en el botón en la UI

3. **Deploy manual ejecuta:**
   ```bash
   ssh -i ~/.ssh/deploy_key deploy@143.244.165.130 << 'REMOTE_SCRIPT'
     cd /var/www/flashdrop
     git pull origin FlashDrop
     chmod -R 775 uploads/ video/
     sudo systemctl reload php8.3-fpm
     sudo systemctl reload nginx
   REMOTE_SCRIPT
   ```

4. **En el servidor:**
   - Descarga el código
   - Configura permisos
   - Recarga servicios
   - Health check automático

---

## 7. Solución de problemas

### "Permission denied (publickey)"
- Verifica que `DEPLOY_SSH_KEY` sea la clave **privada** (no pública)
- Verifica que la clave pública esté en `/home/deploy/.ssh/authorized_keys` del servidor

### "Connection refused" o "ssh: Could not resolve hostname"
- Verifica que `DEPLOY_HOST` sea la IP correcta (143.244.165.130)
- Verifica que el firewall permita SSH (puerto 22)

### "git: command not found"
- En el servidor, Git no está instalado
- Vuelve a correr `setup-server.sh` como root

### "PHP Fatal error in upload.php"
- Verifica que las variables de BD (`DB_HOST`, `DB_PASS`, etc.) estén correctas
- Verifica que MySQL esté corriendo: `systemctl status mysql`
- Verifica que la BD se haya importado: `mysql -u flashdrop_app -p flashdrop -e "SELECT COUNT(*) FROM photos;"`

---

## 8. Deploy MANUAL (si GitLab no funciona)

En el Droplet:
```bash
ssh deploy@143.244.165.130

cd /var/www/flashdrop
git pull origin FlashDrop
chmod -R 775 uploads/ video/
sudo systemctl reload php8.3-fpm nginx

# Ver logs si hay error:
tail -f /var/log/nginx/flashdrop-error.log
```

---

## 9. Rollback (si algo sale mal)

```bash
ssh deploy@143.244.165.130

cd /var/www/flashdrop
git log --oneline       # ver commits
git checkout <commit>   # volver a commit anterior
git pull                # o actualizar

sudo systemctl reload nginx php8.3-fpm
```

---

## 10. Ver logs de deploy

En GitLab: **Pipelines** → selecciona el pipeline → click en ✅/**❌** de deploy.

En el servidor:
```bash
tail -100 /var/log/flashdrop-deploy.log
tail -50 /var/log/nginx/flashdrop-error.log
sudo journalctl -u php8.3-fpm -n 50
```

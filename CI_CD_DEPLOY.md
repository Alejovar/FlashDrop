# 🚀 Guía CI/CD: Deploy automatizado de FLASHDROP

---

## 📋 Contenido

Este directorio contiene todo lo necesario para hacer deploy automático a DigitalOcean con GitLab CI/CD.

```
.
├── .gitlab-ci.yml              ← Pipeline de GitLab (stages: validate, build, deploy)
├── GITLAB_VARIABLES.md          ← Variables secretas necesarias en GitLab
├── INSTALACION_MANUAL.md        ← Setup paso a paso sin scripts
├── scripts/
│   ├── setup-server.sh          ← Setup inicial del Droplet (ejecutar como root)
│   └── deploy.sh                ← Deploy en el servidor (ejecutado por CI/CD)
├── db.sql                       ← Esquema MySQL
└── config.example.php           ← Template de configuración (copia → config.php)
```

---

## 🔧 Setup rápido (recomendado)

### 1️⃣ En tu máquina local: Genera SSH key

```bash
ssh-keygen -t ed25519 -C "flashdrop-deploy"
# Presiona Enter × 3
cat ~/.ssh/id_ed25519
# Copia el contenido (es tu DEPLOY_SSH_KEY)
```

### 2️⃣ En DigitalOcean: Setup del Droplet

```bash
ssh root@143.244.165.130

# Descarga el repo
git clone https://gitlab.com/tu-usuario/flashdrop.git /tmp/setup
bash /tmp/setup/scripts/setup-server.sh

# Guarda la contraseña de MySQL que aparece
```

### 3️⃣ En GitLab: Configura variables

Ve a **Settings → CI/CD → Variables** y agrega:

| Variable | Valor | Protegida |
|----------|-------|-----------|
| `DEPLOY_SSH_KEY` | Tu clave privada (~/.ssh/id_ed25519) | ✅ |
| `DEPLOY_HOST` | 143.244.165.130 | ✅ |
| `DEPLOY_USER` | deploy | ✅ |
| `DB_HOST` | 127.0.0.1 | ✅ |
| `DB_NAME` | flashdrop | ✅ |
| `DB_USER` | flashdrop_app | ✅ |
| `DB_PASS` | (contraseña de MySQL) | ✅ |

### 4️⃣ En tu repositorio local

```bash
cp config.example.php config.php
# Edita config.php con los valores reales
git add .
git commit -m "Initial commit"
git push origin FlashDrop
```

### 5️⃣ En GitLab: Deploy

- Ve a **Pipelines**
- Espera a que `validate_php` y `build_artifacts` pasen ✅
- Haz click en el botón **manual** de `deploy_production` 🚀
- Espera a que finalice (logs en verde)

---

## 🏗️ Flujo del pipeline

```
Push a GitLab
    ↓
validate_php ............................ Lint de sintaxis PHP
    ↓
validate_config ......................... Verifica archivos críticos
    ↓
build_artifacts ......................... Genera ZIP del proyecto
    ↓
deploy_production (MANUAL REQUERIDO) ... Ejecuta:
    • git pull origin FlashDrop
    • chmod permisos
    • reload nginx + php-fpm
    • health check
```

---

## 📝 Checklist

- [ ] Droplet en DigitalOcean creado (143.244.165.130)
- [ ] SSH key generada en máquina local (`~/.ssh/id_ed25519`)
- [ ] `setup-server.sh` ejecutado en el Droplet como root
- [ ] Contraseña de MySQL guardada
- [ ] Variables de GitLab configuradas (6 variables)
- [ ] `config.php` actualizado localmente
- [ ] Código pusheado a GitLab
- [ ] Pipeline ejecutado manualmente
- [ ] Admin creado: `php tools/crear_admin.php alejo Contraseña123`
- [ ] Dominio apuntando a 143.244.165.130 (opcional)
- [ ] SSL configurado con certbot (opcional pero recomendado)

---

## ⚙️ Detalles técnicos

### `.gitlab-ci.yml`

Define 3 stages:

1. **validate** — checks estáticos (lint PHP, archivos)
2. **build** — prepara artefactos (ZIP)
3. **deploy** — ejecuta en el servidor vía SSH

El deploy:
- Lee `DEPLOY_SSH_KEY`, `DEPLOY_HOST`, `DEPLOY_USER` de GitLab
- Crea conexión SSH
- Ejecuta `git pull origin FlashDrop`
- Configura permisos
- Recarga servicios

### Seguridad

- SSH key privada (**nunca** en git, solo en GitLab variables)
- Contraseña de BD enmascarada en logs
- `config.php` NO entra en git (excluir con `.gitignore`)
- Deploy requiere click manual (no automático)
- Solo rama `main` puede deployar (proteger en Settings → Protected branches)

### Scripts

**`setup-server.sh`** (una sola vez):
- Instala Nginx, PHP 8.3, MySQL, Git
- Crea usuario `deploy`
- Configura sudoers (para que `deploy` pueda recargar servicios)
- Genera BD y usuario de aplicación
- Habilita firewall

**`deploy.sh`** (cada deploy):
- Pull del código
- Configurar permisos
- Validar estructura
- Recargar servicios
- Health check

---

## 🐛 Troubleshooting

### "Permission denied (publickey)"

**Causa:** SSH key incorrecta.

**Solución:**
```bash
# Verifica que sea la privada, no la pública
cat ~/.ssh/id_ed25519 | head -1
# Debe empezar con: -----BEGIN OPENSSH PRIVATE KEY-----

# NO hagas esto (es la pública):
cat ~/.ssh/id_ed25519.pub
```

### "mysql: command not found" en el servidor

**Causa:** MySQL no instalado.

**Solución:**
```bash
apt-get install -y mysql-server
systemctl start mysql
```

### "Connection refused" desde GitLab

**Causa:** Firewall bloquea puerto 22.

**Solución:**
```bash
ufw allow 22/tcp
ufw reload
```

### "PHP Fatal error" en el sitio

**Causa:** `config.php` inválido o credenciales incorrectas.

**Solución:**
```bash
ssh deploy@143.244.165.130
cd /var/www/flashdrop
php -l config.php    # verifica sintaxis
tail -20 /var/log/nginx/flashdrop-error.log
mysql -u flashdrop_app -p flashdrop -e "SELECT COUNT(*) FROM photos;"
```

---

## 🔐 Cambiar contraseña de admin

```bash
ssh deploy@143.244.165.130
cd /var/www/flashdrop
php tools/crear_admin.php alejo NUEVA_CONTRASEÑA
```

---

## 🔄 Rollback (volver atrás)

```bash
ssh deploy@143.244.165.130
cd /var/www/flashdrop
git log --oneline   # ve qué commits existen
git checkout <hash>
sudo systemctl reload nginx php8.3-fpm
```

O directamente desde GitLab: vuelve a ejecutar el deploy en un commit anterior.

---

## 📊 Logs

En GitLab:
- **Pipelines** → click en el hash/ID → ver output

En el servidor:
```bash
# Nginx
tail -50 /var/log/nginx/flashdrop-error.log

# PHP-FPM
sudo journalctl -u php8.3-fpm -n 50

# Deploy (si existe)
tail -100 /var/log/flashdrop-deploy.log

# MySQL
sudo tail -50 /var/log/mysql/error.log
```

---

## 🚀 Deploy MANUAL (si CI/CD no funciona)

```bash
ssh deploy@143.244.165.130

cd /var/www/flashdrop

# Pull del código
git pull origin FlashDrop

# Permisos
chmod -R 755 .
chmod -R 775 uploads/ video/
chmod 600 config.php

# Recargar servicios
sudo systemctl reload php8.3-fpm
sudo systemctl reload nginx

# Verificar
curl -I http://localhost/index.php
```

---

## 📈 Escalamiento (opcional)

Si en el futuro necesitas:
- **Base de datos en servidor aparte:** cambiar `DB_HOST` en `config.php`
- **CDN para imágenes:** apuntar `UPLOADS_URL` a CDN
- **Cache HTTP:** agregar headers de cache en Nginx
- **Monitoreo:** instalar Prometheus, Grafana, etc.

---

## ✅ Verificación final

Una vez deployado:

1. **¿Puedo entrar a la página?**
   ```
   http://143.244.165.130/index.php
   ```

2. **¿Puedo subir una foto?**
   - Ir a `/subir.php`
   - Subir una foto
   - Debe aparecer en `/galeria.php` con marca de agua

3. **¿Panel admin funciona?**
   - Ir a `/admin/login.php`
   - Entrar con usuario/contraseña que creaste
   - Ver fotos, poder ocultarlas y reproducirlas en pantalla

4. **¿Pantalla grande funciona?**
   - Abrir `/pantalla.php` en otra ventana
   - Hacer click en "Iniciar pantalla"
   - Subir una foto desde `/subir.php` en otra pestaña
   - Debe aparecer la notificación MSN en la pantalla grande

---

## 🎓 Apuntes finales

- **Protege tu rama main:** Settings → Repository → Protected branches → Allow only maintainers
- **Revisa logs regularmente:** para detectar errores temprano
- **Respaldo de fotos:** `uploads/` debe incluirse en backups diarios
- **SSL es obligatorio:** certbot toma 2 minutos: `certbot --nginx -d tu-dominio.com`

¡Listo! 🎉

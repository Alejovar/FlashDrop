# 🚀 FLASHDROP — Setup CI/CD en DigitalOcean (Guía rápida)

Tu Droplet está listo en: **143.244.165.130**

---

## ⏱️ Resumen 5 minutos

1. **Genera SSH key** (máquina local)
2. **Ejecuta setup-server.sh** (Droplet, como root)
3. **Configura variables de GitLab** (6 variables)
4. **Push a GitLab** + click en botón deploy
5. **¡Listo! El sitio está en vivo**

---

## 📥 Descarga los archivos

El ZIP incluye:

```
flashdrop/
├── .gitlab-ci.yml               ← Pipeline automático
├── CI_CD_DEPLOY.md              ← Guía completa (⭐ lee esto)
├── GITLAB_VARIABLES.md          ← Variables necesarias
├── INSTALACION_MANUAL.md        ← Si prefieres hacer todo manual
├── scripts/
│   ├── setup-server.sh          ← Ejecutar en Droplet (1x)
│   └── deploy.sh                ← Se ejecuta automáticamente
├── db.sql                       ← Esquema MySQL
└── config.example.php           ← Copia → config.php
```

---

## 🔧 Paso 1: SSH Key (tu máquina)

```bash
ssh-keygen -t ed25519 -C "flashdrop-deploy"
# Presiona Enter 3 veces

# Ver la clave privada (la necesitarás en GitLab)
cat ~/.ssh/id_ed25519
```

**Guarda todo en un archivo de texto.** Lo usarás en el paso 3.

---

## 🖥️ Paso 2: Setup del Droplet

Conéctate al Droplet como **root**:

```bash
ssh root@143.244.165.130
# Pide contraseña, introduce la que recibiste en DigitalOcean

# Descarga el setup
cd /tmp
git clone https://gitlab.com/tu-usuario/flashdrop.git flashdrop-setup
cd flashdrop-setup

# Ejecuta el script (toma ~5 minutos)
bash scripts/setup-server.sh
```

**Al final, guarda estos datos:**
```
Usuario de deploy: deploy
Contraseña MySQL: xxxxxxxxxxxxxxxx (generada)
```

---

## 🏗️ Paso 3: Variables de GitLab

En tu navegador, ve a:

```
https://gitlab.com/tu-usuario/flashdrop
  → Settings
  → CI/CD
  → Variables
```

Agrega estas **6 variables** (todas deben estar **Protegidas** ✅):

| Variable | Valor |
|----------|-------|
| `DEPLOY_SSH_KEY` | Pega tu clave privada de ~/.ssh/id_ed25519 |
| `DEPLOY_HOST` | 143.244.165.130 |
| `DEPLOY_USER` | deploy |
| `DB_HOST` | 127.0.0.1 |
| `DB_NAME` | flashdrop |
| `DB_USER` | flashdrop_app |
| `DB_PASS` | Contraseña que generó setup-server.sh |

**⚠️ IMPORTANTE:** marca todas como **Protegidas** (checkbox `🔒 Protect variable`).

---

## 💻 Paso 4: Configuración local

En tu repo local:

```bash
# Copiar config
cp config.example.php config.php

# Editar con tus valores
nano config.php
```

Cambia estos valores:
```php
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'flashdrop');
define('DB_USER', 'flashdrop_app');
define('DB_PASS', 'la_contraseña_que_guardaste');
```

Guarda con **Ctrl+O** → Enter → **Ctrl+X**.

---

## 🚀 Paso 5: Push a GitLab

```bash
git add config.php
git commit -m "Add config for production"
git push origin FlashDrop
```

Automáticamente se dispara el pipeline en GitLab:
- ✅ `validate_php` (verifica sintaxis)
- ✅ `build_artifacts` (genera ZIP)
- 🔘 `deploy_production` (espera tu click)

---

## 🎯 Paso 6: Deploy

En GitLab:

```
Pipelines → selecciona el último → busca "deploy_production"
    → haz click en el botón azul "manual" o en el ▶
```

Espera a que termine (verde = éxito ✅).

---

## ✨ Paso 7: Crear admin y verificar

En el Droplet:

```bash
ssh deploy@143.244.165.130

cd /var/www/flashdrop
php tools/crear_admin.php alejo MiContraseña123
```

Ahora abre en el navegador:

```
http://143.244.165.130
```

Deberías ver el menú principal de AlejoFest 🎉

---

## ✅ Checklist de verificación

- [ ] Puedo ver `http://143.244.165.130/index.php` (menú principal)
- [ ] Puedo subir una foto en `/subir.php`
- [ ] La foto aparece en `/galeria.php` con marca de agua
- [ ] Puedo entrar a `/admin/login.php` con el admin que creé
- [ ] Desde el admin puedo ver las fotos y ocultarlas
- [ ] La pantalla grande funciona en `/pantalla.php` (botón "Iniciar")

Si algo no funciona, revisar **Logs** en el paso de troubleshooting.

---

## 🌐 SSL (HTTPS) — ⭐ RECOMENDADO

Una vez que todo funciona:

```bash
# Necesitas un dominio apuntando a 143.244.165.130
# Luego, en el Droplet:
apt-get install -y certbot python3-certbot-nginx
certbot --nginx -d tu-dominio.com
```

Nginx se reconfigura automáticamente para HTTPS.

---

## 🐛 Troubleshooting rápido

### "Permission denied (publickey)"
- Verifica que la SSH key sea la **privada** (no la que termina en .pub)
- Verifica que esté completa en GitLab (incluir BEGIN y END)

### "Can't connect to MySQL"
- El servidor `127.0.0.1` está corriendo: `ssh deploy@143.244.165.130 && mysql -u flashdrop_app -p -e "SELECT 1;"`
- La contraseña en `config.php` es correcta

### "HTTP 404 o 500"
- Revisa logs de Nginx: `ssh deploy@... && tail -50 /var/log/nginx/flashdrop-error.log`
- Revisa logs de PHP: `ssh deploy@... && sudo journalctl -u php8.3-fpm -n 50`

---

## 📊 Ver logs del deploy

En GitLab → Pipelines → click en el pipeline → ver output de `deploy_production`.

En el Droplet:
```bash
ssh deploy@143.244.165.130
tail -100 /var/log/flashdrop-deploy.log
```

---

## 🔄 Hacer deploy nuevamente (después de cambios)

```bash
# En tu máquina local
git add .
git commit -m "Descripción del cambio"
git push origin FlashDrop

# En GitLab
# → Pipelines → botón deploy de "deploy_production"
```

---

## 📚 Documentación completa

Lee estos archivos para entender mejor:

1. **CI_CD_DEPLOY.md** — Explicación completa del pipeline
2. **GITLAB_VARIABLES.md** — Detalle de cada variable
3. **INSTALACION_MANUAL.md** — Setup paso a paso sin scripts

---

## 🎓 Apuntes finales

- Protege la rama `main`: Settings → Repository → Protected Branches
- Haz backup regularmente de `/uploads` (las fotos)
- Los logs son tu amigo: revisar cuando algo falla
- Si necesitas revertir: `git checkout <commit_anterior>` y vuelve a deployar

---

## 🚨 Emergencia: volver atrás

```bash
ssh deploy@143.244.165.130
cd /var/www/flashdrop
git log --oneline         # lista commits
git checkout <hash>       # vuelve al que quieras
sudo systemctl reload nginx php8.3-fpm
```

O desde GitLab: redeploy en un commit anterior.

---

¡Listo! Tu app está en vivo y con deploy automatizado. 🎉

Si algo no funciona, revisa **CI_CD_DEPLOY.md** → apartado **Troubleshooting**.

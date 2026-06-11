# Instalación Manual de FLASHDROP en DigitalOcean

Si prefieres hacer todo manualmente o el script `setup-server.sh` falla.

---

## Paso 0: Acceder al Droplet

```bash
ssh root@143.244.165.130
# O si tienes contraseña, la solicita aquí
```

---

## Paso 1: Actualizar el sistema

```bash
apt-get update
apt-get upgrade -y
```

---

## Paso 2: Instalar Nginx

```bash
apt-get install -y nginx
systemctl enable nginx
systemctl start nginx
```

Verificar: abre `http://143.244.165.130` en el navegador (debe mostrar "Welcome to nginx").

---

## Paso 3: Instalar PHP 8.3 + FPM

```bash
apt-get install -y php8.3-fpm php8.3-cli php8.3-gd php8.3-mysql php8.3-exif php8.3-xml php8.3-curl php8.3-zip php8.3-mbstring
```

Verificar:
```bash
php -v
php -m | grep -E "gd|mysql|exif"
```

---

## Paso 4: Instalar MySQL 8.0

```bash
apt-get install -y mysql-server
systemctl enable mysql
systemctl start mysql
```

Verificar:
```bash
mysql -u root -e "SELECT VERSION();"
```

---

## Paso 5: Crear usuario y BD de MySQL

```bash
# Generar contraseña aleatoria
PASS=$(openssl rand -base64 16)
echo "Contraseña para flashdrop_app: $PASS"

# Ejecutar SQL
mysql -u root << EOF
CREATE DATABASE flashdrop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'flashdrop_app'@'localhost' IDENTIFIED BY '$PASS';
GRANT SELECT, INSERT, UPDATE ON flashdrop.* TO 'flashdrop_app'@'localhost';
FLUSH PRIVILEGES;
EOF
```

Guarda esa contraseña (la necesitarás en config.php y en GitLab).

---

## Paso 6: Crear usuario de deploy

```bash
useradd -m -s /bin/bash -G www-data deploy
```

---

## Paso 7: Configurar directorios del proyecto

```bash
mkdir -p /var/www/flashdrop
chown -R deploy:www-data /var/www/flashdrop
chmod 755 /var/www/flashdrop
```

---

## Paso 8: Configurar Nginx

Crear archivo `/etc/nginx/sites-available/flashdrop`:

```bash
cat > /etc/nginx/sites-available/flashdrop << 'EOF'
server {
    listen 80;
    server_name 143.244.165.130;

    root /var/www/flashdrop;
    index index.php;

    access_log /var/log/nginx/flashdrop-access.log;
    error_log /var/log/nginx/flashdrop-error.log;

    # No servir archivos ocultos
    location ~ /\. {
        deny all;
    }

    # PHP
    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # No ejecutar PHP en uploads
    location ~ ^/uploads/.*\.php$ {
        deny all;
    }

    location /uploads {
        expires 30d;
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
}
EOF
```

Activar:
```bash
ln -sf /etc/nginx/sites-available/flashdrop /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default
```

Validar y recargar:
```bash
nginx -t
systemctl reload nginx
```

---

## Paso 9: Configurar PHP

```bash
# Tamaño máximo de subida
sed -i 's/^upload_max_filesize.*/upload_max_filesize = 100M/' /etc/php/8.3/fpm/php.ini
sed -i 's/^post_max_size.*/post_max_size = 100M/' /etc/php/8.3/fpm/php.ini

# Desactivar errores en producción
sed -i 's/^display_errors.*/display_errors = Off/' /etc/php/8.3/fpm/php.ini

# Recargar
systemctl reload php8.3-fpm
```

---

## Paso 10: Clonar repositorio (como usuario deploy)

```bash
sudo -u deploy git clone https://gitlab.com/tu-usuario/flashdrop.git /var/www/flashdrop
cd /var/www/flashdrop
```

Crear `config.php` basado en `config.example.php`:

```bash
cp config.example.php config.php
```

Editar `config.php` con las credenciales reales:

```bash
nano config.php
```

```php
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'flashdrop');
define('DB_USER', 'flashdrop_app');
define('DB_PASS', 'la_contraseña_que_generaste');
```

---

## Paso 11: Importar esquema SQL

```bash
cd /var/www/flashdrop
mysql -u flashdrop_app -p flashdrop < db.sql
# Pide la contraseña
```

Verificar:
```bash
mysql -u flashdrop_app -p flashdrop -e "SHOW TABLES;"
```

---

## Paso 12: Permisos finales

```bash
cd /var/www/flashdrop
chmod -R 755 .
chmod -R 775 uploads/ video/
chmod 600 config.php
chown -R deploy:www-data .
```

---

## Paso 13: Crear admin

```bash
php tools/crear_admin.php alejo MiContraseñaSegura123
```

---

## Paso 14: SSL con Let's Encrypt (RECOMENDADO)

```bash
apt-get install -y certbot python3-certbot-nginx

# Necesitas un dominio apuntando a 143.244.165.130
# Luego:
certbot --nginx -d tu-dominio.com
```

Nginx se reconfiguración automáticamente para HTTPS.

---

## Paso 15: Firewall

```bash
ufw allow 22
ufw allow 80
ufw allow 443
ufw --force enable
```

---

## Verificación

```bash
# ¿Nginx está corriendo?
systemctl status nginx

# ¿PHP-FPM está corriendo?
systemctl status php8.3-fpm

# ¿MySQL está corriendo?
systemctl status mysql

# ¿Puedo acceder al sitio?
curl -I http://143.244.165.130/index.php
# Debe devolver HTTP 200
```

---

## Logs

Si algo sale mal:

```bash
# Nginx
tail -50 /var/log/nginx/flashdrop-error.log

# PHP
sudo journalctl -u php8.3-fpm -n 50

# MySQL
sudo tail -50 /var/log/mysql/error.log
```

---

## Próximos pasos (para GitLab CI/CD)

Una vez que todo funcione manualmente:

1. Generar SSH key para deploy:
   ```bash
   sudo -u deploy ssh-keygen -t ed25519 -C "flashdrop-deploy" -f /home/deploy/.ssh/id_ed25519 -N ""
   cat /home/deploy/.ssh/id_ed25519
   ```

2. Copiar esa clave privada a GitLab → Settings → CI/CD → Variables → `DEPLOY_SSH_KEY`

3. Configurar las otras variables (DB_PASS, etc.)

4. Push a GitLab y prueba el pipeline.

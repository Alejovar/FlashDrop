#!/bin/bash
# setup-server.sh — Configuración inicial del Droplet de DigitalOcean
# Uso: bash scripts/setup-server.sh
# Ejecutar como root o con sudo

set -e  # exit on error

echo "======================================"
echo "FLASHDROP — Setup del Droplet"
echo "======================================"

# Detectar el usuario actual
DEPLOY_USER="${1:-deploy}"
DEPLOY_PATH="/var/www/flashdrop"

echo "✓ Actualizando paquetes..."
apt-get update && apt-get upgrade -y

# ============================================================
# 1. NGINX + PHP 8.3 + PHP-FPM
# ============================================================
echo "✓ Instalando Nginx + PHP 8.3 + extensiones..."
apt-get install -y \
    nginx \
    php8.3-fpm \
    php8.3-cli \
    php8.3-gd \
    php8.3-mysql \
    php8.3-exif \
    php8.3-xml \
    php8.3-curl \
    php8.3-zip \
    php8.3-mbstring

# ============================================================
# 2. MySQL 8.0
# ============================================================
echo "✓ Instalando MySQL 8.0..."
apt-get install -y mysql-server

# ============================================================
# 3. GIT + SSH
# ============================================================
echo "✓ Instalando Git..."
apt-get install -y git openssh-server openssh-client

# ============================================================
# 4. UTILIDADES
# ============================================================
echo "✓ Instalando utilidades..."
apt-get install -y curl wget zip unzip htop

# ============================================================
# 5. CREAR USUARIO DEPLOY
# ============================================================
echo "✓ Creando usuario de deploy..."
if ! id "$DEPLOY_USER" &>/dev/null; then
    useradd -m -s /bin/bash -G www-data "$DEPLOY_USER"
    echo "  Usuario $DEPLOY_USER creado."
else
    echo "  Usuario $DEPLOY_USER ya existe."
fi

# ============================================================
# 6. SSH KEY PARA GITLAB
# ============================================================
echo "✓ Configurando SSH para GitLab..."
sudo -u $DEPLOY_USER mkdir -p /home/$DEPLOY_USER/.ssh
echo "  📝 Genera una SSH key en GitLab (Settings → SSH Keys) y pega la pública aquí:"
read -p "  Pega la clave SSH pública: " ssh_pub
echo "$ssh_pub" | sudo -u $DEPLOY_USER tee /home/$DEPLOY_USER/.ssh/authorized_keys > /dev/null
chmod 600 /home/$DEPLOY_USER/.ssh/authorized_keys

# ============================================================
# 7. DIRECTORIOS DEL PROYECTO
# ============================================================
echo "✓ Creando estructura de directorios..."
mkdir -p $DEPLOY_PATH
chown -R $DEPLOY_USER:www-data $DEPLOY_PATH
chmod 755 $DEPLOY_PATH
mkdir -p $DEPLOY_PATH/uploads $DEPLOY_PATH/video
chmod 775 $DEPLOY_PATH/uploads $DEPLOY_PATH/video

# ============================================================
# 8. CONFIGURACIÓN DE NGINX
# ============================================================
echo "✓ Configurando Nginx..."
cat > /etc/nginx/sites-available/flashdrop << 'NGINX_CONF'
server {
    listen 80;
    listen [::]:80;
    server_name 143.244.165.130;

    root /var/www/flashdrop;
    index index.php index.html;

    # Logs
    access_log /var/log/nginx/flashdrop-access.log;
    error_log /var/log/nginx/flashdrop-error.log;

    # Seguridad: no servir archivos ocultos
    location ~ /\. {
        deny all;
        log_not_found off;
        access_log off;
    }

    # PHP-FPM
    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
        
        # Seguridad
        fastcgi_param PHP_VALUE "display_errors=Off";
    }

    # Uploads: NO ejecutar PHP
    location ~ ^/uploads/.*\.php$ {
        deny all;
    }

    # Directorio /uploads: servir solo imágenes
    location /uploads {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }

    # Rewrite: enviar todo a index.php (opcional para routing bonito)
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
}
NGINX_CONF

ln -sf /etc/nginx/sites-available/flashdrop /etc/nginx/sites-enabled/ 2>/dev/null || true
rm -f /etc/nginx/sites-enabled/default 2>/dev/null || true

# Validar sintaxis de Nginx
nginx -t

# ============================================================
# 9. CONFIGURACIÓN DE PHP-FPM
# ============================================================
echo "✓ Configurando PHP-FPM..."
sed -i 's/^; *max_input_vars.*/max_input_vars = 3000/' /etc/php/8.3/fpm/php.ini
sed -i 's/^upload_max_filesize.*/upload_max_filesize = 100M/' /etc/php/8.3/fpm/php.ini
sed -i 's/^post_max_size.*/post_max_size = 100M/' /etc/php/8.3/fpm/php.ini
sed -i 's/^display_errors.*/display_errors = Off/' /etc/php/8.3/fpm/php.ini
sed -i 's/^error_reporting.*/error_reporting = E_ALL \& ~E_NOTICE/' /etc/php/8.3/fpm/php.ini

# ============================================================
# 10. MYSQL: USUARIO Y BASE DE DATOS INICIALES
# ============================================================
echo "✓ Inicializando MySQL..."
# Crear BD de prueba (la real se importará desde .sql en el deploy)
mysql -e "CREATE DATABASE IF NOT EXISTS flashdrop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" || true

# Crear usuario de aplicación
MYSQL_APP_PASS=$(openssl rand -base64 16)
mysql -e "DROP USER IF EXISTS 'flashdrop_app'@'localhost';" || true
mysql -e "CREATE USER 'flashdrop_app'@'localhost' IDENTIFIED BY '$MYSQL_APP_PASS';" || true
mysql -e "GRANT SELECT, INSERT, UPDATE ON flashdrop.* TO 'flashdrop_app'@'localhost';" || true
mysql -e "FLUSH PRIVILEGES;" || true

echo "  Credenciales de MySQL:"
echo "  Usuario: flashdrop_app"
echo "  Pass: $MYSQL_APP_PASS"
echo "  ⚠️  Guárdalo en las variables de GitLab como DB_PASS"

# ============================================================
# 11. INICIAR SERVICIOS
# ============================================================
echo "✓ Iniciando servicios..."
systemctl enable php8.3-fpm mysql nginx
systemctl restart php8.3-fpm mysql nginx

# ============================================================
# 12. CERTIFICADO SSL (Let's Encrypt - recomendado)
# ============================================================
echo ""
echo "✓ Instalando Certbot para SSL..."
apt-get install -y certbot python3-certbot-nginx
echo "  Para obtener certificado SSL:"
echo "  1. Apunta tu dominio a la IP: 143.244.165.130"
echo "  2. Ejecuta: sudo certbot --nginx -d tu-dominio.com"

# ============================================================
# 13. SCRIPT DE DEPLOY
# ============================================================
echo "✓ Creando script de deploy..."
mkdir -p /home/$DEPLOY_USER/scripts
cat > /home/$DEPLOY_USER/scripts/deploy.sh << 'DEPLOY_SCRIPT'
#!/bin/bash
set -e
DEPLOY_PATH="/var/www/flashdrop"
cd $DEPLOY_PATH
echo "=== Pull de código ==="
git pull origin FlashDrop 2>/dev/null || (git init && git remote add origin $GIT_REPO && git fetch --all && git checkout -b main origin/main)
echo "=== Permisos ==="
chmod -R 755 .
chmod -R 775 uploads/ video/
chmod 600 config.php 2>/dev/null || true
echo "=== Reiniciando servicios ==="
sudo systemctl reload php8.3-fpm
sudo systemctl reload nginx
echo "✓ Deploy completado"
DEPLOY_SCRIPT
chmod +x /home/$DEPLOY_USER/scripts/deploy.sh
chown $DEPLOY_USER:$DEPLOY_USER /home/$DEPLOY_USER/scripts/deploy.sh

# ============================================================
# 14. SUDO SIN CONTRASEÑA PARA SYSTEMCTL (seguro para el deploy)
# ============================================================
echo "✓ Configurando sudoers para deploy..."
cat >> /etc/sudoers.d/flashdrop-deploy << SUDOERS
$DEPLOY_USER ALL=(ALL) NOPASSWD: /bin/systemctl reload php8.3-fpm
$DEPLOY_USER ALL=(ALL) NOPASSWD: /bin/systemctl restart php8.3-fpm
$DEPLOY_USER ALL=(ALL) NOPASSWD: /bin/systemctl reload nginx
$DEPLOY_USER ALL=(ALL) NOPASSWD: /bin/systemctl restart nginx
SUDOERS
chmod 440 /etc/sudoers.d/flashdrop-deploy

# ============================================================
# 15. FIREWALL (UFW)
# ============================================================
echo "✓ Configurando firewall..."
ufw allow 22/tcp  # SSH
ufw allow 80/tcp  # HTTP
ufw allow 443/tcp # HTTPS
ufw --force enable

# ============================================================
# FINAL
# ============================================================
echo ""
echo "======================================"
echo "✅ Setup completado"
echo "======================================"
echo ""
echo "📝 PRÓXIMOS PASOS:"
echo ""
echo "1. En GitLab (Settings → CI/CD → Variables):"
echo "   DEPLOY_SSH_KEY         = (contenido de ~/.ssh/id_rsa privada)"
echo "   DB_HOST                = 127.0.0.1"
echo "   DB_NAME                = flashdrop"
echo "   DB_USER                = flashdrop_app"
echo "   DB_PASS                = $MYSQL_APP_PASS"
echo "   WATERMARK_SCALE        = 0.42"
echo ""
echo "2. En tu repositorio local:"
echo "   cp config.example.php config.php"
echo "   # Edita config.php con los valores anteriores"
echo ""
echo "3. En DigitalOcean:"
echo "   - Apunta tu dominio a 143.244.165.130"
echo "   - Ejecuta: certbot --nginx -d tu-dominio.com"
echo ""
echo "4. Push a GitLab:"
echo "   git add ."
echo "   git commit -m 'Initial commit'"
echo "   git push origin FlashDrop"
echo ""
echo "5. En GitLab: Pipelines → Deploy (click en botón 'manual')"
echo ""
echo "6. Crea el admin:"
echo "   ssh $DEPLOY_USER@143.244.165.130"
echo "   cd /var/www/flashdrop"
echo "   php tools/crear_admin.php alejo MiContraseña123"
echo ""
echo "🎉 ¡Listo!"

#!/bin/bash
# scripts/deploy.sh v2 — Deploy en el servidor (ejecutado por pipeline CI/CD vía SSH)

set -e

DEPLOY_PATH="/var/www/flashdrop"
LOG_FILE="/var/log/flashdrop-deploy.log"

{
    echo "========================================"
    echo "Deploy iniciado: $(date)"
    echo "========================================"

    cd $DEPLOY_PATH || exit 1

    # 1. Actualizar código
    echo "Descargando codigo..."
    if [ -d .git ]; then
        git fetch origin
        git reset --hard origin/main
    else
        echo "Error: no es un repositorio git"
        exit 1
    fi

    # 2. Crear estructura de directorios v2
    echo "Configurando directorios..."
    mkdir -p uploads/originals video
    chmod -R 775 uploads video

    # 3. Permisos de archivos PHP
    echo "Aplicando permisos..."
    find . -type f -name "*.php"    -exec chmod 644 {} \;
    find . -type d                  -exec chmod 755 {} \;
    find . -name ".htaccess"        -exec chmod 644 {} \;
    chmod -R 775 uploads/ video/

    # 4. Validar estructura
    echo "Validando estructura..."
    test -f config.php      || echo "ADVERTENCIA: falta config.php"
    test -f .htaccess       || echo "ADVERTENCIA: falta .htaccess raiz"
    test -d uploads/originals && echo "OK: uploads/originals existe"

    # 5. Aplicar esquema DB (idempotente)
    echo "Aplicando esquema DB..."
    sudo mysql flashdrop < db.sql

    # 6. Limpiar caché PHP
    echo "Recargando servicios..."
    sudo systemctl reload php8.3-fpm 2>/dev/null || echo "ADVERTENCIA: no se pudo recargar php-fpm"
    sudo systemctl reload nginx 2>/dev/null      || echo "ADVERTENCIA: no se pudo recargar nginx"

    # 7. Health check
    echo "Health check..."
    sleep 2
    STATUS=$(curl -s -o /dev/null -w "%{http_code}" http://localhost/index.php 2>/dev/null || echo "000")
    if [ "$STATUS" = "000" ] || echo "$STATUS" | grep -q "^5"; then
        echo "FALLO health check: HTTP $STATUS"
        tail -20 /var/log/nginx/flashdrop-error.log
        exit 1
    fi
    echo "OK: health check HTTP $STATUS"

    echo "========================================"
    echo "Deploy completado: $(date)"
    echo "========================================"
} | tee -a "$LOG_FILE"

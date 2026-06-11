#!/bin/bash
# scripts/deploy.sh — Script de deploy en el servidor
# Ejecutado por el pipeline CI/CD vía SSH
# Uso: ssh deploy@servidor /var/www/flashdrop/scripts/deploy.sh

set -e

DEPLOY_PATH="/var/www/flashdrop"
LOG_FILE="/var/log/flashdrop-deploy.log"

{
    echo "========================================"
    echo "🚀 Deploy iniciado: $(date)"
    echo "========================================"
    
    cd $DEPLOY_PATH || exit 1
    
    # ============================================================
    # 1. ACTUALIZAR CÓDIGO DESDE GIT
    # ============================================================
    echo "📥 Descargando código..."
    if [ -d .git ]; then
        git fetch origin FlashDrop
        git reset --hard origin/main
    else
        echo "Error: no es un repositorio git"
        exit 1
    fi
    
    # ============================================================
    # 2. INSTALAR DEPENDENCIAS (si existen)
    # ============================================================
    if [ -f composer.json ]; then
        echo "📦 Instalando dependencias PHP..."
        composer install --no-dev --optimize-autoloader --no-interaction
    fi
    
    # ============================================================
    # 3. APLICAR PERMISOS
    # ============================================================
    echo "🔐 Configurando permisos..."
    find . -type f -name "*.php" -exec chmod 644 {} \;
    find . -type d -exec chmod 755 {} \;
    chmod 644 config.php 2>/dev/null || true
    chmod 755 tools/*.php 2>/dev/null || true
    
    # uploads y video: lectura/escritura para www-data
    chmod -R 775 uploads/ video/
    
    # .htaccess debe ser legible
    find . -name ".htaccess" -exec chmod 644 {} \;
    
    # ============================================================
    # 4. VALIDAR ESTRUCTURA
    # ============================================================
    echo "✔️ Validando estructura..."
    test -d uploads || { mkdir -p uploads; chmod 775 uploads; }
    test -d video || { mkdir -p video; chmod 775 video; }
    test -f .htaccess || echo "⚠️  Falta .htaccess"
    test -f config.php || echo "⚠️  Falta config.php (debería existir)"
    
    # ============================================================
    # 5. LIMPIAR CACHÉ PHP
    # ============================================================
    echo "🧹 Limpiando caché PHP..."
    sudo systemctl reload php8.3-fpm 2>/dev/null || echo "⚠️  no se pudo recargar php-fpm"
    sudo systemctl reload nginx 2>/dev/null || echo "⚠️  no se pudo recargar nginx"
    
    # ============================================================
    # 6. HEALTH CHECK
    # ============================================================
    echo "🏥 Health check..."
    sleep 2
    
    # Verificar que el sitio responda (HTTP 200 o 404 está bien, 500 es malo)
    STATUS=$(curl -s -o /dev/null -w "%{http_code}" http://localhost/index.php 2>/dev/null || echo "000")
    if [ "$STATUS" = "000" ] || [[ "$STATUS" = "5"* ]]; then
        echo "❌ Health check falló: HTTP $STATUS"
        echo "Revisa los logs de Nginx:"
        tail -20 /var/log/nginx/flashdrop-error.log
        exit 1
    fi
    echo "✓ Health check OK (HTTP $STATUS)"
    
    # ============================================================
    # 7. COMPLETADO
    # ============================================================
    echo "========================================"
    echo "✅ Deploy completado: $(date)"
    echo "========================================"
} | tee -a "$LOG_FILE"

#!/bin/bash

# Script de Recuperação: curso.conf está VAZIO (0 bytes)

echo "=========================================="
echo "🚨 RECUPERAÇÃO DE CONFIGURAÇÃO NGINX"
echo "=========================================="
echo ""

# Verificar se o arquivo está realmente vazio
FILE_SIZE=$(stat -c%s /etc/nginx/conf.d/curso.conf 2>/dev/null || echo "0")
echo "📊 Tamanho do curso.conf: ${FILE_SIZE} bytes"
echo ""

if [ "$FILE_SIZE" -eq 0 ]; then
    echo "⚠️  CONFIRMADO: Arquivo está vazio!"
    echo ""
    
    # Procurar backups
    echo "🔍 Procurando backups..."
    BACKUPS=$(ls -lht /etc/nginx/conf.d/curso.conf.backup* 2>/dev/null)
    
    if [ ! -z "$BACKUPS" ]; then
        echo "✅ Backups encontrados:"
        echo "$BACKUPS"
        echo ""
        
        # Pegar o backup mais recente
        LATEST_BACKUP=$(ls -t /etc/nginx/conf.d/curso.conf.backup* 2>/dev/null | head -1)
        echo "📄 Usando backup mais recente: $LATEST_BACKUP"
        echo ""
        
        # Restaurar
        echo "♻️  Restaurando configuração..."
        sudo cp "$LATEST_BACKUP" /etc/nginx/conf.d/curso.conf
        
    else
        echo "⚠️  Nenhum backup encontrado!"
        echo ""
        echo "📝 Criando configuração funcional do zero..."
        
        # Criar configuração básica funcional
        sudo tee /etc/nginx/conf.d/curso.conf > /dev/null <<'EOF'
server {
    listen 8088 ssl http2;
    listen [::]:8088 ssl http2;
    
    server_name tstjoinenglish.duckdns.org;
    
    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/tstjoinenglish.duckdns.org/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/tstjoinenglish.duckdns.org/privkey.pem;
    
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_prefer_server_ciphers off;
    
    root /var/www/ingles03/public;
    index index.php index.html;
    
    # Security Headers
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    add_header X-Frame-Options "DENY" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_pass unix:/run/php-fpm/www.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param HTTPS on;
    }

    location ~ /\. {
        deny all;
    }
    
    access_log /var/log/nginx/ingles03_access.log;
    error_log /var/log/nginx/ingles03_error.log;
}
EOF
        echo "✅ Configuração básica criada!"
    fi
    
    echo ""
    echo "🧪 Testando configuração..."
    sudo nginx -t
    
    if [ $? -eq 0 ]; then
        echo ""
        echo "✅ Configuração válida!"
        echo ""
        echo "🔄 Recarregando Nginx..."
        sudo systemctl reload nginx
        
        echo ""
        echo "⏱️  Aguardando 2 segundos..."
        sleep 2
        
        echo ""
        echo "🧪 Testando conexão local..."
        curl -I http://localhost:8088
        
        echo ""
        echo "🧪 Verificando porta 8088..."
        sudo netstat -tlnp | grep :8088 || sudo ss -tlnp | grep :8088
        
        echo ""
        echo "=========================================="
        echo "✅ RECUPERAÇÃO CONCLUÍDA!"
        echo "=========================================="
        echo ""
        echo "Teste no navegador:"
        echo "  https://tstjoinenglish.duckdns.org:8088"
        
    else
        echo ""
        echo "❌ ERRO na configuração!"
        echo ""
        echo "Verifique os erros acima e:"
        echo "  sudo tail -n 50 /var/log/nginx/error.log"
    fi
    
else
    echo "ℹ️  Arquivo não está vazio. Verificando conteúdo..."
    echo ""
    echo "Primeiras 20 linhas:"
    sudo head -n 20 /etc/nginx/conf.d/curso.conf
fi

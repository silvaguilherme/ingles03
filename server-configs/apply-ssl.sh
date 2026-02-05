#!/bin/bash

# Script para aplicar configuração SSL correta

echo "=========================================="
echo "🔧 APLICANDO CONFIGURAÇÃO SSL"
echo "=========================================="
echo ""

echo "⚠️  PROBLEMA IDENTIFICADO:"
echo "   O curso.conf está configurado para HTTP (porta 8088)"
echo "   mas SEM SSL! Por isso ERR_SSL_PROTOCOL_ERROR"
echo ""
echo "✅ SOLUÇÃO:"
echo "   Aplicar configuração com SSL ativado"
echo ""

# Backup
echo "💾 Fazendo backup da configuração atual..."
sudo cp /etc/nginx/conf.d/curso.conf /etc/nginx/conf.d/curso.conf.backup-http-only
echo "   Backup salvo em: curso.conf.backup-http-only"
echo ""

# Aplicar configuração SSL
echo "📝 Aplicando configuração SSL..."
sudo tee /etc/nginx/conf.d/curso.conf > /dev/null <<'EOF'
server {
    listen 8088 ssl http2;
    listen [::]:8088 ssl http2;
    
    server_name tstjoinenglish.duckdns.org;
    
    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/tstjoinenglish.duckdns.org/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/tstjoinenglish.duckdns.org/privkey.pem;
    
    # SSL Protocols e Ciphers Modernos
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers 'ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384:ECDHE-ECDSA-CHACHA20-POLY1305:ECDHE-RSA-CHACHA20-POLY1305';
    ssl_prefer_server_ciphers off;
    
    # SSL Session Cache
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;
    ssl_session_tickets off;

    root /var/www/ingles03/public;
    index index.php index.html;
    
    # Security Headers
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    add_header X-Frame-Options "DENY" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    
    # Desabilitar server tokens
    server_tokens off;

    # Laravel Routes
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP-FPM Configuration
    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_pass unix:/run/php-fpm/www.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param HTTPS on;
        
        fastcgi_connect_timeout 60s;
        fastcgi_send_timeout 180s;
        fastcgi_read_timeout 180s;
    }

    # Bloquear arquivos ocultos
    location ~ /\. {
        deny all;
        access_log off;
        log_not_found off;
    }
    
    # Bloquear arquivos sensíveis
    location ~* \.(env|log)$ {
        deny all;
    }
    
    # Favicon e robots.txt
    location = /favicon.ico {
        access_log off;
        log_not_found off;
    }
    
    location = /robots.txt {
        access_log off;
        log_not_found off;
    }
    
    # Cache de arquivos estáticos
    location ~* \.(jpg|jpeg|png|gif|ico|svg|webp|css|js|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }
    
    # Logs
    access_log /var/log/nginx/ingles03_access.log;
    error_log /var/log/nginx/ingles03_error.log;
}
EOF

echo "✅ Configuração SSL aplicada!"
echo ""

# Testar configuração
echo "🧪 Testando configuração..."
if sudo nginx -t; then
    echo ""
    echo "✅ Configuração válida!"
    echo ""
    
    # Recarregar Nginx
    echo "🔄 Recarregando Nginx..."
    sudo systemctl reload nginx
    
    echo ""
    echo "⏱️  Aguardando 2 segundos..."
    sleep 2
    
    echo ""
    echo "🔍 Verificando porta 8088 com SSL..."
    sudo ss -tlnp | grep :8088
    
    echo ""
    echo "🧪 Testando SSL local..."
    timeout 3 openssl s_client -connect localhost:8088 -servername tstjoinenglish.duckdns.org </dev/null 2>&1 | grep -E "(subject|issuer|Verification|Protocol)"
    
    echo ""
    echo "=========================================="
    echo "✅ CONFIGURAÇÃO SSL APLICADA COM SUCESSO!"
    echo "=========================================="
    echo ""
    echo "🌐 Teste no navegador:"
    echo "   https://tstjoinenglish.duckdns.org:8088"
    echo ""
    echo "🔒 Certificado válido até: May 6 10:58:23 2026 GMT"
    echo ""
    
else
    echo ""
    echo "❌ ERRO na configuração!"
    echo ""
    echo "Restaurando backup..."
    sudo cp /etc/nginx/conf.d/curso.conf.backup-http-only /etc/nginx/conf.d/curso.conf
    sudo systemctl reload nginx
    echo ""
    echo "❌ Configuração restaurada para versão HTTP"
    echo "   Verifique os erros acima"
fi

#!/bin/bash

# Script de Diagnóstico SSL para ERR_SSL_PROTOCOL_ERROR

echo "=========================================="
echo "🔒 DIAGNÓSTICO SSL"
echo "=========================================="
echo ""

# 1. Verificar certificados
echo "1️⃣ Certificados Let's Encrypt:"
echo "-----------------------------------"
if [ -d "/etc/letsencrypt/live/tstjoinenglish.duckdns.org" ]; then
    ls -lh /etc/letsencrypt/live/tstjoinenglish.duckdns.org/
    echo ""
    echo "📅 Validade do certificado:"
    sudo openssl x509 -in /etc/letsencrypt/live/tstjoinenglish.duckdns.org/fullchain.pem -noout -dates
    echo ""
    echo "📋 Detalhes do certificado:"
    sudo openssl x509 -in /etc/letsencrypt/live/tstjoinenglish.duckdns.org/fullchain.pem -noout -subject -issuer
else
    echo "❌ Diretório de certificados NÃO encontrado!"
fi
echo ""

# 2. Verificar configuração Nginx SSL
echo "2️⃣ Configuração SSL no Nginx:"
echo "-----------------------------------"
echo "Tamanho do arquivo: $(stat -c%s /etc/nginx/conf.d/curso.conf 2>/dev/null || echo '0') bytes"
echo ""
echo "Linhas SSL:"
sudo grep -n "ssl" /etc/nginx/conf.d/curso.conf
echo ""

# 3. Ver configuração completa
echo "3️⃣ Configuração completa curso.conf:"
echo "-----------------------------------"
sudo cat /etc/nginx/conf.d/curso.conf
echo ""

# 4. Testar SSL local
echo "4️⃣ Teste SSL local:"
echo "-----------------------------------"
echo "OpenSSL s_client test:"
timeout 3 openssl s_client -connect localhost:8088 -servername tstjoinenglish.duckdns.org 2>&1 | head -20
echo ""

# 5. Verificar porta 8088
echo "5️⃣ Porta 8088 escutando:"
echo "-----------------------------------"
sudo ss -tlnp | grep :8088
echo ""

# 6. Últimos erros SSL
echo "6️⃣ Erros recentes do Nginx:"
echo "-----------------------------------"
sudo tail -n 30 /var/log/nginx/error.log | grep -i ssl
echo ""

# 7. Teste de sintaxe
echo "7️⃣ Teste de sintaxe Nginx:"
echo "-----------------------------------"
sudo nginx -t
echo ""

echo "=========================================="
echo "🔧 COMANDOS DE CORREÇÃO"
echo "=========================================="
echo ""
echo "Se certificados estão OK mas configuração errada:"
echo "  sudo cp /var/www/ingles03/server-configs/nginx-tstjoinenglish.conf /etc/nginx/conf.d/curso.conf"
echo "  sudo nginx -t"
echo "  sudo systemctl reload nginx"
echo ""
echo "Se certificados expiraram:"
echo "  sudo certbot renew --force-renewal"
echo ""
echo "Se curso.conf está vazio ou corrompido:"
echo "  sudo ./server-configs/restore-config.sh"
echo ""

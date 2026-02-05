#!/bin/bash

# Script de instalação SSL na porta 8088
# Execute com: sudo bash install-ssl-8088.sh

set -e

echo "========================================"
echo "Instalação SSL na Porta 8088"
echo "========================================"
echo ""

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Variáveis
DOMAIN="tstjoinenglish.duckdns.org"
PROJECT_PATH="/var/www/curso"
EMAIL="your-email@example.com"  # ALTERE ESTE EMAIL
PORT=8088

# Verificar se está rodando como root
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}Por favor, execute como root (sudo)${NC}"
    exit 1
fi

# Solicitar email se não foi alterado
if [ "$EMAIL" == "your-email@example.com" ]; then
    echo -e "${YELLOW}Digite seu email para Let's Encrypt:${NC}"
    read -p "Email: " EMAIL
fi

# Detectar servidor web
echo -e "${YELLOW}Detectando servidor web...${NC}"
if command -v apache2 &> /dev/null; then
    SERVER="apache"
    echo -e "${GREEN}Apache detectado${NC}"
elif command -v nginx &> /dev/null; then
    SERVER="nginx"
    echo -e "${GREEN}Nginx detectado${NC}"
else
    echo -e "${RED}Nenhum servidor web detectado (Apache ou Nginx)${NC}"
    exit 1
fi

# Verificar se o projeto existe
if [ ! -d "$PROJECT_PATH" ]; then
    echo -e "${RED}Projeto não encontrado em $PROJECT_PATH${NC}"
    echo -e "${YELLOW}Por favor, ajuste a variável PROJECT_PATH no script${NC}"
    exit 1
fi

# Primeiro, obter certificado usando porta 80 temporária
echo -e "${YELLOW}Obtendo certificado SSL usando porta 80...${NC}"
echo -e "${YELLOW}Importante: Certifique-se de que $DOMAIN aponta para este servidor${NC}"
echo -e "${YELLOW}e que a porta 80 está livre${NC}"
sleep 3

# Instalar Certbot
echo -e "${YELLOW}Instalando Certbot...${NC}"
apt update
apt install certbot -y

# Obter certificado usando modo standalone
echo -e "${YELLOW}Obtendo certificado...${NC}"
certbot certonly --standalone -d "$DOMAIN" --email "$EMAIL" --agree-tos --non-interactive

if [ $? -ne 0 ]; then
    echo -e "${RED}Falha ao obter certificado${NC}"
    echo -e "${YELLOW}Certifique-se de que:${NC}"
    echo -e "  1. A porta 80 está livre"
    echo -e "  2. O domínio aponta para este servidor"
    echo -e "  3. O firewall permite conexões na porta 80"
    exit 1
fi

# Configurar servidor web
echo -e "${YELLOW}Configurando servidor web na porta $PORT...${NC}"

if [ "$SERVER" == "apache" ]; then
    # Configuração Apache
    echo -e "${YELLOW}Configurando Apache...${NC}"
    
    # Adicionar Listen 8088 no ports.conf
    if ! grep -q "Listen $PORT" /etc/apache2/ports.conf; then
        echo "Listen $PORT" >> /etc/apache2/ports.conf
        echo -e "${GREEN}Porta $PORT adicionada ao ports.conf${NC}"
    fi
    
    # Copiar configuração
    if [ -f "./server-configs/apache-tstjoinenglish.conf" ]; then
        cp ./server-configs/apache-tstjoinenglish.conf /etc/apache2/sites-available/tstjoinenglish.conf
        
        # Ajustar caminho
        sed -i "s|/var/www/curso|$PROJECT_PATH|g" /etc/apache2/sites-available/tstjoinenglish.conf
        
        # Habilitar módulos
        a2enmod ssl
        a2enmod rewrite
        a2enmod headers
        
        # Habilitar site
        a2ensite tstjoinenglish.conf
        
        # Testar configuração
        apache2ctl configtest
        
        # Reiniciar Apache
        systemctl restart apache2
        
        echo -e "${GREEN}Apache configurado com sucesso na porta $PORT${NC}"
    else
        echo -e "${RED}Arquivo de configuração não encontrado${NC}"
        exit 1
    fi
else
    # Configuração Nginx
    echo -e "${YELLOW}Configurando Nginx...${NC}"
    
    # Copiar configuração
    if [ -f "./server-configs/nginx-tstjoinenglish.conf" ]; then
        cp ./server-configs/nginx-tstjoinenglish.conf /etc/nginx/sites-available/tstjoinenglish
        
        # Ajustar caminho
        sed -i "s|/var/www/curso|$PROJECT_PATH|g" /etc/nginx/sites-available/tstjoinenglish
        
        # Criar symlink
        ln -sf /etc/nginx/sites-available/tstjoinenglish /etc/nginx/sites-enabled/
        
        # Testar configuração
        nginx -t
        
        # Reiniciar Nginx
        systemctl restart nginx
        
        echo -e "${GREEN}Nginx configurado com sucesso na porta $PORT${NC}"
    else
        echo -e "${RED}Arquivo de configuração não encontrado${NC}"
        exit 1
    fi
fi

# Configurar permissões do Laravel
echo -e "${YELLOW}Configurando permissões...${NC}"
chown -R www-data:www-data "$PROJECT_PATH/storage"
chown -R www-data:www-data "$PROJECT_PATH/bootstrap/cache"
chmod -R 775 "$PROJECT_PATH/storage"
chmod -R 775 "$PROJECT_PATH/bootstrap/cache"

# Atualizar .env do Laravel
echo -e "${YELLOW}Atualizando .env...${NC}"
if [ -f "$PROJECT_PATH/.env" ]; then
    sed -i "s|APP_URL=.*|APP_URL=https://$DOMAIN:$PORT|g" "$PROJECT_PATH/.env"
    echo -e "${GREEN}.env atualizado${NC}"
fi

# Limpar cache do Laravel
echo -e "${YELLOW}Limpando cache do Laravel...${NC}"
cd "$PROJECT_PATH"
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan cache:clear
sudo -u www-data php artisan route:clear
sudo -u www-data php artisan view:clear
sudo -u www-data php artisan optimize

# Configurar renovação automática
echo -e "${YELLOW}Configurando renovação automática...${NC}"

# Criar hook de renovação para reiniciar o servidor
cat > /etc/letsencrypt/renewal-hooks/deploy/restart-webserver.sh << 'EOF'
#!/bin/bash
if systemctl is-active apache2 &> /dev/null; then
    systemctl restart apache2
fi
if systemctl is-active nginx &> /dev/null; then
    systemctl restart nginx
fi
EOF

chmod +x /etc/letsencrypt/renewal-hooks/deploy/restart-webserver.sh

# Testar renovação
echo -e "${YELLOW}Testando renovação automática...${NC}"
certbot renew --dry-run

# Configurar firewall
if command -v ufw &> /dev/null && ufw status | grep -q "active"; then
    echo -e "${YELLOW}Configurando firewall...${NC}"
    ufw allow $PORT/tcp
    echo -e "${GREEN}Porta $PORT liberada no firewall${NC}"
fi

echo ""
echo -e "${GREEN}=======================================${NC}"
echo -e "${GREEN}SSL instalado com sucesso!${NC}"
echo -e "${GREEN}=======================================${NC}"
echo ""
echo -e "Acesse: ${GREEN}https://$DOMAIN:$PORT${NC}"
echo ""
echo -e "${YELLOW}Importante:${NC}"
echo -e "  - Libere a porta $PORT no firewall/roteador"
echo -e "  - O certificado será renovado automaticamente"
echo -e "  - Para renovar manualmente: ${YELLOW}sudo certbot renew${NC}"
echo ""
echo -e "Logs:"
echo -e "  - Certbot: /var/log/letsencrypt/"
if [ "$SERVER" == "apache" ]; then
    echo -e "  - Apache: /var/log/apache2/tstjoinenglish_8088_error.log"
else
    echo -e "  - Nginx: /var/log/nginx/tstjoinenglish_8088_error.log"
fi
echo ""

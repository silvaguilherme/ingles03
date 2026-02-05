#!/bin/bash

# Script de instalação SSL para tstjoinenglish.duckdns.org
# Execute com: sudo bash install-ssl.sh

set -e

echo "================================"
echo "Instalação SSL - tstjoinenglish"
echo "================================"
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

# Instalar Certbot
echo -e "${YELLOW}Instalando Certbot...${NC}"
apt update
if [ "$SERVER" == "apache" ]; then
    apt install certbot python3-certbot-apache -y
else
    apt install certbot python3-certbot-nginx -y
fi

# Verificar se o projeto existe
if [ ! -d "$PROJECT_PATH" ]; then
    echo -e "${RED}Projeto não encontrado em $PROJECT_PATH${NC}"
    echo -e "${YELLOW}Por favor, ajuste a variável PROJECT_PATH no script${NC}"
    exit 1
fi

# Copiar configuração do servidor
echo -e "${YELLOW}Configurando servidor web...${NC}"
if [ "$SERVER" == "apache" ]; then
    # Copiar configuração do Apache
    if [ -f "./server-configs/apache-tstjoinenglish.conf" ]; then
        cp ./server-configs/apache-tstjoinenglish.conf /etc/apache2/sites-available/tstjoinenglish.conf
        
        # Habilitar módulos necessários para proxy
        a2enmod proxy
        a2enmod proxy_http
        a2enmod rewrite
        a2enmod ssl
        a2enmod headers
        
        # Habilitar site
        a2ensite tstjoinenglish.conf
        
        # Testar configuração
        apache2ctl configtest
        
        # Reiniciar Apache
        systemctl restart apache2
    else
        echo -e "${RED}Arquivo de configuração não encontrado${NC}"
        exit 1
    fi
else
    # Copiar configuração do Nginx
    if [ -f "./server-configs/nginx-tstjoinenglish.conf" ]; then
        cp ./server-configs/nginx-tstjoinenglish.conf /etc/nginx/sites-available/tstjoinenglish
        
        # Criar symlink
        ln -sf /etc/nginx/sites-available/tstjoinenglish /etc/nginx/sites-enabled/
        
        # Remover default se existir
        rm -f /etc/nginx/sites-enabled/default
        
        # Testar configuração
        nginx -t
        
        # Reiniciar Nginx
        systemctl restart nginx
        
        echo -e "${GREEN}Nginx configurado com proxy reverso para porta 8088${NC}"
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

# Obter certificado SSL
echo -e "${YELLOW}Obtendo certificado SSL...${NC}"
echo -e "${YELLOW}Importante: Certifique-se de que $DOMAIN aponta para este servidor${NC}"
sleep 3

if [ "$SERVER" == "apache" ]; then
    certbot --apache -d "$DOMAIN" --email "$EMAIL" --agree-tos --non-interactive --redirect
else
    certbot --nginx -d "$DOMAIN" --email "$EMAIL" --agree-tos --non-interactive --redirect
fi

# Atualizar .env do Laravel
echo -e "${YELLOW}Atualizando .env...${NC}"
if [ -f "$PROJECT_PATH/.env" ]; then
    sed -i "s|APP_URL=.*|APP_URL=https://$DOMAIN|g" "$PROJECT_PATH/.env"
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

# Testar renovação automática
echo -e "${YELLOW}Testando renovação automática...${NC}"
certbot renew --dry-run

# Configurar firewall (se UFW estiver ativo)
if command -v ufw &> /dev/null && ufw status | grep -q "active"; then
    echo -e "${YELLOW}Configurando firewall...${NC}"
    ufw allow 80/tcp
    ufw allow 443/tcp
fi

echo ""
echo -e "${GREEN}================================${NC}"
echo -e "${GREEN}SSL instalado com sucesso!${NC}"
echo -e "${GREEN}================================${NC}"
echo ""
echo -e "Acesse: ${GREEN}https://$DOMAIN${NC}"
echo ""
echo -e "O certificado será renovado automaticamente."
echo -e "Para renovar manualmente: ${YELLOW}sudo certbot renew${NC}"
echo ""
echo -e "Logs do Certbot: /var/log/letsencrypt/"
echo ""

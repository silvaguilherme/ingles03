#!/bin/bash

# Script de instalação SSL na porta 8088 - Versão com Webroot
# Execute com: sudo bash install-ssl-8088-webroot.sh

set -e

echo "========================================"
echo "Instalação SSL na Porta 8088 (Webroot)"
echo "========================================"
echo ""

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Variáveis
DOMAIN="tstjoinenglish.duckdns.org"
PROJECT_PATH="/var/www/ingles03"
EMAIL="your-email@example.com"  # ALTERE ESTE EMAIL
PORT=8088
WEBROOT_PATH="$PROJECT_PATH/public"

# Verificar se está rodando como root
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}Por favor, execute como root (sudo)${NC}"
    exit 1
fi

# Detectar gerenciador de pacotes
echo -e "${YELLOW}Detectando sistema...${NC}"
if command -v apt &> /dev/null; then
    PKG_MANAGER="apt"
    PKG_UPDATE="apt update"
    PKG_INSTALL="apt install -y"
    echo -e "${GREEN}Sistema Debian/Ubuntu detectado${NC}"
elif command -v dnf &> /dev/null; then
    PKG_MANAGER="dnf"
    PKG_UPDATE="dnf check-update || true"
    PKG_INSTALL="dnf install -y"
    echo -e "${GREEN}Sistema RHEL/Oracle Linux detectado${NC}"
elif command -v yum &> /dev/null; then
    PKG_MANAGER="yum"
    PKG_UPDATE="yum check-update || true"
    PKG_INSTALL="yum install -y"
    echo -e "${GREEN}Sistema CentOS detectado${NC}"
else
    echo -e "${RED}Gerenciador de pacotes não suportado${NC}"
    exit 1
fi

# Solicitar email se não foi alterado
if [ "$EMAIL" == "your-email@example.com" ]; then
    echo -e "${YELLOW}Digite seu email para Let's Encrypt:${NC}"
    read -p "Email: " EMAIL
fi

# Detectar servidor web
echo -e "${YELLOW}Detectando servidor web...${NC}"
if command -v httpd &> /dev/null; then
    SERVER="apache"
    APACHE_CMD="httpd"
    APACHE_CONF_DIR="/etc/httpd"
    APACHE_SITES_DIR="/etc/httpd/conf.d"
    APACHE_LOG_DIR="/var/log/httpd"
    echo -e "${GREEN}Apache (httpd) detectado${NC}"
elif command -v apache2 &> /dev/null; then
    SERVER="apache"
    APACHE_CMD="apache2"
    APACHE_CONF_DIR="/etc/apache2"
    APACHE_SITES_DIR="/etc/apache2/sites-available"
    APACHE_LOG_DIR="/var/log/apache2"
    echo -e "${GREEN}Apache2 detectado${NC}"
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

# Instalar Certbot
echo -e "${YELLOW}Instalando Certbot...${NC}"
$PKG_UPDATE
$PKG_INSTALL certbot

# Criar diretório .well-known para validação
echo -e "${YELLOW}Preparando webroot para validação...${NC}"
mkdir -p "$WEBROOT_PATH/.well-known/acme-challenge"
chown -R apache:apache "$WEBROOT_PATH/.well-known" 2>/dev/null || chown -R www-data:www-data "$WEBROOT_PATH/.well-known" 2>/dev/null || chown -R nginx:nginx "$WEBROOT_PATH/.well-known" 2>/dev/null || true

# Configurar servidor web temporário na porta 80 para validação
echo -e "${YELLOW}Configurando acesso temporário na porta 80...${NC}"

if [ "$SERVER" == "apache" ]; then
    # Criar configuração temporária para porta 80
    cat > /tmp/certbot-temp.conf << EOF
<VirtualHost *:80>
    ServerName $DOMAIN
    DocumentRoot $WEBROOT_PATH
    
    <Directory $WEBROOT_PATH>
        AllowOverride All
        Require all granted
    </Directory>
    
    <Directory $WEBROOT_PATH/.well-known>
        Require all granted
    </Directory>
</VirtualHost>
EOF

    # Copiar para local apropriado
    cp /tmp/certbot-temp.conf $APACHE_SITES_DIR/certbot-temp.conf
    
    # Habilitar se usar Debian/Ubuntu
    if command -v a2ensite &> /dev/null; then
        a2ensite certbot-temp.conf 2>/dev/null || true
    fi
    
    # Verificar se porta 80 está configurada
    if [ -f "$APACHE_CONF_DIR/ports.conf" ]; then
        if ! grep -q "^Listen 80" "$APACHE_CONF_DIR/ports.conf"; then
            echo "Listen 80" >> "$APACHE_CONF_DIR/ports.conf"
        fi
    fi
    
    # Recarregar Apache
    systemctl reload $APACHE_CMD 2>/dev/null || $APACHE_CMD -k graceful 2>/dev/null || true
    
else
    # Nginx
    cat > /etc/nginx/conf.d/certbot-temp.conf << EOF
server {
    listen 80;
    server_name $DOMAIN;
    root $WEBROOT_PATH;
    
    location /.well-known/acme-challenge/ {
        allow all;
    }
}
EOF

    # Recarregar Nginx
    nginx -s reload 2>/dev/null || systemctl reload nginx 2>/dev/null || true
fi

# Aguardar um pouco
sleep 2

# Obter certificado usando webroot
echo -e "${YELLOW}Obtendo certificado SSL...${NC}"
certbot certonly --webroot -w "$WEBROOT_PATH" -d "$DOMAIN" --email "$EMAIL" --agree-tos --non-interactive

if [ $? -ne 0 ]; then
    echo -e "${RED}Falha ao obter certificado${NC}"
    echo -e "${YELLOW}Certifique-se de que:${NC}"
    echo -e "  1. O domínio $DOMAIN aponta para este servidor"
    echo -e "  2. A porta 80 está acessível externamente"
    echo -e "  3. Não há firewall bloqueando"
    
    # Limpar configuração temporária
    if [ "$SERVER" == "apache" ]; then
        rm -f $APACHE_SITES_DIR/certbot-temp.conf
        systemctl reload $APACHE_CMD 2>/dev/null || true
    else
        rm -f /etc/nginx/conf.d/certbot-temp.conf
        systemctl reload nginx 2>/dev/null || true
    fi
    
    exit 1
fi

# Remover configuração temporária da porta 80
echo -e "${YELLOW}Removendo configuração temporária...${NC}"
if [ "$SERVER" == "apache" ]; then
    if command -v a2dissite &> /dev/null; then
        a2dissite certbot-temp.conf 2>/dev/null || true
    fi
    rm -f $APACHE_SITES_DIR/certbot-temp.conf
    systemctl reload $APACHE_CMD 2>/dev/null || true
else
    rm -f /etc/nginx/conf.d/certbot-temp.conf
    systemctl reload nginx 2>/dev/null || true
fi

# Configurar servidor web na porta 8088
echo -e "${YELLOW}Configurando servidor web na porta $PORT...${NC}"

if [ "$SERVER" == "apache" ]; then
    # Configuração Apache
    echo -e "${YELLOW}Configurando Apache...${NC}"
    
    # Determinar arquivo de portas
    if [ -f "$APACHE_CONF_DIR/ports.conf" ]; then
        PORTS_FILE="$APACHE_CONF_DIR/ports.conf"
    elif [ -f "$APACHE_CONF_DIR/conf/httpd.conf" ]; then
        PORTS_FILE="$APACHE_CONF_DIR/conf/httpd.conf"
    else
        PORTS_FILE="$APACHE_CONF_DIR/httpd.conf"
    fi
    
    # Adicionar Listen 8088
    if ! grep -q "Listen $PORT" $PORTS_FILE 2>/dev/null; then
        echo "Listen $PORT" >> $PORTS_FILE
        echo -e "${GREEN}Porta $PORT adicionada${NC}"
    fi
    
    # Copiar configuração
    if [ -f "./server-configs/apache-tstjoinenglish.conf" ]; then
        CONF_FILE="$APACHE_SITES_DIR/tstjoinenglish.conf"
        cp ./server-configs/apache-tstjoinenglish.conf $CONF_FILE
        
        # Ajustar caminho e diretórios de log
        sed -i "s|/var/www/curso|$PROJECT_PATH|g" $CONF_FILE
        sed -i "s|\${APACHE_LOG_DIR}|$APACHE_LOG_DIR|g" $CONF_FILE
        
        # Habilitar módulos (se a2enmod existir)
        if command -v a2enmod &> /dev/null; then
            a2enmod ssl 2>/dev/null || true
            a2enmod rewrite 2>/dev/null || true
            a2enmod headers 2>/dev/null || true
            a2ensite tstjoinenglish.conf 2>/dev/null || true
        fi
        
        # Testar configuração
        if command -v apache2ctl &> /dev/null; then
            apache2ctl configtest || $APACHE_CMD -t
        else
            $APACHE_CMD -t
        fi
        
        # Reiniciar Apache
        systemctl restart $APACHE_CMD
        
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
        # Ajustar para Oracle/RHEL que não usa sites-available/enabled
        if [ -d "/etc/nginx/conf.d" ]; then
            CONF_FILE="/etc/nginx/conf.d/tstjoinenglish.conf"
            cp ./server-configs/nginx-tstjoinenglish.conf $CONF_FILE
        else
            cp ./server-configs/nginx-tstjoinenglish.conf /etc/nginx/sites-available/tstjoinenglish
            ln -sf /etc/nginx/sites-available/tstjoinenglish /etc/nginx/sites-enabled/
            CONF_FILE="/etc/nginx/sites-available/tstjoinenglish"
        fi
        
        # Ajustar caminho
        sed -i "s|/var/www/curso|$PROJECT_PATH|g" $CONF_FILE
        
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
chown -R www-data:www-data "$PROJECT_PATH/storage" "$PROJECT_PATH/bootstrap/cache" 2>/dev/null || \
chown -R apache:apache "$PROJECT_PATH/storage" "$PROJECT_PATH/bootstrap/cache" 2>/dev/null || \
chown -R nginx:nginx "$PROJECT_PATH/storage" "$PROJECT_PATH/bootstrap/cache" 2>/dev/null || true
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

# Determinar usuário do servidor web
if id "apache" &>/dev/null; then
    WEB_USER="apache"
elif id "www-data" &>/dev/null; then
    WEB_USER="www-data"
elif id "nginx" &>/dev/null; then
    WEB_USER="nginx"
else
    WEB_USER="root"
fi

sudo -u $WEB_USER php artisan config:clear 2>/dev/null || php artisan config:clear
sudo -u $WEB_USER php artisan cache:clear 2>/dev/null || php artisan cache:clear
sudo -u $WEB_USER php artisan route:clear 2>/dev/null || php artisan route:clear
sudo -u $WEB_USER php artisan view:clear 2>/dev/null || php artisan view:clear
sudo -u $WEB_USER php artisan optimize 2>/dev/null || php artisan optimize

# Configurar renovação automática
echo -e "${YELLOW}Configurando renovação automática...${NC}"
mkdir -p /etc/letsencrypt/renewal-hooks/deploy

cat > /etc/letsencrypt/renewal-hooks/deploy/restart-webserver.sh << 'EOF'
#!/bin/bash
if systemctl is-active httpd &> /dev/null; then
    systemctl restart httpd
fi
if systemctl is-active apache2 &> /dev/null; then
    systemctl restart apache2
fi
if systemctl is-active nginx &> /dev/null; then
    systemctl restart nginx
fi
EOF

chmod +x /etc/letsencrypt/renewal-hooks/deploy/restart-webserver.sh

# Configurar cron para renovação com webroot
echo -e "${YELLOW}Configurando renovação com webroot...${NC}"
cat > /etc/cron.d/certbot-renew << EOF
# Renovar certificados Let's Encrypt
0 3 * * * root certbot renew --webroot -w $WEBROOT_PATH --quiet --deploy-hook "/etc/letsencrypt/renewal-hooks/deploy/restart-webserver.sh"
EOF

# Configurar firewall
if command -v firewall-cmd &> /dev/null; then
    echo -e "${YELLOW}Configurando firewalld...${NC}"
    firewall-cmd --permanent --add-port=$PORT/tcp 2>/dev/null || true
    firewall-cmd --reload 2>/dev/null || true
    echo -e "${GREEN}Porta $PORT liberada no firewalld${NC}"
elif command -v ufw &> /dev/null && ufw status | grep -q "active"; then
    echo -e "${YELLOW}Configurando ufw...${NC}"
    ufw allow $PORT/tcp
    echo -e "${GREEN}Porta $PORT liberada no ufw${NC}"
fi

echo ""
echo -e "${GREEN}=======================================${NC}"
echo -e "${GREEN}SSL instalado com sucesso!${NC}"
echo -e "${GREEN}=======================================${NC}"
echo ""
echo -e "Acesse: ${GREEN}https://$DOMAIN:$PORT${NC}"
echo ""
echo -e "${YELLOW}Importante:${NC}"
echo -e "  - Porta $PORT liberada no servidor"
echo -e "  - Libere também a porta $PORT no firewall/roteador"
echo -e "  - O certificado será renovado automaticamente via cron"
echo -e "  - Para renovar manualmente: ${YELLOW}sudo certbot renew --webroot -w $WEBROOT_PATH${NC}"
echo ""
echo -e "Logs:"
echo -e "  - Certbot: /var/log/letsencrypt/"
if [ "$SERVER" == "apache" ]; then
    echo -e "  - Apache: $APACHE_LOG_DIR/tstjoinenglish_8088_error.log"
else
    echo -e "  - Nginx: /var/log/nginx/tstjoinenglish_8088_error.log"
fi
echo ""

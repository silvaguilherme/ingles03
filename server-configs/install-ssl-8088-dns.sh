#!/bin/bash

# Script de instalação SSL na porta 8088 usando DNS Challenge (DuckDNS)
# Execute com: sudo bash install-ssl-8088-dns.sh

set -e

echo "========================================"
echo "Instalação SSL - Método DNS (DuckDNS)"
echo "========================================"
echo ""

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Variáveis
DOMAIN="tstjoinenglish.duckdns.org"
DUCKDNS_SUBDOMAIN="tstjoinenglish"
DUCKDNS_TOKEN=""  # COLOQUE SEU TOKEN AQUI
PROJECT_PATH="/var/www/ingles03"
EMAIL="your-email@example.com"  # ALTERE ESTE EMAIL
PORT=8088

# Verificar se está rodando como root
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}Por favor, execute como root (sudo)${NC}"
    exit 1
fi

# Solicitar token do DuckDNS se não foi configurado
if [ -z "$DUCKDNS_TOKEN" ]; then
    echo -e "${YELLOW}Token do DuckDNS não configurado.${NC}"
    echo -e "${YELLOW}Você pode encontrar seu token em: https://www.duckdns.org${NC}"
    read -p "Digite seu token DuckDNS: " DUCKDNS_TOKEN
fi

# Solicitar email se não foi alterado
if [ "$EMAIL" == "your-email@example.com" ]; then
    echo -e "${YELLOW}Digite seu email para Let's Encrypt:${NC}"
    read -p "Email: " EMAIL
fi

# Detectar gerenciador de pacotes
echo -e "${YELLOW}Detectando sistema...${NC}"
if command -v apt &> /dev/null; then
    PKG_MANAGER="apt"
    PKG_UPDATE="apt update"
    PKG_INSTALL="apt install -y"
elif command -v dnf &> /dev/null; then
    PKG_MANAGER="dnf"
    PKG_UPDATE="dnf check-update || true"
    PKG_INSTALL="dnf install -y"
elif command -v yum &> /dev/null; then
    PKG_MANAGER="yum"
    PKG_UPDATE="yum check-update || true"
    PKG_INSTALL="yum install -y"
else
    echo -e "${RED}Gerenciador de pacotes não suportado${NC}"
    exit 1
fi

# Detectar servidor web
echo -e "${YELLOW}Detectando servidor web...${NC}"
if command -v httpd &> /dev/null; then
    SERVER="apache"
    APACHE_CMD="httpd"
    APACHE_CONF_DIR="/etc/httpd"
    APACHE_SITES_DIR="/etc/httpd/conf.d"
    APACHE_LOG_DIR="/var/log/httpd"
elif command -v apache2 &> /dev/null; then
    SERVER="apache"
    APACHE_CMD="apache2"
    APACHE_CONF_DIR="/etc/apache2"
    APACHE_SITES_DIR="/etc/apache2/sites-available"
    APACHE_LOG_DIR="/var/log/apache2"
elif command -v nginx &> /dev/null; then
    SERVER="nginx"
else
    echo -e "${RED}Nenhum servidor web detectado${NC}"
    exit 1
fi

# Verificar se o projeto existe
if [ ! -d "$PROJECT_PATH" ]; then
    echo -e "${RED}Projeto não encontrado em $PROJECT_PATH${NC}"
    exit 1
fi

# Instalar Certbot
echo -e "${YELLOW}Instalando Certbot...${NC}"
$PKG_UPDATE
$PKG_INSTALL certbot

# Criar script de hook para DuckDNS
echo -e "${YELLOW}Configurando DNS challenge com DuckDNS...${NC}"

mkdir -p /etc/letsencrypt/renewal-hooks/dns

# Script para adicionar registro TXT
cat > /etc/letsencrypt/renewal-hooks/dns/duckdns-auth.sh << EOF
#!/bin/bash
# Adicionar registro TXT para validação DNS
echo "Adicionando registro TXT..."
curl "https://www.duckdns.org/update?domains=$DUCKDNS_SUBDOMAIN&token=$DUCKDNS_TOKEN&txt=\$CERTBOT_VALIDATION"
sleep 30  # Aguardar propagação DNS
EOF

# Script para limpar registro TXT
cat > /etc/letsencrypt/renewal-hooks/dns/duckdns-cleanup.sh << EOF
#!/bin/bash
# Limpar registro TXT
echo "Limpando registro TXT..."
curl "https://www.duckdns.org/update?domains=$DUCKDNS_SUBDOMAIN&token=$DUCKDNS_TOKEN&txt=removed&clear=true"
EOF

chmod +x /etc/letsencrypt/renewal-hooks/dns/duckdns-auth.sh
chmod +x /etc/letsencrypt/renewal-hooks/dns/duckdns-cleanup.sh

# Obter certificado usando DNS challenge
echo -e "${YELLOW}Obtendo certificado SSL via DNS...${NC}"
echo -e "${YELLOW}Isso pode levar alguns minutos...${NC}"

certbot certonly --manual \
    --preferred-challenges dns \
    --manual-auth-hook /etc/letsencrypt/renewal-hooks/dns/duckdns-auth.sh \
    --manual-cleanup-hook /etc/letsencrypt/renewal-hooks/dns/duckdns-cleanup.sh \
    -d "$DOMAIN" \
    --email "$EMAIL" \
    --agree-tos \
    --non-interactive

if [ $? -ne 0 ]; then
    echo -e "${RED}Falha ao obter certificado${NC}"
    echo -e "${YELLOW}Verifique:${NC}"
    echo -e "  1. Token DuckDNS está correto"
    echo -e "  2. Domínio está ativo no DuckDNS"
    exit 1
fi

echo -e "${GREEN}Certificado obtido com sucesso!${NC}"

# Configurar servidor web na porta 8088
echo -e "${YELLOW}Configurando servidor web na porta $PORT...${NC}"

# Verificar se o diretório server-configs existe
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
if [ ! -d "$SCRIPT_DIR" ]; then
    SCRIPT_DIR="./server-configs"
fi

if [ "$SERVER" == "apache" ]; then
    # Configuração Apache
    echo -e "${YELLOW}Configurando Apache na porta $PORT...${NC}"
    
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
        echo -e "${GREEN}Porta $PORT adicionada em $PORTS_FILE${NC}"
    fi
    
    # Verificar se o arquivo de configuração existe
    if [ -f "$SCRIPT_DIR/apache-tstjoinenglish.conf" ]; then
        CONF_FILE="$APACHE_SITES_DIR/tstjoinenglish.conf"
        cp "$SCRIPT_DIR/apache-tstjoinenglish.conf" $CONF_FILE
        
        sed -i "s|/var/www/curso|$PROJECT_PATH|g" $CONF_FILE
        sed -i "s|\${APACHE_LOG_DIR}|$APACHE_LOG_DIR|g" $CONF_FILE
        
        # Habilitar módulos se disponível
        if command -v a2enmod &> /dev/null; then
            a2enmod ssl 2>/dev/null || true
            a2enmod rewrite 2>/dev/null || true
            a2enmod headers 2>/dev/null || true
            a2ensite tstjoinenglish.conf 2>/dev/null || true
        fi
        
        # Testar configuração
        if command -v apache2ctl &> /dev/null; then
            apache2ctl configtest || true
        else
            $APACHE_CMD -t || true
        fi
        
        # Reiniciar Apache
        systemctl restart $APACHE_CMD 2>/dev/null || service $APACHE_CMD restart 2>/dev/null || true
        echo -e "${GREEN}Apache configurado na porta $PORT${NC}"
    else
        echo -e "${YELLOW}Arquivo de configuração não encontrado em $SCRIPT_DIR${NC}"
        echo -e "${YELLOW}Configure manualmente o Apache para usar o certificado em:${NC}"
        echo -e "  - Certificado: /etc/letsencrypt/live/$DOMAIN/fullchain.pem"
        echo -e "  - Chave: /etc/letsencrypt/live/$DOMAIN/privkey.pem"
        echo -e "  - Porta: $PORT"
    fi
else
    # Configuração Nginx
    echo -e "${YELLOW}Configurando Nginx na porta $PORT...${NC}"
    
    if [ -f "$SCRIPT_DIR/nginx-tstjoinenglish.conf" ]; then
        if [ -d "/etc/nginx/conf.d" ]; then
            CONF_FILE="/etc/nginx/conf.d/tstjoinenglish.conf"
        else
            CONF_FILE="/etc/nginx/sites-available/tstjoinenglish"
            ln -sf $CONF_FILE /etc/nginx/sites-enabled/
        fi
        
        cp "$SCRIPT_DIR/nginx-tstjoinenglish.conf" $CONF_FILE
        sed -i "s|/var/www/curso|$PROJECT_PATH|g" $CONF_FILE
        
        nginx -t 2>/dev/null || true
        systemctl restart nginx 2>/dev/null || service nginx restart 2>/dev/null || true
        echo -e "${GREEN}Nginx configurado na porta $PORT${NC}"
    else
        echo -e "${YELLOW}Arquivo de configuração não encontrado em $SCRIPT_DIR${NC}"
        echo -e "${YELLOW}Configure manualmente o Nginx para usar o certificado em:${NC}"
        echo -e "  - Certificado: /etc/letsencrypt/live/$DOMAIN/fullchain.pem"
        echo -e "  - Chave: /etc/letsencrypt/live/$DOMAIN/privkey.pem"
        echo -e "  - Porta: $PORT"
    fi
fi

# Configurar permissões
echo -e "${YELLOW}Configurando permissões...${NC}"
chown -R apache:apache "$PROJECT_PATH/storage" "$PROJECT_PATH/bootstrap/cache" 2>/dev/null || \
chown -R www-data:www-data "$PROJECT_PATH/storage" "$PROJECT_PATH/bootstrap/cache" 2>/dev/null || true
chmod -R 775 "$PROJECT_PATH/storage" "$PROJECT_PATH/bootstrap/cache"

# Atualizar .env
if [ -f "$PROJECT_PATH/.env" ]; then
    sed -i "s|APP_URL=.*|APP_URL=https://$DOMAIN:$PORT|g" "$PROJECT_PATH/.env"
fi

# Limpar cache Laravel
cd "$PROJECT_PATH"
if id "apache" &>/dev/null; then
    WEB_USER="apache"
elif id "www-data" &>/dev/null; then
    WEB_USER="www-data"
else
    WEB_USER="root"
fi

sudo -u $WEB_USER php artisan config:clear 2>/dev/null || php artisan config:clear
sudo -u $WEB_USER php artisan cache:clear 2>/dev/null || php artisan cache:clear
sudo -u $WEB_USER php artisan optimize 2>/dev/null || php artisan optimize

# Configurar renovação automática
mkdir -p /etc/letsencrypt/renewal-hooks/deploy

cat > /etc/letsencrypt/renewal-hooks/deploy/restart-webserver.sh << 'EOFR'
#!/bin/bash
systemctl restart httpd 2>/dev/null || systemctl restart apache2 2>/dev/null || systemctl restart nginx 2>/dev/null || true
EOFR

chmod +x /etc/letsencrypt/renewal-hooks/deploy/restart-webserver.sh

# Adicionar cron para renovação
cat > /etc/cron.d/certbot-renew << EOFC
0 3 * * * root certbot renew --manual --preferred-challenges dns --manual-auth-hook /etc/letsencrypt/renewal-hooks/dns/duckdns-auth.sh --manual-cleanup-hook /etc/letsencrypt/renewal-hooks/dns/duckdns-cleanup.sh --quiet
EOFC

# Configurar firewall
if command -v firewall-cmd &> /dev/null; then
    firewall-cmd --permanent --add-port=$PORT/tcp 2>/dev/null || true
    firewall-cmd --reload 2>/dev/null || true
elif command -v ufw &> /dev/null; then
    ufw allow $PORT/tcp 2>/dev/null || true
fi

echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}SSL instalado com sucesso via DNS!${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "Acesse: ${GREEN}https://$DOMAIN:$PORT${NC}"
echo ""
echo -e "${YELLOW}Renovação automática configurada via cron${NC}"
echo -e "Certificado válido por 90 dias e será renovado automaticamente"
echo ""

# Guia: SSL Direto na Porta 8088

## Visão Geral

Este guia configura SSL diretamente na porta 8088, permitindo acesso via:
```
https://tstjoinenglish.duckdns.org:8088
```

Isso é útil quando você já tem outras aplicações usando as portas 80/443.

## Arquitetura

```
Internet → HTTPS:8088 → Apache/Nginx:8088 → Laravel (PHP-FPM)
```

## Instalação Rápida

### Passo 1: Preparar o Ambiente

```bash
# Certifique-se de que o projeto está no servidor
cd /var/www/curso

# Configurar permissões
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### Passo 2: Executar o Script de Instalação

```bash
# Fazer upload da pasta server-configs para o servidor
cd /caminho/onde/esta/server-configs

# Executar o script
sudo bash install-ssl-8088.sh
```

O script irá:
1. ✅ Obter certificado Let's Encrypt (usando porta 80 temporariamente)
2. ✅ Configurar Apache/Nginx para porta 8088 com SSL
3. ✅ Configurar renovação automática
4. ✅ Ajustar permissões do Laravel

### Passo 3: Liberar a Porta no Firewall

```bash
# UFW (Ubuntu/Debian)
sudo ufw allow 8088/tcp

# Firewalld (CentOS/RHEL)
sudo firewall-cmd --permanent --add-port=8088/tcp
sudo firewall-cmd --reload

# iptables
sudo iptables -A INPUT -p tcp --dport 8088 -j ACCEPT
sudo service iptables save
```

**Importante:** Também libere a porta 8088 no roteador/firewall da rede!

## Configuração Manual

Se preferir configurar manualmente:

### Para Apache

1. **Adicionar a porta no ports.conf:**
```bash
sudo nano /etc/apache2/ports.conf
```
Adicione:
```apache
Listen 8088
```

2. **Copiar configuração:**
```bash
sudo cp server-configs/apache-tstjoinenglish.conf /etc/apache2/sites-available/
```

3. **Obter certificado SSL:**
```bash
# Certifique-se que a porta 80 está livre temporariamente
sudo certbot certonly --standalone -d tstjoinenglish.duckdns.org
```

4. **Habilitar módulos e site:**
```bash
sudo a2enmod ssl
sudo a2enmod rewrite
sudo a2enmod headers
sudo a2ensite tstjoinenglish
sudo apache2ctl configtest
sudo systemctl restart apache2
```

### Para Nginx

1. **Copiar configuração:**
```bash
sudo cp server-configs/nginx-tstjoinenglish.conf /etc/nginx/sites-available/tstjoinenglish
sudo ln -s /etc/nginx/sites-available/tstjoinenglish /etc/nginx/sites-enabled/
```

2. **Obter certificado SSL:**
```bash
sudo certbot certonly --standalone -d tstjoinenglish.duckdns.org
```

3. **Testar e reiniciar:**
```bash
sudo nginx -t
sudo systemctl restart nginx
```

## Verificação

### 1. Verificar se o servidor está escutando na porta 8088:
```bash
sudo netstat -tlnp | grep 8088
# ou
sudo ss -tlnp | grep 8088
```

### 2. Testar localmente:
```bash
curl -k https://localhost:8088
```

### 3. Testar do navegador:
```
https://tstjoinenglish.duckdns.org:8088
```

### 4. Verificar certificado SSL:
```bash
sudo certbot certificates
```

## Renovação do Certificado

O certificado será renovado automaticamente. Para testar:

```bash
sudo certbot renew --dry-run
```

Para renovar manualmente:
```bash
sudo certbot renew
```

Após renovação, reinicie o servidor web:
```bash
# Apache
sudo systemctl restart apache2

# Nginx
sudo systemctl restart nginx
```

## Configuração do Laravel

Atualize o `.env`:
```env
APP_URL=https://tstjoinenglish.duckdns.org:8088
APP_ENV=production
APP_DEBUG=false
```

Limpe o cache:
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize
```

## Troubleshooting

### Erro: "Connection refused" na porta 8088

**Verificar se o servidor está rodando:**
```bash
sudo systemctl status apache2  # ou nginx
```

**Verificar logs:**
```bash
# Apache
sudo tail -f /var/log/apache2/tstjoinenglish_8088_error.log

# Nginx
sudo tail -f /var/log/nginx/tstjoinenglish_8088_error.log
```

### Erro: "SSL certificate problem"

**Verificar se o certificado existe:**
```bash
sudo ls -la /etc/letsencrypt/live/tstjoinenglish.duckdns.org/
```

**Se não existir, obter novamente:**
```bash
sudo certbot certonly --standalone -d tstjoinenglish.duckdns.org
```

### Erro: "Address already in use" na porta 8088

**Identificar processo:**
```bash
sudo lsof -i :8088
```

**Parar processo:**
```bash
sudo kill -9 <PID>
```

### Porta 8088 bloqueada

**Verificar firewall local:**
```bash
sudo ufw status
sudo ufw allow 8088/tcp
```

**Verificar se a porta está acessível externamente:**
```bash
# De outro computador
telnet tstjoinenglish.duckdns.org 8088
```

**Liberar no roteador:**
- Acesse as configurações do roteador
- Procure por "Port Forwarding" ou "NAT"
- Adicione regra: Porta Externa 8088 → IP do Servidor:8088

### Erro 500 no Laravel

**Verificar permissões:**
```bash
cd /var/www/curso
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

**Verificar logs do Laravel:**
```bash
tail -f storage/logs/laravel.log
```

## Comandos Úteis

```bash
# Ver portas em uso
sudo netstat -tlnp

# Reiniciar servidor web
sudo systemctl restart apache2  # ou nginx

# Ver logs em tempo real
sudo tail -f /var/log/apache2/tstjoinenglish_8088_error.log
sudo tail -f /var/log/nginx/tstjoinenglish_8088_error.log

# Testar configuração
sudo apache2ctl configtest  # Apache
sudo nginx -t              # Nginx

# Ver status do certificado
sudo certbot certificates

# Forçar renovação
sudo certbot renew --force-renewal
```

## Segurança Adicional

### 1. Configurar fail2ban (opcional)
```bash
sudo apt install fail2ban
sudo systemctl enable fail2ban
sudo systemctl start fail2ban
```

### 2. Restringir acesso por IP (opcional)

**Apache:**
```apache
<Directory /var/www/curso/public>
    Require ip 192.168.1.0/24  # Seu range de IPs
    Require ip 200.100.50.10   # IP específico
</Directory>
```

**Nginx:**
```nginx
location / {
    allow 192.168.1.0/24;
    allow 200.100.50.10;
    deny all;
}
```

## Diferença das Configurações

| Aspecto | Porta 443 (padrão) | Porta 8088 (customizada) |
|---------|-------------------|-------------------------|
| URL | `https://dominio.com` | `https://dominio.com:8088` |
| Compatibilidade | Total | Pode ter bloqueios em redes |
| Certificado | Let's Encrypt | Let's Encrypt (mesmo) |
| Firewall | Geralmente aberta | Precisa liberar manualmente |
| Convivência | Exclusivo | Permite outras apps na 443 |

## Próximos Passos

- ✅ Testar o acesso via navegador
- ✅ Configurar backup automático do certificado
- ✅ Monitorar logs regularmente
- ✅ Configurar alertas de expiração (embora renovação seja automática)
- ✅ Documentar a configuração para a equipe

## Recursos

- Let's Encrypt: https://letsencrypt.org/
- Certbot: https://certbot.eff.org/
- Teste SSL: https://www.ssllabs.com/ssltest/
- DuckDNS: https://www.duckdns.org/

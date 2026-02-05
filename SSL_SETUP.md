# Configuração SSL com Let's Encrypt e DuckDNS

Este guia mostra como configurar SSL/HTTPS no seu projeto Laravel usando Let's Encrypt e DuckDNS.

## Pré-requisitos

- Domínio DuckDNS configurado: `tstjoinenglish.duckdns.org`
- Servidor Linux (Ubuntu/Debian)
- Portas 80 e 443 abertas no firewall
- Apache ou Nginx instalado

## Passo 1: Atualizar DuckDNS

Certifique-se de que seu domínio DuckDNS aponta para o IP correto do servidor:

```bash
# Atualize o IP do DuckDNS (substitua SEU_TOKEN pelo token do DuckDNS)
curl "https://www.duckdns.org/update?domains=tstjoinenglish&token=SEU_TOKEN&ip="
```

## Passo 2: Instalar Certbot

### Para Ubuntu/Debian com Apache:
```bash
sudo apt update
sudo apt install certbot python3-certbot-apache -y
```

### Para Ubuntu/Debian com Nginx:
```bash
sudo apt update
sudo apt install certbot python3-certbot-nginx -y
```

## Passo 3: Configurar o Servidor Web

### Opção A: Apache

1. Crie o arquivo de configuração do VirtualHost:

```bash
sudo nano /etc/apache2/sites-available/tstjoinenglish.conf
```

2. Adicione a configuração:

```apache
<VirtualHost *:80>
    ServerName tstjoinenglish.duckdns.org
    ServerAdmin webmaster@localhost
    
    DocumentRoot /var/www/curso/public
    
    <Directory /var/www/curso/public>
        AllowOverride All
        Require all granted
        Options -Indexes +FollowSymLinks
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/curso_error.log
    CustomLog ${APACHE_LOG_DIR}/curso_access.log combined
</VirtualHost>
```

3. Habilite o site e módulos necessários:

```bash
sudo a2enmod rewrite
sudo a2ensite tstjoinenglish.conf
sudo systemctl restart apache2
```

### Opção B: Nginx

1. Crie o arquivo de configuração:

```bash
sudo nano /etc/nginx/sites-available/tstjoinenglish
```

2. Adicione a configuração:

```nginx
server {
    listen 80;
    listen [::]:80;
    
    server_name tstjoinenglish.duckdns.org;
    root /var/www/curso/public;
    
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    
    index index.php;
    
    charset utf-8;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }
    
    error_page 404 /index.php;
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

3. Habilite o site:

```bash
sudo ln -s /etc/nginx/sites-available/tstjoinenglish /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

## Passo 4: Obter Certificado SSL

### Para Apache:
```bash
sudo certbot --apache -d tstjoinenglish.duckdns.org
```

### Para Nginx:
```bash
sudo certbot --nginx -d tstjoinenglish.duckdns.org
```

Durante o processo:
- Forneça um email válido
- Aceite os termos de serviço
- Escolha redirecionar HTTP para HTTPS (opção 2)

## Passo 5: Configurar Renovação Automática

O Certbot configura automaticamente a renovação. Teste com:

```bash
sudo certbot renew --dry-run
```

Para forçar renovação manual (se necessário):
```bash
sudo certbot renew
```

## Passo 6: Atualizar Laravel

Atualize o arquivo `.env`:

```env
APP_URL=https://tstjoinenglish.duckdns.org
```

Se necessário, force HTTPS no Laravel editando `app/Providers/AppServiceProvider.php`:

```php
public function boot(): void
{
    if ($this->app->environment('production')) {
        \URL::forceScheme('https');
    }
}
```

## Passo 7: Configurar Permissões

Certifique-se de que as permissões estão corretas:

```bash
cd /var/www/curso
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

## Passo 8: Limpar Cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize
```

## Verificação

1. Acesse: `https://tstjoinenglish.duckdns.org`
2. Verifique o cadeado SSL no navegador
3. Teste em: https://www.ssllabs.com/ssltest/

## Configuração do Firewall (UFW)

```bash
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
sudo ufw status
```

## Troubleshooting

### Erro "Certificate validation failed"
- Certifique-se de que o DuckDNS está apontando para o IP correto
- Verifique se as portas 80 e 443 estão abertas

### Erro "Unable to find a virtual host"
- Verifique a configuração do Apache/Nginx
- Certifique-se de que o ServerName/server_name está correto

### Mixed Content (conteúdo misto)
Adicione no `.env`:
```env
ASSET_URL=https://tstjoinenglish.duckdns.org
```

### Redirecionamento infinito
Adicione no `.env`:
```env
FORCE_HTTPS=true
```

E no arquivo `public/.htaccess` (se usar Apache):
```apache
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

## Manutenção

- Certificados Let's Encrypt são válidos por 90 dias
- A renovação automática ocorre via cron
- Verifique logs em `/var/log/letsencrypt/`

## Notas Importantes

1. **Backup**: Faça backup do servidor antes de fazer mudanças
2. **DNS**: Aguarde propagação DNS (pode levar alguns minutos)
3. **Token DuckDNS**: Mantenha seu token em segurança
4. **Renovação**: Monitore a renovação automática dos certificados

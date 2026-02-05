# Checklist de Instalação SSL

## Antes de Começar

- [ ] Servidor Linux configurado (Ubuntu/Debian)
- [ ] Projeto Laravel no servidor (em `/var/www/curso` ou outro caminho)
- [ ] Apache ou Nginx instalado e funcionando
- [ ] PHP instalado (versão 8.1+)
- [ ] MySQL configurado e rodando
- [ ] Token do DuckDNS em mãos

## Passo a Passo

### 1. Configurar DuckDNS

- [ ] Acessar https://www.duckdns.org
- [ ] Fazer login
- [ ] Configurar domínio: `tstjoinenglish`
- [ ] Anotar o token do DuckDNS
- [ ] Atualizar IP do servidor:
  ```bash
  curl "https://www.duckdns.org/update?domains=tstjoinenglish&token=SEU_TOKEN&ip="
  ```
- [ ] Verificar que o DNS está funcionando:
  ```bash
  nslookup tstjoinenglish.duckdns.org
  ```

### 2. Preparar o Servidor

- [ ] Fazer upload do projeto para o servidor
- [ ] Configurar permissões:
  ```bash
  sudo chown -R www-data:www-data /var/www/curso
  sudo chmod -R 775 /var/www/curso/storage
  sudo chmod -R 775 /var/www/curso/bootstrap/cache
  ```

### 3. Instalar Certificado SSL

**Opção A - Script Automático:**

- [ ] Fazer upload da pasta `server-configs/` para o servidor
- [ ] Editar o email no arquivo `install-ssl.sh`
- [ ] Executar:
  ```bash
  cd /caminho/para/server-configs
  sudo bash install-ssl.sh
  ```

**Opção B - Manual:**

- [ ] Copiar arquivo de configuração do servidor:
  - Apache: `server-configs/apache-tstjoinenglish.conf` → `/etc/apache2/sites-available/`
  - Nginx: `server-configs/nginx-tstjoinenglish.conf` → `/etc/nginx/sites-available/`

- [ ] Instalar Certbot:
  ```bash
  # Para Apache
  sudo apt install certbot python3-certbot-apache -y
  
  # Para Nginx
  sudo apt install certbot python3-certbot-nginx -y
  ```

- [ ] Obter certificado:
  ```bash
  # Para Apache
  sudo certbot --apache -d tstjoinenglish.duckdns.org
  
  # Para Nginx
  sudo certbot --nginx -d tstjoinenglish.duckdns.org
  ```

### 4. Configurar Laravel

- [ ] Atualizar `.env`:
  ```env
  APP_URL=https://tstjoinenglish.duckdns.org
  APP_ENV=production
  APP_DEBUG=false
  ```

- [ ] Limpar cache:
  ```bash
  php artisan config:clear
  php artisan cache:clear
  php artisan route:clear
  php artisan view:clear
  php artisan optimize
  ```

### 5. Configurar Firewall

- [ ] Abrir portas:
  ```bash
  sudo ufw allow 80/tcp
  sudo ufw allow 443/tcp
  sudo ufw enable
  ```

### 6. Testar

- [ ] Acessar: `https://tstjoinenglish.duckdns.org`
- [ ] Verificar cadeado SSL no navegador
- [ ] Testar redirecionamento HTTP → HTTPS
- [ ] Verificar todas as páginas do site
- [ ] Testar em diferentes navegadores
- [ ] Verificar console do navegador (F12) para erros de conteúdo misto

### 7. Monitoramento

- [ ] Testar renovação automática:
  ```bash
  sudo certbot renew --dry-run
  ```

- [ ] Adicionar lembrete no calendário para verificar renovação em 60 dias

## Troubleshooting

### Problema: Certificado não é obtido
- [ ] Verificar que o DuckDNS aponta para o IP correto
- [ ] Verificar que as portas 80 e 443 estão abertas
- [ ] Verificar logs: `sudo tail -f /var/log/letsencrypt/letsencrypt.log`

### Problema: Conteúdo misto (mixed content)
- [ ] Verificar `APP_URL` no `.env`
- [ ] Verificar que `ForceHttps` está ativo
- [ ] Inspecionar elementos com HTTP no console do navegador
- [ ] Atualizar URLs hardcoded no código

### Problema: Redirecionamento infinito
- [ ] Verificar configuração do proxy reverso (se usar)
- [ ] Verificar headers `X-Forwarded-Proto`
- [ ] Desabilitar temporariamente middleware `ForceHttps`

### Problema: Site não carrega
- [ ] Verificar logs do servidor:
  ```bash
  # Apache
  sudo tail -f /var/log/apache2/error.log
  
  # Nginx
  sudo tail -f /var/log/nginx/error.log
  ```
- [ ] Verificar logs do Laravel:
  ```bash
  tail -f storage/logs/laravel.log
  ```

## Comandos Úteis

```bash
# Verificar status do certificado
sudo certbot certificates

# Renovar manualmente
sudo certbot renew

# Verificar status do Apache
sudo systemctl status apache2

# Verificar status do Nginx
sudo systemctl status nginx

# Reiniciar servidor web
sudo systemctl restart apache2  # ou nginx

# Verificar configuração
apache2ctl configtest  # Apache
nginx -t              # Nginx
```

## Recursos Adicionais

- Guia completo: `SSL_SETUP.md`
- Teste SSL: https://www.ssllabs.com/ssltest/
- Documentação Let's Encrypt: https://letsencrypt.org/docs/
- DuckDNS: https://www.duckdns.org/spec.jsp

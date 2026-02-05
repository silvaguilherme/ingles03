# Guia Rápido: Configuração SSL com Porta 8088

## Sua Configuração Atual

- **Porta da aplicação**: 8088
- **Domínio**: tstjoinenglish.duckdns.org
- **Servidor web**: Atuará como proxy reverso (porta 80/443 → 8088)

## Como Funciona

```
Internet (443 HTTPS)
    ↓
Nginx/Apache (porta 80/443)
    ↓ [Proxy Reverso]
Laravel (porta 8088)
```

## Passos Rápidos

### 1. Preparar a Aplicação

Sua aplicação precisa estar rodando na porta 8088. Escolha uma opção:

**Opção A - Usando `php artisan serve`:**
```bash
cd /var/www/curso
php artisan serve --host=0.0.0.0 --port=8088
```

**Opção B - Usando o script fornecido:**
```bash
bash server-configs/start-app.sh
```

**Opção C - Usando systemd (recomendado para produção):**
```bash
# Copiar arquivo de serviço
sudo cp server-configs/laravel-curso.service /etc/systemd/system/

# Ajustar caminho se necessário
sudo nano /etc/systemd/system/laravel-curso.service

# Habilitar e iniciar
sudo systemctl daemon-reload
sudo systemctl enable laravel-curso
sudo systemctl start laravel-curso

# Verificar status
sudo systemctl status laravel-curso
```

### 2. Instalar SSL

Execute o script de instalação:
```bash
cd /caminho/para/server-configs
sudo bash install-ssl.sh
```

O script irá:
- ✅ Detectar seu servidor web (Apache/Nginx)
- ✅ Configurar proxy reverso para porta 8088
- ✅ Instalar Certbot
- ✅ Obter certificado SSL
- ✅ Configurar redirecionamento HTTPS

### 3. Verificar

Após a instalação:
```bash
# Verificar se a aplicação está rodando
curl http://localhost:8088

# Verificar se o proxy está funcionando
curl http://tstjoinenglish.duckdns.org

# Verificar HTTPS
curl https://tstjoinenglish.duckdns.org
```

## Configuração Manual (Alternativa)

Se preferir configurar manualmente:

### Para Apache:

```bash
# Copiar configuração
sudo cp server-configs/apache-tstjoinenglish.conf /etc/apache2/sites-available/

# Habilitar módulos de proxy
sudo a2enmod proxy
sudo a2enmod proxy_http
sudo a2enmod ssl
sudo a2enmod headers

# Habilitar site
sudo a2ensite tstjoinenglish
sudo systemctl restart apache2

# Obter certificado
sudo certbot --apache -d tstjoinenglish.duckdns.org
```

### Para Nginx:

```bash
# Copiar configuração
sudo cp server-configs/nginx-tstjoinenglish.conf /etc/nginx/sites-available/tstjoinenglish
sudo ln -s /etc/nginx/sites-available/tstjoinenglish /etc/nginx/sites-enabled/

# Testar e reiniciar
sudo nginx -t
sudo systemctl restart nginx

# Obter certificado
sudo certbot --nginx -d tstjoinenglish.duckdns.org
```

## Gerenciar o Serviço Laravel

### Comandos Úteis do Systemd:

```bash
# Iniciar
sudo systemctl start laravel-curso

# Parar
sudo systemctl stop laravel-curso

# Reiniciar
sudo systemctl restart laravel-curso

# Status
sudo systemctl status laravel-curso

# Ver logs
sudo journalctl -u laravel-curso -f

# Ver logs da aplicação
tail -f /var/www/curso/storage/logs/laravel-service.log
```

## Troubleshooting

### Aplicação não inicia na porta 8088

```bash
# Verificar se a porta está em uso
sudo lsof -i :8088

# Matar processo se necessário
sudo kill -9 $(lsof -t -i:8088)

# Verificar logs
tail -f /var/www/curso/storage/logs/laravel.log
```

### Erro 502 Bad Gateway

```bash
# Verificar se a aplicação está rodando
curl http://localhost:8088

# Se não estiver, inicie o serviço
sudo systemctl start laravel-curso
```

### SSL não funciona

```bash
# Verificar portas abertas
sudo ufw status
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Verificar DuckDNS
nslookup tstjoinenglish.duckdns.org

# Ver logs do Certbot
sudo tail -f /var/log/letsencrypt/letsencrypt.log
```

### Headers de Proxy

Se tiver problemas com redirecionamentos, adicione ao `.env`:

```env
TRUSTED_PROXIES=*
APP_URL=https://tstjoinenglish.duckdns.org
```

E crie/edite `app/Http/Middleware/TrustProxies.php`:

```php
protected $proxies = '*';

protected $headers = Request::HEADER_X_FORWARDED_ALL;
```

## Arquivos Criados

- `server-configs/apache-tstjoinenglish.conf` - Config Apache com proxy
- `server-configs/nginx-tstjoinenglish.conf` - Config Nginx com proxy  
- `server-configs/start-app.sh` - Script para iniciar aplicação
- `server-configs/laravel-curso.service` - Service do systemd
- `server-configs/install-ssl.sh` - Script de instalação automática

## Notas Importantes

1. **Sempre use systemd em produção** ao invés de `php artisan serve`
2. **Portas 80 e 443** devem estar abertas no firewall
3. **Porta 8088** só precisa ser acessível localmente
4. **Logs** ficam em `/var/www/curso/storage/logs/`
5. **Certificado** renova automaticamente a cada 90 dias

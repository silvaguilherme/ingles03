# Guia de Segurança: Headers HTTP e Configuração Nginx

## Avaliação de Segurança

### Headers Implementados ✅

| Header | Configuração Atual | Nível de Segurança |
|--------|-------------------|-------------------|
| **HSTS** | `max-age=31536000; includeSubDomains; preload` | ✅ **Excelente** |
| **X-Frame-Options** | `DENY` | ✅ **Excelente** |
| **X-Content-Type-Options** | `nosniff` | ✅ **Excelente** |
| **X-XSS-Protection** | `1; mode=block` | ✅ **Bom** (legado) |
| **Referrer-Policy** | `strict-origin-when-cross-origin` | ✅ **Excelente** |
| **Content-Security-Policy** | Configurado | ✅ **Excelente** |
| **Permissions-Policy** | Recursos desabilitados | ✅ **Excelente** |

### Melhorias Implementadas 🔒

#### 1. **HSTS (HTTP Strict Transport Security)**
```nginx
add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;
```
- **O que faz:** Força o navegador a sempre usar HTTPS
- **Benefício:** Previne ataques man-in-the-middle e downgrade
- **Recomendação:** Após 6 meses de testes, adicione ao [HSTS Preload List](https://hstspreload.org/)

#### 2. **Content Security Policy (CSP)**
```nginx
add_header Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net; ..." always;
```
- **O que faz:** Controla quais recursos podem ser carregados
- **Benefício:** Previne XSS e injeção de código malicioso
- **Nota:** Ajuste conforme os CDNs que você usa

#### 3. **Permissions-Policy**
```nginx
add_header Permissions-Policy "geolocation=(), microphone=(), camera=()..." always;
```
- **O que faz:** Desabilita APIs do browser não necessárias
- **Benefício:** Reduz superfície de ataque

#### 4. **SSL/TLS Otimizado**
```nginx
ssl_protocols TLSv1.2 TLSv1.3;
ssl_session_cache shared:SSL:10m;
ssl_stapling on;
```
- **O que faz:** Usa apenas protocolos modernos e seguros
- **Benefício:** Proteção contra ataques de downgrade, melhor performance

#### 5. **Proteção contra File Upload Exploits**
```nginx
location ~ \.php$ {
    try_files $uri =404;  # Previne execução de PHP inexistente
}
```

#### 6. **Bloqueio de Arquivos Sensíveis**
```nginx
location ~* \.(env|log)$ {
    deny all;
}
```
- **Benefício:** Previne vazamento de credenciais

## Como Aplicar

### Opção 1: Atualizar arquivo existente

```bash
# Backup
sudo cp /etc/nginx/conf.d/curso.conf /etc/nginx/conf.d/curso.conf.backup

# No repositório
cd /var/www/ingles03
git pull

# Copiar nova configuração
sudo cp server-configs/nginx-secure.conf /etc/nginx/conf.d/curso.conf

# Testar
sudo nginx -t

# Aplicar
sudo systemctl reload nginx
```

### Opção 2: Instalar módulo headers-more (para limpar headers)

```bash
# Instalar
sudo dnf install nginx-mod-http-headers-more

# Reiniciar nginx
sudo systemctl restart nginx
```

**Nota:** Se o módulo não estiver disponível, comente as linhas `more_clear_headers` no arquivo.

## Teste de Segurança

### 1. Testar Headers Online

Acesse estes sites e teste seu domínio:
- **Security Headers:** https://securityheaders.com
- **SSL Labs:** https://www.ssllabs.com/ssltest/
- **Mozilla Observatory:** https://observatory.mozilla.org/

### 2. Testar via curl

```bash
curl -I https://tstjoinenglish.duckdns.org:8088
```

Você deve ver headers como:
```
Strict-Transport-Security: max-age=31536000; includeSubDomains; preload
X-Frame-Options: DENY
X-Content-Type-Options: nosniff
Content-Security-Policy: ...
```

### 3. Teste de XSS

```bash
# Tentar injetar script (deve ser bloqueado pelo CSP)
curl "https://tstjoinenglish.duckdns.org:8088/?test=<script>alert('xss')</script>"
```

## Ajustes para CSP (Content Security Policy)

A CSP atual pode ser muito restritiva. Ajuste conforme necessário:

### Se usar CDNs específicos:
```nginx
# Adicione os domínios permitidos
script-src 'self' https://cdn.exemplo.com;
style-src 'self' https://cdn.exemplo.com;
```

### Se tiver problemas com inline scripts:
```nginx
# Use nonces ou hashes (mais seguro que 'unsafe-inline')
script-src 'self' 'nonce-{random}';
```

### Para desenvolvimento (menos restritivo):
```nginx
# APENAS EM DESENVOLVIMENTO
script-src 'self' 'unsafe-inline' 'unsafe-eval';
```

## Configurações Adicionais Recomendadas

### 1. Rate Limiting (Prevenir DDoS/Brute Force)

Adicione ao bloco `http` em `/etc/nginx/nginx.conf`:

```nginx
# Limitar requisições por IP
limit_req_zone $binary_remote_addr zone=general:10m rate=10r/s;
limit_req_zone $binary_remote_addr zone=login:10m rate=5r/m;

# No server block
location / {
    limit_req zone=general burst=20 nodelay;
}

location /login {
    limit_req zone=login burst=2 nodelay;
}
```

### 2. Fail2ban (Bloquear IPs maliciosos)

```bash
sudo dnf install fail2ban
sudo systemctl enable fail2ban
sudo systemctl start fail2ban
```

Configuração `/etc/fail2ban/jail.local`:
```ini
[nginx-limit-req]
enabled = true
filter = nginx-limit-req
logpath = /var/log/nginx/*error.log
maxretry = 5
findtime = 600
bantime = 3600
```

### 3. ModSecurity (Web Application Firewall)

```bash
sudo dnf install nginx-mod-modsecurity
```

### 4. Configurar log detalhado

```nginx
# Log format com mais informações
log_format security '$remote_addr - $remote_user [$time_local] '
                   '"$request" $status $body_bytes_sent '
                   '"$http_referer" "$http_user_agent" '
                   '$request_time $upstream_response_time';

access_log /var/log/nginx/security.log security;
```

## Monitoramento

### Verificar logs de segurança

```bash
# Tentativas de acesso a arquivos sensíveis
sudo grep "\.env" /var/log/nginx/error.log

# Erros 403 (acessos negados)
sudo grep " 403 " /var/log/nginx/access.log

# Requisições suspeitas
sudo grep -E "(union|select|script|alert)" /var/log/nginx/access.log
```

## Checklist de Segurança ✅

- [x] HTTPS obrigatório (HSTS)
- [x] Headers de segurança configurados
- [x] CSP implementado
- [x] SSL/TLS moderno (TLS 1.2+)
- [x] Arquivos sensíveis bloqueados
- [x] Server tokens desabilitado
- [x] Proteção contra clickjacking
- [ ] Rate limiting configurado
- [ ] Fail2ban instalado
- [ ] Logs monitorados
- [ ] Backup automático configurado
- [ ] WAF (ModSecurity) - opcional

## Próximos Passos

1. ✅ Aplicar configuração segura
2. 🔍 Testar em https://securityheaders.com
3. 🔍 Testar SSL em https://ssllabs.com
4. 📊 Monitorar logs por 1 semana
5. 🔧 Ajustar CSP conforme necessário
6. 🛡️ Configurar rate limiting
7. 🚨 Instalar fail2ban
8. 📈 Configurar monitoramento (opcional: Prometheus/Grafana)

## Nota Importante sobre CSP

A política CSP atual permite `'unsafe-inline'` e `'unsafe-eval'` para compatibilidade com Laravel e frameworks JavaScript modernos. 

Para máxima segurança:
1. Remova `'unsafe-inline'` e `'unsafe-eval'`
2. Use nonces para scripts inline
3. Compile assets com Vite/Laravel Mix
4. Teste extensivamente!

## Recursos

- [OWASP Security Headers](https://owasp.org/www-project-secure-headers/)
- [Mozilla Web Security Guidelines](https://infosec.mozilla.org/guidelines/web_security)
- [CSP Evaluator](https://csp-evaluator.withgoogle.com/)

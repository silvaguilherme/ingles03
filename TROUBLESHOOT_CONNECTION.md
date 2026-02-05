# Solução Rápida: ERR_CONNECTION_REFUSED

## ⚠️ Problema Identificado

Você criou a nova configuração `nginx-secure.conf` mas **NÃO a aplicou no servidor**.

O servidor ainda está usando a configuração antiga e pode ter parado.

## 🔧 Solução Rápida (Execute no Servidor)

### Opção 1: Manter configuração atual (mais seguro)

```bash
# Conectar ao servidor via SSH
ssh seu-usuario@tstjoinenglish.duckdns.org

# Verificar status
sudo systemctl status nginx

# Se estiver parado, iniciar
sudo systemctl start nginx

# Se houver erro, ver logs
sudo journalctl -u nginx -n 50 --no-pager
```

### Opção 2: Aplicar nova configuração segura

```bash
# No servidor
cd /var/www/ingles03
git pull

# Backup da configuração atual
sudo cp /etc/nginx/conf.d/curso.conf /etc/nginx/conf.d/curso.conf.backup-$(date +%Y%m%d)

# Aplicar nova configuração
sudo cp server-configs/nginx-secure.conf /etc/nginx/conf.d/curso.conf

# IMPORTANTE: Remover linhas do módulo headers-more se não instalado
sudo sed -i '/more_clear_headers/d' /etc/nginx/conf.d/curso.conf

# Testar configuração
sudo nginx -t

# Se OK, recarregar
sudo systemctl reload nginx

# Se ERRO, restaurar backup
# sudo cp /etc/nginx/conf.d/curso.conf.backup-YYYYMMDD /etc/nginx/conf.d/curso.conf
# sudo systemctl restart nginx
```

## 🔍 Diagnóstico Completo

```bash
# Executar script de diagnóstico
cd /var/www/ingles03
chmod +x server-configs/diagnose-connection.sh
sudo ./server-configs/diagnose-connection.sh
```

## 🚨 Problemas Comuns

### 1. Nginx parado
```bash
sudo systemctl start nginx
sudo systemctl enable nginx
```

### 2. Erro de sintaxe na configuração
```bash
# Ver erro exato
sudo nginx -t

# Restaurar backup
sudo cp /etc/nginx/conf.d/curso.conf.backup /etc/nginx/conf.d/curso.conf
sudo systemctl restart nginx
```

### 3. Módulo headers-more não instalado
A nova configuração usa `more_clear_headers` que requer módulo extra.

**Solução:**
```bash
# Remover linhas que usam o módulo
sudo sed -i '/more_clear_headers/d' /etc/nginx/conf.d/curso.conf
sudo nginx -t
sudo systemctl reload nginx
```

### 4. Porta 8088 bloqueada
```bash
# Verificar firewall
sudo firewall-cmd --list-ports

# Se 8088/tcp não aparecer
sudo firewall-cmd --permanent --add-port=8088/tcp
sudo firewall-cmd --reload
```

### 5. Processo Nginx travado
```bash
# Matar processos
sudo pkill -9 nginx

# Reiniciar
sudo systemctl start nginx
```

## ✅ Validação

Após corrigir:

```bash
# No servidor
curl -I http://localhost:8088

# No seu computador
curl -I https://tstjoinenglish.duckdns.org:8088
```

Deve retornar:
```
HTTP/1.1 200 OK
Server: nginx
```

## 📞 Se Precisar de Ajuda

Me envie a saída de:
```bash
sudo systemctl status nginx
sudo nginx -t
sudo tail -n 30 /var/log/nginx/error.log
```

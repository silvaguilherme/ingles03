#!/bin/bash

# Script de Diagnóstico para ERR_CONNECTION_REFUSED
# Execute no servidor Oracle Linux

echo "====================================="
echo "DIAGNÓSTICO DE CONEXÃO RECUSADA"
echo "====================================="
echo ""

# 1. Verificar se Nginx está rodando
echo "1️⃣ Status do Nginx:"
echo "-----------------------------------"
sudo systemctl status nginx --no-pager -l
echo ""

# 2. Verificar se está escutando na porta 8088
echo "2️⃣ Portas em escuta:"
echo "-----------------------------------"
sudo netstat -tlnp | grep :8088 || sudo ss -tlnp | grep :8088
echo ""

# 3. Verificar erros de configuração
echo "3️⃣ Teste de configuração Nginx:"
echo "-----------------------------------"
sudo nginx -t
echo ""

# 4. Verificar logs de erro recentes
echo "4️⃣ Últimos erros do Nginx:"
echo "-----------------------------------"
sudo tail -n 50 /var/log/nginx/error.log
echo ""

# 5. Verificar firewall
echo "5️⃣ Regras de Firewall (porta 8088):"
echo "-----------------------------------"
sudo firewall-cmd --list-all | grep -A 10 "ports:"
echo ""

# 6. Verificar processos Nginx
echo "6️⃣ Processos Nginx:"
echo "-----------------------------------"
ps aux | grep nginx | grep -v grep
echo ""

# 7. Verificar configuração ativa
echo "7️⃣ Arquivo de configuração ativo:"
echo "-----------------------------------"
ls -lh /etc/nginx/conf.d/curso.conf
echo ""

# 8. Testar conexão local
echo "8️⃣ Teste de conexão local:"
echo "-----------------------------------"
curl -I http://localhost:8088 2>&1
echo ""

echo "====================================="
echo "COMANDOS DE CORREÇÃO RÁPIDA"
echo "====================================="
echo ""
echo "Se Nginx estiver parado:"
echo "  sudo systemctl start nginx"
echo ""
echo "Se houver erro de configuração:"
echo "  sudo cp /etc/nginx/conf.d/curso.conf /etc/nginx/conf.d/curso.conf.backup"
echo "  sudo cp /etc/nginx/conf.d/curso.conf.backup.original /etc/nginx/conf.d/curso.conf"
echo "  sudo nginx -t"
echo "  sudo systemctl restart nginx"
echo ""
echo "Se firewall bloquear:"
echo "  sudo firewall-cmd --permanent --add-port=8088/tcp"
echo "  sudo firewall-cmd --reload"
echo ""

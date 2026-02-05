#!/bin/bash

# Script para iniciar a aplicação Laravel na porta 8088
# Execute com: bash start-app.sh

set -e

# Cores para output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

PROJECT_PATH="/var/www/curso"
PORT=8088

echo -e "${YELLOW}Iniciando aplicação Laravel na porta $PORT...${NC}"

# Verificar se o projeto existe
if [ ! -d "$PROJECT_PATH" ]; then
    echo "Erro: Projeto não encontrado em $PROJECT_PATH"
    exit 1
fi

cd "$PROJECT_PATH"

# Verificar se a porta já está em uso
if lsof -Pi :$PORT -sTCP:LISTEN -t >/dev/null ; then
    echo -e "${YELLOW}Porta $PORT já está em uso. Parando processo...${NC}"
    PID=$(lsof -t -i:$PORT)
    kill -9 $PID 2>/dev/null || true
    sleep 2
fi

# Limpar cache
echo -e "${YELLOW}Limpando cache...${NC}"
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Otimizar
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Iniciar servidor
echo -e "${GREEN}Iniciando servidor em http://localhost:$PORT${NC}"
echo -e "${YELLOW}Pressione Ctrl+C para parar${NC}"
echo ""

# Usar nohup para rodar em background (opcional)
# nohup php artisan serve --host=0.0.0.0 --port=$PORT > storage/logs/server.log 2>&1 &
# echo "Servidor rodando em background. PID: $!"
# echo "Logs em: storage/logs/server.log"

# Ou rodar em foreground (padrão)
php artisan serve --host=0.0.0.0 --port=$PORT

#!/bin/bash

# Script de Setup - Sistema Anki da Plataforma

echo "╔════════════════════════════════════════════╗"
echo "║  Setup do Sistema Anki da Plataforma      ║"
echo "╚════════════════════════════════════════════╝"
echo ""

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# 1. Executar migrations
echo -e "${YELLOW}[1/4]${NC} Executando migrations..."
php artisan migrate --table=anki_decks
php artisan migrate --table=anki_cards
php artisan migrate --table=anki_card_progress

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓${NC} Migrations executadas com sucesso!"
else
    echo -e "${RED}✗${NC} Erro ao executar migrations"
    exit 1
fi
echo ""

# 2. Criar diretório de mídia
echo -e "${YELLOW}[2/4]${NC} Criando diretório de mídia..."
mkdir -p storage/anki-media
chmod 755 storage/anki-media

if [ -d "storage/anki-media" ]; then
    echo -e "${GREEN}✓${NC} Diretório criado!"
else
    echo -e "${RED}✗${NC} Erro ao criar diretório"
    exit 1
fi
echo ""

# 3. Criar symbolic link para acesso público
echo -e "${YELLOW}[3/4]${NC} Criando symbolic link..."
php artisan storage:link

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓${NC} Symbolic link criado!"
else
    echo -e "${RED}✗${NC} Erro ao criar symbolic link"
fi
echo ""

# 4. Cache clear
echo -e "${YELLOW}[4/4]${NC} Limpando cache..."
php artisan cache:clear
php artisan config:clear

echo -e "${GREEN}✓${NC} Cache limpo!"
echo ""

echo "╔════════════════════════════════════════════╗"
echo "║  Setup Concluído! 🎉                       ║"
echo "╠════════════════════════════════════════════╣"
echo "║  Próximos passos:                           ║"
echo "║  1. Organize seus arquivos APKG            ║"
echo "║  2. Execute: php artisan anki:import       ║"
echo "║  3. Acesse: /anki no seu navegador         ║"
echo "╚════════════════════════════════════════════╝"

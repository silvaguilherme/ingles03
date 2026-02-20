#!/bin/bash

# Script para preparar estrutura de pastas para importação automática de APKG
# Executar na pasta root do projeto

echo "╔════════════════════════════════════════════════════════════════╗"
echo "║  Preparador de Estrutura APKG                                 ║"
echo "║  Este script cria a estrutura necessária para importação       ║"
echo "╚════════════════════════════════════════════════════════════════╝"
echo ""

# Verificar argumentos
if [ $# -eq 0 ]; then
    echo "Uso: bash prepare-anki-structure.sh <caminho-dos-apkg>"
    echo ""
    echo "Exemplo:"
    echo "  bash prepare-anki-structure.sh ~/Downloads/meus-decks"
    echo ""
    echo "Estrutura esperada em ~/Downloads/meus-decks:"
    echo "  vocabulario.apkg"
    echo "  gramatica.apkg"
    echo "  conversacao.apkg"
    echo ""
    echo "Será criado em storage/apkg-import/:"
    echo "  1/"
    echo "  ├── vocabulario.apkg"
    echo "  ├── gramatica.apkg"
    echo "  └── conversacao.apkg"
    echo "  2/"
    echo "  ├── ... (próximos submodulos)"
    exit 1
fi

SOURCE_DIR="$1"

if [ ! -d "$SOURCE_DIR" ]; then
    echo "❌ Erro: Diretório não existe: $SOURCE_DIR"
    exit 1
fi

# Criar diretório de destino
DEST_DIR="storage/apkg-import"
mkdir -p "$DEST_DIR"

echo "📁 Organisando arquivos APKG..."
echo ""

APKG_COUNT=$(find "$SOURCE_DIR" -maxdepth 1 -name "*.apkg" | wc -l)

if [ $APKG_COUNT -eq 0 ]; then
    echo "❌ Nenhum arquivo .apkg encontrado em $SOURCE_DIR"
    exit 1
fi

# Pedir ID do submodulo
read -p "🔢 Qual é o ID do submodulo? (1-999): " SUBMODULE_ID

if ! [[ $SUBMODULE_ID =~ ^[0-9]+$ ]]; then
    echo "❌ ID inválido. Deve ser um número."
    exit 1
fi

# Criar pasta do submodulo
mkdir -p "$DEST_DIR/$SUBMODULE_ID"

# Copiar arquivos
echo "📋 Copiando $APKG_COUNT arquivos..."
cp "$SOURCE_DIR"/*.apkg "$DEST_DIR/$SUBMODULE_ID/" 2>/dev/null

if [ $? -eq 0 ]; then
    echo "✅ Arquivos copiados com sucesso!"
    echo ""
    echo "📂 Estrutura criada:"
    ls -lh "$DEST_DIR/$SUBMODULE_ID/"
    echo ""
    echo "🚀 Para importar, execute:"
    echo "   php artisan anki:import --path=storage/apkg-import"
    echo ""
    echo "📊 Ou para importar apenas este submodulo:"
    echo "   php artisan anki:import --path=storage/apkg-import/$SUBMODULE_ID"
else
    echo "❌ Erro ao copiar arquivos"
    exit 1
fi

#!/bin/bash

# Script para atualizar projeto com mudanças locais
# Execute com: bash update-project.sh

echo "Atualizando projeto..."

# Verificar mudanças locais
if [[ -n $(git status -s) ]]; then
    echo "Mudanças locais detectadas. Fazendo stash..."
    git stash push -m "Auto stash antes do pull $(date +%Y-%m-%d_%H:%M:%S)"
    STASHED=true
else
    STASHED=false
fi

# Fazer pull
echo "Fazendo pull..."
git pull

# Reaplicar mudanças se foram guardadas
if [ "$STASHED" = true ]; then
    echo "Reaplicando mudanças locais..."
    git stash pop
    
    if [ $? -eq 0 ]; then
        echo "Mudanças reaplicadas com sucesso!"
    else
        echo "ATENÇÃO: Houve conflitos ao reaplicar as mudanças."
        echo "Resolva os conflitos manualmente."
        echo "Suas mudanças estão salvas no stash."
        echo "Para ver: git stash list"
        echo "Para descartar: git stash drop"
    fi
fi

echo "Atualização concluída!"

# 🎴 Guia Rápido de Setup - Sistema Anki

## Instalação Rápida (5 minutos)

### Passo 1: Executar Migrations
```bash
php artisan migrate
```

### Passo 2: Criar Pastas Necessárias
```bash
mkdir -p storage/anki-media
php artisan storage:link
```

### Passo 3: Limpar Cache
```bash
php artisan cache:clear
```

### Pronto! ✨

A plataforma de Anki já está funcionando!

---

## Importando seus Decks APKG

### Método 1: Interface Web (Recomendado)

1. Acesse um **Submodulo**
2. Clique no ícone **🎴 Anki** (embaixo do título do submodulo)
3. Escolha seu arquivo `.apkg`
4. Pronto! Os cards serão importados automaticamente

### Método 2: Linha de Comando

```bash
# Importar todos os APKG de uma pasta
php artisan anki:import --path=/caminho/para/seus/arquivos

# Exemplo:
php artisan anki:import --path=/home/usuario/meus-decks
```

### Estrutura de Pastas (Para importação automática)

```
meus-decks/
├── 1/           (ID do submodulo)
│   ├── ingles-basico.apkg
│   └── conversacao.apkg
├── 2/
│   ├── gramatica.apkg
│   └── expressoes.apkg
└── 3/
    └── vocabulario.apkg
```

---

## Usando a Plataforma

### 1. Acessar Dashboard
```
Menu → Anki
```

### 2. Estudar um Deck
- Clique em "Estudar Deck"
- Leia a pergunta
- Clique para virar e ver a resposta
- Escolha seu nível: ❌ | 😐 | 😊 | ✨

### 3. Acompanhar Progresso
- Dashboard mostra estatísticas gerais
- Cada deck mostra porcentagem de conclusão
- "Prontos para revisar" mostra quantos cards você pode estudar agora

---

## O que significam os botões?

| Botão | Próxima Revisão | Use quando... |
|-------|----------------|---------------|
| ❌ Errei | 10 min | Você não soube a resposta |
| 😐 Difícil | 1 dia | Acertou mas foi difícil |
| 😊 Médio | 3 dias | Respondeu corretamente |
| ✨ Fácil | 7+ dias | Muito fácil, dominado |

---

## Troubleshooting

### Erro na importação
```bash
# Verifique os logs
tail -f storage/logs/laravel.log

# Certifique-se que o arquivo é válido (deve ser .apkg)
# Um arquivo APKG é um ZIP com:
# - collection.anki2 (banco de dados SQLite)
# - media (arquivos de mídia)
```

### Mídia não aparece
```bash
# Recrie o symbolic link
php artisan storage:link

# Verifique permissões
chmod -R 755 storage/anki-media
```

### Cards não aparecem
- Certifique-se de que o arquivo foi importado
- Veja o Dashboard Anki para confirmar a importação
- Pode ser que todos os cards já foram estudados e estão agendados para depois

---

## Estrutura de Arquivos Criada

```
projeto/
├── app/
│   ├── Models/
│   │   ├── AnkiDeck.php
│   │   ├── AnkiCard.php
│   │   └── AnkiCardProgress.php
│   ├── Http/Controllers/
│   │   ├── AnkiController.php
│   │   └── AnkiDeckController.php
│   ├── Services/
│   │   └── AnkiImportService.php
│   └── Console/Commands/
│       └── ImportAnkiDecks.php
├── database/migrations/
│   ├── 2024_01_01_000001_create_anki_decks_table.php
│   ├── 2024_01_01_000002_create_anki_cards_table.php
│   └── 2024_01_01_000003_create_anki_card_progress_table.php
├── resources/views/anki/
│   ├── index.blade.php
│   ├── study.blade.php
│   ├── upload.blade.php
│   └── no-cards.blade.php
├── storage/
│   ├── anki-decks/       (Arquivos APKG uploadados)
│   └── anki-media/       (Imagens e áudio extraídos)
└── routes/
    └── web.php           (Rotas incluídas)
```

---

## Como Funciona o Algoritmo SM-2

O sistema usa o algoritmo **SM-2** do Anki, que é science-backed e muito eficiente:

1. **Novo Card**: Quando você estuda um card pela primeira vez
2. **Intervalo**: Aumenta cada vez que você acerta
3. **Facilidade**: Ajusta baseado em sua dificuldade
4. **Próxima Revisão**: Agendado automaticamente

**Fórmula simplificada:**
```
SE acertou:
  intervalo = intervalo × facilidade
  facilidade aumenta
SENÃO:
  intervalo = 1
  facilidade diminui
```

---

## Dicas Para Melhor Aprendizado

1. **Seja Consistente**: Estude todos os dias
2. **Ser Honesto**: Marque "Errei" quando errar, mesmo que tenha duvidado
3. **Revisar no Tempo Certo**: Não pule revisar cards vencidos
4. **Organizar por Tags**: Use tags no Anki para organizar tópicos

---

## Próximos Passos

- [ ] Executar migrations
- [ ] Importar seus decks APKG
- [ ] Estudar pelo menos um card
- [ ] Voltar amanhã para revisar
- [ ] Acompanhar progresso no dashboard

---

**Dúvidas?** Verifique [ANKI_DOCUMENTATION.md](./ANKI_DOCUMENTATION.md) para documentação mais detalhada.

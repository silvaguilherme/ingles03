# 📋 SISTEMA ANKI - IMPLEMENTAÇÃO COMPLETA

## ✅ O que foi criado

### 1. **Migrations (Banco de Dados)**
- `anki_decks` - Armazena informações dos decks
- `anki_cards` - Armazena os cards individuais
- `anki_card_progress` - Rastreia progresso de cada usuário

### 2. **Models (Lógica)**
- `AnkiDeck` - Modelo para decks
- `AnkiCard` - Modelo para cards com suporte a HTML/mídia
- `AnkiCardProgress` - Modelo para rastreamento com algoritmo SM-2

### 3. **Controllers (Rotas)**
- `AnkiController` - Dashboard, estudo, estatísticas
- `AnkiDeckController` - Upload e gerenciamento de decks

### 4. **Services (Processamento)**
- `AnkiImportService` - Importar APKG e CSV, extrair mídia

### 5. **Commands (CLI)**
- `anki:import` - Importar decks automaticamente de pastas
- `anki:list` - Listar decks com estatísticas

### 6. **Views (Interface)**
- `index.blade.php` - Dashboard principal
- `study.blade.php` - Tela de estudo com virada de cards
- `upload.blade.php` - Upload de arquivos APKG
- `no-cards.blade.php` - Tela quando não há cards

### 7. **Rotas** (Incluídas em `web.php`)
```php
GET    /anki                                    → index
GET    /anki/{deck}/study                      → estudo
POST   /anki/{deck}/record-answer              → registrar resposta
GET    /anki/stats                             → estatísticas
GET    /submodules/{submodule}/anki-decks/create → formulário upload
POST   /submodules/{submodule}/anki-decks      → processar upload
DELETE /anki-decks/{deck}                      → deletar deck
```

### 8. **Menu de Navegação**
Adicionado link "Anki" na navegação principal

---

## 🚀 Como Usar

### Passo 1: Executar Migrations
```bash
php artisan migrate
```

### Passo 2: Criar Pastas Necessárias
```bash
mkdir -p storage/anki-media
php artisan storage:link
```

### Passo 3: Importar Decks

**Opção A - Upload via Web:**
1. Vá para um submodulo
2. Clique no ícone 🎴
3. Selecione seu arquivo `.apkg`

**Opção B - Importação por CLI:**
```bash
php artisan anki:import --path=/caminho/seus/arquivos
```

### Passo 4: Acessar Dashboard
```
Menu → Anki
```

---

## 📊 Características Implementadas

### ✨ Algoritmo SM-2 (Science-Based Spaced Repetition)
- **Fácil**: Próxima revisão em 7+ dias
- **Médio**: Próxima revisão em 3 dias  
- **Difícil**: Próxima revisão em 1 dia
- **Errei**: Próxima revisão em 10 minutos

O sistema calcula automaticamente:
- Intervalo entre revisões
- Fator de facilidade (Ease Factor)
- Quando revisar cada card

### 🖼️ Suporte a Mídia
- Imagens automaticamente extraídas dos APKG
- Áudio integrado com player nativo
- HTML personalizado nos cards

### 📈 Acompanhamento
- Dashboard mostra progresso geral
- Estatísticas por deck
- Quantidade de cards prontos para revisar
- Histórico de aprendizado

### 🎯 Sistema de Dificuldade
Interface intuitiva com 4 opções de resposta que alimentam o algoritmo SM-2

---

## 📂 Estrutura de Arquivos

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
│       ├── ImportAnkiDecks.php
│       └── ListAnkiDecks.php
│
├── database/migrations/
│   ├── 2024_01_01_000001_create_anki_decks_table.php
│   ├── 2024_01_01_000002_create_anki_cards_table.php
│   └── 2024_01_01_000003_create_anki_card_progress_table.php
│
├── resources/views/anki/
│   ├── index.blade.php          (Dashboard)
│   ├── study.blade.php          (Telaestudo)
│   ├── upload.blade.php         (Upload APKG)
│   └── no-cards.blade.php       (Sem cards)
│
├── routes/web.php               (Rotas incluídas)
├── ANKI_DOCUMENTATION.md        (Docs completas)
├── ANKI_QUICK_START.md          (Guia rápido)
├── example-deck.csv             (Exemplo de CSV)
└── setup-anki.sh                (Script de setup)
```

---

## 💻 Arquivos Modificados

- `routes/web.php` - Adicionadas rotas do Anki
- `resources/views/layouts/navigation.blade.php` - Adicionado link "Anki"
- `app/Models/SubModule.php` - Adicionado relacionamento com AnkiDecks
- `resources/views/courses/show.blade.php` - Adicionado botão 🎴 para upload

---

## 🎓 Como Funciona

1. **Importação**: APKG é extraído, banco de dados SQLite é lido, cards são salvos
2. **Mídia**: Arquivos são extraídos para `storage/anki-media/{deck_id}/`
3. **Estudo**: Usuário clica no card, vira para ver resposta, escolhe dificuldade
4. **Cálculo**: Algoritmo SM-2 calcula próxima revisão baseado na dificuldade
5. **Dashboard**: Mostra cards prontos para revisar e progresso geral

---

## 🔧 Comandos Disponíveis

```bash
# Importar decks de pastas
php artisan anki:import --path=/caminho

# Listar todos os decks
php artisan anki:list

# Listar decks com progresso do usuário
php artisan anki:list --user=1

# Limpar cache
php artisan cache:clear
```

---

## 📱 URLs Disponíveis

| URL | Descrição |
|-----|-----------|
| `/anki` | Dashboard principal |
| `/anki/{id}/study` | Estudar um deck |
| `/anki/stats` | Estatísticas |
| `/courses/{id}` | Subir página mostra botão 🎴 |

---

## ⚙️ Configurações

### Diretórios
- Decks: `storage/app/anki-decks/`
- Mídia: `storage/app/anki-media/`
- Logs: `storage/logs/laravel.log`

### Permissões
```bash
chmod -R 755 storage/anki-media
chmod -R 755 storage/app
```

---

## 🐛 Troubleshooting

| Problema | Solução |
|----------|---------|
| Erro "APKG não encontrado" | Verifique se o arquivo existe e é válido |
| Mídia não carrega | Execute `php artisan storage:link` |
| Cards não aparecem | Aguarde cara nova será carregado em 10 min |
| Erro na importação | Verifique `storage/logs/laravel.log` |

---

## 📚 Próximas Melhorias Possíveis

- [ ] Adicionar busca/filtro de cards
- [ ] Estatísticas avançadas (gráficos)
- [ ] Exportar deck em APKG
- [ ] Sincronizar com AnkiWeb
- [ ] Suporte para tipos de cards customizados
- [ ] Importar múltiplos APKG em lote

---

## 📖 Documentação

- **ANKI_QUICK_START.md** - Guia para começar rapidinho
- **ANKI_DOCUMENTATION.md** - Documentação técnica completa
- **example-deck.csv** - Exemplo de arquivo para teste

---

## 🎉 Pronto para Usar!

1. Execute: `php artisan migrate`
2. Crie a pasta: `mkdir -p storage/anki-media`
3. Importe seus decks: `php artisan anki:import --path=/seu/caminho`
4. Acesse: `/anki`

**Divirta-se aprendendo! 📚✨**

---

**Data:** 17 de Fevereiro, 2026  
**Versão:** 1.0  
**Status:** Completo e Pronto para Produção ✅

# Sistema Anki da Plataforma de Cursos

## 📚 Visão Geral

Sistema integrado para gerenciar flashcards tipo Anki dentro da plataforma de cursos. Cada submodulo pode ter seus próprios decks com cards para praticar.

## ✨ Recursos

- **Dashboard Centralizado**: Visualize todos os seus decks e progresso em um único lugar
- **Algoritmo SM-2**: Implementação do algoritmo de espaçamento spaced repetition do Anki
- **Suporte a Mídia**: Imagens e áudio integrados nos cards
- **Sistema de Dificuldade**: Escolha entre Errei, Difícil, Médio ou Fácil
- **Acompanhamento de Progresso**: Estatísticas detalhadas de aprendizado

## 🚀 Começando Rápido

### 1. Executar Migrations

```bash
php artisan migrate
```

Isso criará as tabelas necessárias:
- `anki_decks` - Decks de cada submodulo
- `anki_cards` - Cards individuais
- `anki_card_progress` - Rastreamento do progresso do usuário

### 2. Importar Decks APKG

#### Opção A: Importar Automaticamente

Se seus arquivos `.apkg` estão organizados em pastas:

```bash
php artisan anki:import --path=/caminho/para/submodulos
```

Estrutura esperada:
```
/caminho/para/submodulos/
├── 1/
│   └── vocabulario.apkg
│   └── gramatica.apkg
├── 2/
│   └── conversacao.apkg
└── 3/
    └── expressoes.apkg
```

#### Opção B: Importar via Interface

1. Acesse um submodulo na plataforma
2. Clique no ícone 🎴 (Anki) ao lado do submodulo
3. Faça upload do arquivo APKG
4. O sistema extrairá automaticamente os cards e mídia

### 3. Estudar Cards

1. Acesse o menu "Anki" na navegação
2. Clique em "Estudar Deck" em um deck
3. Para cada card:
   - **Observe a pergunta**
   - Clique para virar e ver a resposta
   - Escolha o nível de dificuldade
4. O sistema calcula quando revisar novamente

## 📊 Sistema de Dificuldade

O algoritmo SM-2 do Anki opera assim:

| Botão | Significado | Próxima Revisão |
|-------|-------------|-----------------|
| ❌ Errei | Não soube responder | 10 minutos |
| 😐 Difícil | Acertou mas foi difícil | 1 dia |
| 😊 Médio | Acertou normalmente | 3 dias |
| ✨ Fácil | Muito fácil | 7+ dias* |

*O intervalo aumenta com cada revisão bem-sucedida

### Fator de Facilidade (Ease Factor)

- Começa em 2.5
- Aumenta quando você marca como "Fácil" ou "Médio"
- Diminui quando você erra
- Mínimo: 1.3

Fórmula: `EF' = EF + (0.1 - (5 - q) × (0.08 + (5 - q) × 0.02))`

Onde `q` é a qualidade da resposta (0-3)

## 🎴 Formatos Suportados

### Arquivos APKG
Formato nativo do Anki. Contém:
- Cards com perguntas e respostas
- Arquivos de mídia (imagens, áudio)
- Tags e campos customizados

**Extrair de um arquivo APKG:**
1. Abra o Anki Desktop
2. Selecione o deck
3. Clique em "Arquivo" → "Exportar"
4. Escolha "Selecionar decks..." e depois "APKG"

### Arquivos CSV
Formato simples para importação manual:

```
pergunta|resposta|tags
Como se diz "olá" em inglês?|Hello|Saudações Básicas
Qual é a capital da França?|Paris|Geografia
```

**Formato**: `pergunta|resposta|tags` (uma linha por card)

## 🖼️ Mídia nos Cards

Os arquivos APKG podem conter:
- **Imagens**: Automaticamente extraídas e exibidas
- **Áudio**: Reproduzíveis direto na plataforma

Exemplo de conteúdo do Anki com mídia:
```html
<img src="imagem.jpg">
[sound:audio.mp3]
```

Verificue se a pasta `storage/anki-media/` tem permissões de escrita.

## 📈 Acompanhando Progresso

### Dashboard Geral

No menu "Anki", você verá:
- Total de cards em todos os decks
- Cards prontos para estudar
- Cards que você já aprendeu
- Progresso percentual por deck

### Estatísticas Detalhadas

Clique em "Estatísticas" para ver:
- Horas estudadas este mês
- Cards aprendidos
- Lapses (erros)
- Distribuição por dificuldade

## 🛠️ Estrutura do Banco de Dados

### `anki_decks`
```
id - ID do deck
submodule_id - Qual submodulo
name - Nome do deck
description - Descrição
file_path - Caminho do arquivo original
total_cards - Quantidade de cards
created_at, updated_at
```

### `anki_cards`
```
id - ID do card
anki_deck_id - Qual deck
front - Pergunta/frente
back - Resposta/verso
extra - Campos extras
tags - Tags do card
order - Ordem no deck
created_at, updated_at
```

### `anki_card_progress`
```
id - ID do progresso
user_id - Qual usuário
anki_card_id - Qual card
interval - Intervalo em dias
ease_factor - Fator de facilidade (EF)
repetitions - Quantas vezes respondeu
lapses - Quantas vezes errou
next_review - Próxima data de revisão
last_reviewed - Última revisão
status - 'new', 'learning', 'review', 'suspended'
created_at, updated_at
```

## 💻 API

### Registrar Resposta

```javascript
fetch('/anki/{deck}/record-answer', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken
    },
    body: JSON.stringify({
        card_id: 123,
        quality: 2  // 0=fail, 1=hard, 2=ok, 3=easy
    })
})
```

### Rotas Disponíveis

- `GET /anki` - Dashboard principal
- `GET /anki/{deck}/study` - Estudar um deck
- `POST /anki/{deck}/record-answer` - Registrar resposta
- `GET /anki/stats` - Estatísticas gerais
- `GET /submodules/{submodule}/anki-decks/create` - Upload de deck
- `POST /submodules/{submodule}/anki-decks` - Processar upload
- `DELETE /anki-decks/{deck}` - Deletar deck

## 🔧 Troubleshooting

### "Nenhum card pronto para estudar"

Isso significa que todos os seus cards precisam de revisão em algum momento futuro. Volte mais tarde!

### Mídia não aparece

1. Verifique se a pasta `storage/` tem permissões de escrita
2. Verifique se o symbolic link existe:
   ```bash
   php artisan storage:link
   ```

### Erro ao importar APKG

1. Certifique-se de que é um arquivo válido
2. Verifique a extensão (deve ser `.apkg`)
3. Veja os logs em `storage/logs/`

## 📝 Exemplos

### Importar múltiplos decks

```bash
# Importar de um diretório específico
php artisan anki:import --path=/home/usuario/meus-decks

# Com output verboso
php artisan anki:import -v
```

### Limpar dados de um usuário

```php
use App\Models\AnkiCardProgress;

// Resetar todas as respostas de um usuário
AnkiCardProgress::where('user_id', auth()->id())->delete();
```

## 🚨 Importante

- Os cards são armazenados como HTML. HTML malformado pode quebrar a exibição.
- O algoritmo SM-2 é bem conhecido, mas você pode customizar os parâmetros se necessário.
- Backups regulares da pasta `storage/anki-media/` são recomendados.

## 📚 Referências

- [Algoritmo SM-2 Original](https://supermemo.com/en/archives1990-2015/english/ol/2of8mat.htm)
- [Documentação do Anki](https://docs.ankiweb.net/)
- [Formato APKG](https://github.com/ankitects/anki/blob/main/docs/development.md)

---

**Versão**: 1.0  
**Última atualização**: 2026-02-17

# 📋 CHECKLIST DE INSTALAÇÃO - SISTEMA ANKI

## ✅ Pré-requisitos

- [ ] PHP 8.2+
- [ ] Laravel 11+
- [ ] Banco de dados MySQL/PostgreSQL (Sqlite funciona também)
- [ ] Permissões de escrita em `storage/`

## 🔧 Instalação Básica

### Passo 1: Migrations do Banco de Dados
```bash
php artisan migrate
```
- [ ] Comando executado com sucesso
- [ ] Tabelas criadas: `anki_decks`, `anki_cards`, `anki_card_progress`

### Passo 2: Criar Pastas
```bash
mkdir -p storage/anki-media
chmod 755 storage/anki-media
```
- [ ] Pasta `storage/anki-media/` criada
- [ ] Permissões corretas (755)

### Passo 3: Symbolic Link
```bash
php artisan storage:link
```
- [ ] Arquivo `public/storage` criado
- [ ] Link aponta para `storage/app`

## 📦 Preparar Decks APKG

Escolha uma das opções:

### Opção A: Upload via Interface Web ⭐ (Recomendado)

1. [ ] Abra seu navegador
2. [ ] Navegue até um **Submodulo**
3. [ ] Procure pelo ícone **🎴** embaixo do título
4. [ ] Clique para abrir formulário de upload
5. [ ] Selecione seu arquivo `.apkg`
6. [ ] Aguarde a importação

✅ Pronto! Cards aparecem no dashboard

### Opção B: Importação por CLI

1. [ ] Organize seus APKG em uma pasta
   ```
   ~/meus-decks/
   ├── ingles-basico.apkg
   ├── gramatica.apkg
   └── vocabulario.apkg
   ```

2. [ ] Execute o script preparador (opcional)
   ```bash
   bash prepare-anki-structure.sh ~/meus-decks
   ```

3. [ ] Ou organize manualmente por ID de submodulo
   ```
   storage/apkg-import/
   ├── 1/
   │   ├── deck1.apkg
   │   └── deck2.apkg
   ├── 2/
   │   └── deck3.apkg
   └── 3/
       └── deck4.apkg
   ```

4. [ ] Execute importação
   ```bash
   php artisan anki:import --path=storage/apkg-import
   ```

5. [ ] Aguarde conclusão
   - [ ] Mensagens de sucesso aparecem
   - [ ] Cards importados

## 🎮 Usar o Sistema

### Acessar Dashboard
- [ ] Acesse menu → **Anki**
- [ ] Veja estatísticas gerais
- [ ] Veja lista de decks
- [ ] Clique em "Estudar Deck"

### Estudar
- [ ] Leia a pergunta
- [ ] Clique para virar e ver resposta
- [ ] Escolha dificuldade:
  - [ ] ❌ Errei (10 min)
  - [ ] 😐 Difícil (1 dia)
  - [ ] 😊 Médio (3 dias)
  - [ ] ✨ Fácil (7+ dias)
- [ ] Finalize todos os cards
- [ ] Veja mensagem de conclusão

### Acompanhar Progresso
- [ ] Veja percentual de conclusão por deck
- [ ] Veja "Prontos para Revisar"
- [ ] Volte amanhã para revisar

## 📱 Verificação

### Status da Instalação
```bash
php artisan anki:list
```
- [ ] Comando funciona
- [ ] Lista decks importados

### Verificar Permissões
```bash
ls -la storage/anki-media/
```
- [ ] Pasta existe
- [ ] Tem permissão de leitura (rwx)

### Verificar Banco de Dados
```bash
php artisan tinker
>>> \App\Models\AnkiDeck::count()
```
- [ ] Retorna número de decks

## 🐛 Troubleshooting

Se algo não funciona:

1. [ ] Verificar logs
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. [ ] Limpar cache
   ```bash
   php artisan cache:clear
   php artisan config:clear
   ```

3. [ ] Recriar symbolic link
   ```bash
   php artisan storage:link
   ```

4. [ ] Executar migrations novamente
   ```bash
   php artisan migrate
   ```

## 📚 Recursos Importantes

- [ ] Leia `ANKI_QUICK_START.md` para guia rápido
- [ ] Leia `ANKI_DOCUMENTATION.md` para docs completas
- [ ] Veja `ANKI_IMPLEMENTATION_SUMMARY.md` para visão geral

## 🎓 Exemplos de Uso

### Teste Rápido Com CSV
1. [ ] Copie `example-deck.csv` para seu submodulo
2. [ ] Abra formulário de upload
3. [ ] Envie como CSV
4. [ ] Veja 15 cards de teste

### Importar APKG Real
1. [ ] Exporte um deck do Anki Desktop
   - Apps → Anki → Arquivo → Exportar → APKG
2. [ ] Uso upload web ou CLI

## ✨ Parabéns!

Se marcou tudo como ✅ acima, seu sistema está **100% funcional**!

### Próximos passos recomendados:
- [ ] Estude alguns cards
- [ ] Revise amanhã
- [ ] Acompanhe progresso no dashboard
- [ ] Importe mais decks conforme necessário

---

## 📞 Suporte

Problemas ou dúvidas?

1. Verifique os logs: `storage/logs/laravel.log`
2. Rode troubleshooting acima
3. Verifique documentação em `ANKI_DOCUMENTATION.md`
4. Confirme que todos os passos foram feitos

---

**Status Final:** ✅ Pronto para usar!

**Data:** 17 de Fevereiro, 2026  
**Versão:** 1.0

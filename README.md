# Plataforma de Cursos 

Sistema de cursos online com suporte a vídeo, PDF, quiz e acompanhamento de progresso.

---

## Arquitetura

- **Backend:** Laravel 10+ (MVC, Eloquent ORM, migrations, seeders)
- **Frontend:** Blade, Tailwind CSS, Vite, JavaScript
- **Banco de Dados:** MySQL
- **Armazenamento de arquivos:** Local (`storage/app/public/videos`, `pdfs`)
- **Autenticação:** Laravel Breeze (ou padrão)
- **Mobile-first:** Layout responsivo, otimizado para dispositivos móveis e desktop

### Hierarquia de Entidades

- **Course** (Curso)
  - **Module** (Módulo)
    - **SubModule** (Submódulo)
      - **Lesson** (Lição: vídeo, PDF, quiz, texto)
        - **Progress** (Progresso do usuário)

### Estrutura de Pastas

```
app/
  Models/           # Eloquent models (Course, Module, SubModule, Lesson, Progress, User)
  Http/
    Controllers/    # Controllers REST e web
    Requests/       # Form requests
resources/
  views/            # Blade templates (courses, modules, submodules, lessons, layouts)
  css/              # Tailwind e (opcional) mobile.css
  js/               # JS para progresso de vídeo, interações
database/
  migrations/       # Migrations de schema
  seeders/          # Seeders de dados
public/
  storage/          # Symlink para arquivos públicos (videos, pdfs)
routes/
  web.php           # Rotas web
  auth.php          # Rotas de autenticação
```

---

## Fluxo de Dados

- **Usuário** acessa cursos → módulos → submódulos → lições.
- **Lição** pode ser vídeo, PDF, quiz ou texto.
- **Progresso** é salvo automaticamente (barra de progresso, conclusão).
- **Uploads** de vídeos/PDFs são salvos em `storage/app/public/videos` e acessados via `/storage/videos/...`.

---

## Instalação

1. **Clone o repositório**
   ```bash
   git clone https://github.com/sua-org/seu-repo.git
   cd seu-repo
   ```

2. **Instale dependências**
   ```bash
   composer install
   npm install
   ```

3. **Configure o .env**
   - Copie `.env.example` para `.env` e ajuste DB, mail, etc.

4. **Gere a key**
   ```bash
   php artisan key:generate
   ```

5. **Rode as migrations**
   ```bash
   php artisan migrate
   ```

6. **Crie o symlink de storage**
   ```bash
   php artisan storage:link
   ```

7. **Compile os assets**
   ```bash
   npm run build
   ```

8. **(Opcional) Popule com seeders**
   ```bash
   php artisan db:seed
   ```

---

## Como usar

- Acesse `/` e faça login/cadastro.
- Crie cursos, módulos, submódulos e lições.
- Faça upload de vídeos em `storage/app/public/videos` (não versionar no git!).
- Progresso de vídeo é salvo automaticamente.
- Layout responsivo: funciona em mobile e desktop.

---

## Observações

- **Vídeos e PDFs**: não versionar arquivos grandes no git. Use `.gitignore` para ignorar `storage/app/videos` e `storage/app/public/videos`.
- **Mobile.css**: atualmente desabilitado para evitar conflito com Tailwind.
- **Customizações**: ajuste as views em `resources/views` conforme sua identidade visual.

---

## Estrutura de Models

- `Course` → tem muitos `Module`
- `Module` → tem muitos `SubModule`
- `SubModule` → tem muitas `Lesson`
- `Lesson` → pertence a `SubModule`, tem muitos `Progress`
- `Progress` → pertence a `Lesson` e `User`

---

## Licença

MIT

---

> Projeto desenvolvido para UnimedSC — [Seu nome/empresa]

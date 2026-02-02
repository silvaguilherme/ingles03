<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

# Plataforma de Cursos UnimedSC

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

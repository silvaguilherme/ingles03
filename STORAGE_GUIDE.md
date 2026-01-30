# 📁 Guia de Upload e Armazenamento de Arquivos

## Estrutura de Armazenamento

Os arquivos do seu aplicativo Laravel são organizados em pastas específicas dentro da estrutura de armazenamento:

```
storage/
├── app/
│   ├── private/        # Arquivos privados (acessíveis apenas com autenticação)
│   └── public/         # Arquivos públicos (acessíveis via URL direta)
└── ...
```

## 📌 Locais de Armazenamento Recomendados

### Para Vídeos
```
storage/app/public/videos/
├── modulo1/
│   ├── aula1.mp4
│   ├── aula2.mp4
│   └── ...
├── modulo2/
│   └── ...
```

**Chave para armazenar no banco:** `videos/modulo1/aula1.mp4`

### Para PDFs
```
storage/app/public/pdfs/
├── modulo1/
│   ├── material1.pdf
│   ├── material2.pdf
│   └── ...
├── modulo2/
│   └── ...
```

**Chave para armazenar no banco:** `pdfs/modulo1/material1.pdf`

### Para Imagens (Thumbnails de cursos)
```
storage/app/public/images/
├── courses/
│   ├── course1.jpg
│   ├── course2.jpg
├── thumbnails/
│   └── ...
```

## 🔗 Como Usar as Chaves

Quando você cria um curso ou lição no formulário, você preenche com a **chave** (caminho relativo):

- **Campo:** `Chave do Vídeo`
- **Exemplo:** `videos/modulo1/aula1.mp4`
- **URL gerada automaticamente:** `/storage/videos/modulo1/aula1.mp4`

## 📤 Upload de Arquivos (Próxima Fase)

Para implementar upload direto no formulário, você pode:

### Opção 1: Upload via Formulário Blade
```html
<input type="file" name="video" accept="video/*">
```

### Opção 2: Upload via AJAX/Dropzone
Para arquivos grandes, recomenda-se upload assíncrono.

### Opção 3: Integração com Cloud (S3/Oracle Object Storage)
Se usar bucket de armazenamento na nuvem, as URLs serão geradas automaticamente.

## 🔐 Permissões Necessárias

No seu servidor, garanta que a pasta `storage` tem permissões de escrita:

```bash
# No servidor Linux
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache
```

## 🌐 Acessar Arquivo Público

Após criar o link simbólico:

```bash
php artisan storage:link
```

**URL pública dos arquivos:**
```
http://seu-dominio.com/storage/videos/modulo1/aula1.mp4
```

## ✅ Estrutura Final Recomendada

```
storage/app/public/
├── videos/
│   ├── ingles/
│   │   ├── modulo1/
│   │   │   ├── aula1.mp4
│   │   │   └── aula2.mp4
│   │   └── modulo2/
│   │       └── aula1.mp4
├── pdfs/
│   ├── ingles/
│   │   ├── modulo1/
│   │   │   └── material.pdf
│   │   └── modulo2/
│   │       └── material.pdf
└── images/
    └── courses/
        └── thumb.jpg
```

## 🎯 Exemplo Completo

1. **Coloque o arquivo** em `storage/app/public/videos/ingles/modulo1/aula1.mp4`
2. **No formulário de criar lição**, preencha:
   - Título: "Aula 1 - Present Tense"
   - Tipo: "Vídeo"
   - Chave do Vídeo: `videos/ingles/modulo1/aula1.mp4`
   - Duração: `3600` (1 hora em segundos)
3. **Sistema gera automaticamente** a URL pública e mostra o vídeo!

## 🚀 Próximas Melhorias

- [ ] Upload direto no formulário com validação
- [ ] Compressão automática de vídeos
- [ ] Geração de thumbnails
- [ ] Armazenamento em cloud (S3/Oracle Storage)
- [ ] Streaming de vídeo adaptativo (HLS/DASH)

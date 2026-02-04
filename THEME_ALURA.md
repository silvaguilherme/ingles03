# Tema Alura - Layout Minimalista & Escuro

## 🎨 Atualização de Design Completa

Seu projeto agora possui um **tema minimalista e escuro** inspirado na plataforma Alura, com uma experiência visual moderna e profissional.

### 📋 Mudanças Realizadas

#### 1. **Configuração Tailwind** (`tailwind.config.js`)
- Adicionadas cores customizadas do tema Alura
- Fonte Inter para melhor legibilidade
- Paleta de cores implementada:
  - **Fundo Escuro**: `#0f1729`
  - **Cards**: `#1a1f3a`
  - **Acento Azul**: `#1f90ff`
  - **Texto Principal**: `#e0e0e0`
  - **Texto Secundário**: `#8b92a1`

#### 2. **Folhas de Estilo CSS**
- **app.css**: Importação do tema Alura e componentes customizados
- **alura-theme.css**: Estilos avançados incluindo:
  - Scrollbar customizada com cor do acento
  - Seleção de texto com cores do tema
  - Inputs com focus states personalizados
  - Animações suaves (fade in, slide down, spinner)
  - Badges, alerts e componentes estilizados

#### 3. **Layouts Atualizados**

##### App Layout (`resources/views/layouts/app.blade.php`)
- Background escuro do tema Alura
- Header com transparência e backdrop blur
- Suporte a tema escuro nativo

##### Guest Layout (`resources/views/layouts/guest.blade.php`)
- Design minimalista para páginas de autenticação
- Logo agora em texto com cor do acento
- Cards com estilo Alura
- Fundo completamente escuro

##### Navigation (`resources/views/layouts/navigation.blade.php`)
- Navbar minimalista com cores do tema
- Hover effects suaves
- Mobile responsive com design dark
- Logo em texto destacado

#### 4. **Componentes Blade Atualizados**

| Componente | Mudanças |
|-----------|----------|
| `nav-link.blade.php` | Cores Alura, borda azul no estado ativo |
| `dropdown-link.blade.php` | Fundo card ao hover, texto colorido |
| `primary-button.blade.php` | Botão azul com ring offset escuro |
| `secondary-button.blade.php` | Botão com borda, fundo card |
| `danger-button.blade.php` | Ring offset ajustado para tema escuro |
| `text-input.blade.php` | Background card, focus ring azul |
| `input-label.blade.php` | Texto claro com contraste melhorado |
| `input-error.blade.php` | Vermelho mais claro para fundo escuro |
| `dropdown.blade.php` | Fundo card, borda sutil |
| `responsive-nav-link.blade.php` | Cores Alura em todos os estados |
| `auth-session-status.blade.php` | Status com badge verde |
| `modal.blade.php` | Overlay escuro, card com estilo Alura |

#### 5. **Páginas de Autenticação**
- Login: Redesenhado com título, melhor UX
- Labels em Português (Bem-vindo, Senha, etc.)
- Links de registro e recuperação de senha
- Estilo minimalista e profissional

#### 6. **Dashboard**
- Mensagem de boas-vindas em português
- Card com estilo Alura
- Tipografia melhorada

### 🎯 Características Principais

✅ **Minimalista**: Design limpo sem elementos desnecessários
✅ **Tema Escuro**: Confortável para os olhos em qualquer momento
✅ **Acessibilidade**: Contraste adequado, focus states claros
✅ **Responsivo**: Perfeito em mobile e desktop
✅ **Transições Suaves**: Animações fluidas de 200-300ms
✅ **Consistência**: Uso uniforme de cores e espaçamento
✅ **Moderna**: Inspirada em plataformas modernas como Alura

### 🎨 Paleta de Cores

```css
Fundo Escuro: #0f1729
Fundo Escuro (Mais escuro): #0a0e1a
Cards: #1a1f3a
Acento Principal: #1f90ff
Acento Hover: #0066cc
Texto Principal: #e0e0e0
Texto Secundário: #8b92a1
```

### 📱 Responsive Design

- Mobile first approach
- Breakpoints Tailwind padrão
- Hamburger menu para dispositivos pequenos
- Layout fluido em todas as resoluções

### 🚀 Como Usar

1. **Classes Utilitárias**:
   - `.alura-card` - Estilo de card padrão
   - `.alura-btn` - Botão primário
   - `.alura-btn-secondary` - Botão secundário
   - `.alura-input` - Input com estilo
   - `.alura-gradient` - Gradiente azul

2. **Cores no Tailwind**:
   - `bg-alura-dark` - Fundo escuro
   - `text-alura-text` - Texto principal
   - `text-alura-text-muted` - Texto secundário
   - `bg-alura-card` - Fundo de card
   - `bg-alura-accent` - Cor azul acento

### 📦 Arquivos Modificados

- `tailwind.config.js`
- `resources/css/app.css`
- `resources/css/alura-theme.css` *(novo)*
- `resources/views/layouts/app.blade.php`
- `resources/views/layouts/guest.blade.php`
- `resources/views/layouts/navigation.blade.php`
- `resources/views/dashboard.blade.php`
- `resources/views/auth/login.blade.php`
- `resources/views/components/*.blade.php` *(11 componentes)*

### 💡 Dicas de Customização

Se quiser ajustar cores:
1. Edite `tailwind.config.js` na seção `colors.alura`
2. Atualize `resources/css/alura-theme.css` se necessário
3. Execute `npm run build` ou `npm run dev`

### 🔄 Próximas Melhorias Sugeridas

- [ ] Adicionar tema light mode toggler
- [ ] Criar mais componentes customizados
- [ ] Adicionar animações de página
- [ ] Implementar dark mode toggle no navbar
- [ ] Criar documentação de componentes

---

**Tema criado em**: 4 de Fevereiro de 2026
**Status**: ✅ Completo e Pronto para Uso

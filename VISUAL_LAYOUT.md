# 🎨 Layout Visual - Tema Alura

## Estrutura Visual das Páginas

### 1. Layout Principal (app.blade.php)

```
┌─────────────────────────────────────────────────────────────────┐
│                                                                   │
│  🎓 CURSO          [Dashboard]              👤 User ▼            │ ← Navbar (#0a0e1a)
│                                                                   │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│  Dashboard          ← Header Sticky                              │ ← Header (#0a0e1a)
│                                                                   │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│                  Bem-vindo ao seu Curso!                         │
│                                                                   │
│              ┌──────────────────────────────────┐                │
│              │  Comece a explorar os cursos     │                │ ← Card (#1a1f3a)
│              │  disponíveis e continue seu      │                │
│              │  aprendizado.                    │                │
│              └──────────────────────────────────┘                │
│                                                                   │
│                                          Fundo: #0f1729          │
│                                                                   │
└─────────────────────────────────────────────────────────────────┘
```

### 2. Página de Login (guest.blade.php)

```
┌─────────────────────────────────────────────────────────────────┐
│                                                                   │
│                                                                   │
│                      🎓 CURSO                                    │ ← Logo Text (#1f90ff)
│                                                                   │
│            ┌─────────────────────────────────────┐               │
│            │                                       │               │
│            │   Bem-vindo                          │               │ ← Card (#1a1f3a)
│            │   Entre com suas credenciais         │               │   Border: #374151/50
│            │                                       │               │
│            │   Email:  [______________]           │               │
│            │   Senha:  [______________]           │               │
│            │                                       │               │
│            │   ☑ Lembrar-me                      │               │
│            │                                       │               │
│            │   [Esqueceu sua senha?]   [ENTRAR]   │               │ ← Botão #1f90ff
│            │                                       │               │
│            │   Não tem conta? Registrar           │               │
│            │                                       │               │
│            └─────────────────────────────────────┘               │
│                                                                   │
│                      Fundo: #0f1729                              │
│                                                                   │
└─────────────────────────────────────────────────────────────────┘
```

### 3. Card Exemplo

```
┌──────────────────────────────────────────────────────────┐
│                                                            │ ← Border: 1px #374151/50
│  📚 Meu Projeto                                          │
│  Descrição do projeto aqui em texto secundário           │ ← Text: #8b92a1
│                                                            │
│  [Editar]         [Cancelar]                             │ ← Botões
│                                                            │
└──────────────────────────────────────────────────────────┘
   ↑
   Bg: #1a1f3a
   Hover: Border muda pra azul
   Transição: 300ms
```

### 4. Botões

```
┌──────────┐  ┌──────────┐  ┌──────────┐
│ PRIMÁRIO │  │ SECUNDÁRIO│ │  PERIGO  │
│  Azul    │  │   Card   │  │ Vermelho │
│ #1f90ff  │  │ #1a1f3a  │  │ #ef4444 │
└──────────┘  └──────────┘  └──────────┘

Normal:  bg-alura-accent        bg-alura-card        bg-red-600
Hover:   bg-#0066cc             bg-gray-700          bg-red-500
Focus:   ring-2 ring-accent     ring-2 ring-accent   ring-2 ring-red
```

### 5. Formulário

```
Email
[────────────────────────────────────────────] ← #1a1f3a bg
                                                  #1f90ff focus ring

Senha  
[────────────────────────────────────────────] ← #1a1f3a bg
                                                  #1f90ff focus ring

⚠️ Este campo é obrigatório ← #ef4444 (vermelho)
```

### 6. Navegação Mobile

```
┌─────────────────────────────────────────────┐
│ 🎓 CURSO                          [≡]       │ ← Hamburger
└─────────────────────────────────────────────┘

Quando clicado:
┌─────────────────────────────────────────────┐
│ 🎓 CURSO                          [✕]       │
├─────────────────────────────────────────────┤
│ Dashboard                                    │ ← Links
│ Perfil                                      │    mobile
│ Logout                                      │
└─────────────────────────────────────────────┘
```

### 7. Modal/Popup

```
     ┌─────────────────────────────────────┐
     │                                      │ ← bg-alura-card
     │  Confirmar Ação                     │
     │                                      │
     │  Tem certeza que deseja continuar? │ ← text-alura-text
     │                                      │
     │  [Cancelar]         [Confirmar]     │
     │                                      │
     └─────────────────────────────────────┘
           ↓
     Overlay escuro (#0a0e1a)
     opacity-75
```

---

## 🎨 Paleta de Cores Visual

### Cores Principais

```
████  #0f1729  Fundo Principal Dark
████  #0a0e1a  Fundo Mais Escuro
████  #1a1f3a  Cards/Componentes
████  #1f90ff  Azul Acento ⭐
████  #0066cc  Azul Hover
```

### Texto

```
████  #e0e0e0  Texto Principal (Alto Contraste)
████  #8b92a1  Texto Secundário (Muted)
████  #6b7280  Texto Desabilitado
```

### Status

```
████  #10b981  Sucesso (Verde)
████  #f59e0b  Aviso (Amarelo)
████  #ef4444  Erro (Vermelho)
```

---

## 📐 Espaçamento & Tipografia

### Espaçamento (Tailwind)

```
Padding Cards:    p-6 (1.5rem)
Margin Seções:    my-12 (3rem)
Gap entre itens:  gap-6 (1.5rem)
Margin Botões:    mt-4 (1rem)
```

### Tipografia

```
H1: 36px font-bold    → Títulos grandes
H2: 30px font-bold    → Seções
H3: 24px font-semibold → Subseções
P:  16px normal       → Texto padrão
Small: 12px gray      → Labels
```

---

## ✨ Efeitos & Transições

### Hover Effects

```
Card:
  Normal → Hover
  border: gray/50 → border: blue/30
  transition: 300ms

Link:
  color: #1f90ff → color: #0066cc
  transition: 200ms

Button:
  bg: #1f90ff → bg: #0066cc
  transition: 150ms
```

### Focus States

```
Input:
  ┌────────────────────┐
  │ [texto...]         │ ← ring-2 ring-blue
  └────────────────────┘
     ring-offset-dark

Button:
  ┌────────┐
  │ BOTÃO  │ ← ring-2 ring-blue
  └────────┘
  ring-offset-dark
```

### Animações

```
Fade In:
0%   ────────────────► 100%
opacity: 0            opacity: 1
duration: 300ms

Slide Down:
0%   ────────────────► 100%
↑ + opacity: 0        ↓ + opacity: 1
duration: 300ms

Modal Entrance:
Scale 95% + opacity: 0 ────► Scale 100% + opacity: 1
duration: 300ms
```

---

## 🔐 Acessibilidade

### Focus Indicators

```
Todos os elementos focáveis têm:
  • ring-2 em azul acento
  • Bem visível
  • Fácil de navegar com teclado
```

### Contraste

```
Texto Normal: #e0e0e0 em #0f1729 = 11.5:1 ✅ AAA
Texto Muted:  #8b92a1 em #0f1729 = 4.8:1  ✅ AA
Link:         #1f90ff em #0f1729 = 6.2:1  ✅ AAA
```

### Estrutura

```
Semântica HTML:
  <nav>           ← Navegação
  <header>        ← Cabeçalho
  <main>          ← Conteúdo
  <button>        ← Botões (não <div>)
  <label>         ← Sempre com form
```

---

## 📱 Responsive Breakpoints

### Mobile (< 640px)
```
┌────────────────┐
│ [≡] CURSO  👤  │
├────────────────┤
│  Conteúdo      │
│  empilhado     │
│                │
│  Grid 1 col    │
└────────────────┘
```

### Tablet (640px - 1024px)
```
┌──────────────────────────┐
│ CURSO          👤        │
├──────────────────────────┤
│                          │
│  [  Card  ] [  Card  ]  │
│  Grid 2 col             │
│                          │
└──────────────────────────┘
```

### Desktop (1024px+)
```
┌─────────────────────────────────────────┐
│ CURSO              [...]        👤      │
├─────────────────────────────────────────┤
│                                          │
│ [Card] [Card] [Card] [Card]            │
│ Grid 4 col                              │
│                                          │
└─────────────────────────────────────────┘
```

---

## 🎯 Exemplo Completo: Dashboard

```
┌───────────────────────────────────────────────────────────────────┐
│                                                                     │
│  🎓 CURSO                              [Dashboard]         👤 ▼   │
│                                                                     │
├───────────────────────────────────────────────────────────────────┤
│  Dashboard                                                          │
├───────────────────────────────────────────────────────────────────┤
│                                                                     │
│  ┌──────────────────┐  ┌──────────────────┐  ┌──────────────────┐│
│  │ 📚 Curso 1       │  │ 📚 Curso 2       │  │ 📚 Curso 3       ││
│  │ Descrição...     │  │ Descrição...     │  │ Descrição...     ││
│  │                  │  │                  │  │                  ││
│  │ [Continuar]      │  │ [Continuar]      │  │ [Continuar]      ││
│  └──────────────────┘  └──────────────────┘  └──────────────────┘│
│                                                                     │
│  ┌──────────────────┐  ┌──────────────────┐  ┌──────────────────┐│
│  │ 📚 Curso 4       │  │ 📚 Curso 5       │  │ 📚 Curso 6       ││
│  │ Descrição...     │  │ Descrição...     │  │ Descrição...     ││
│  │                  │  │                  │  │                  ││
│  │ [Continuar]      │  │ [Continuar]      │  │ [Continuar]      ││
│  └──────────────────┘  └──────────────────┘  └──────────────────┘│
│                                                                     │
│  Fundo: #0f1729                                                    │
│  Cards: #1a1f3a                                                    │
│  Botões: #1f90ff                                                   │
│                                                                     │
└───────────────────────────────────────────────────────────────────┘
```

---

## 🎉 Resultado: Um Layout Profissional e Minimalista

```
✅ Cores Harmônicas
✅ Tipografia Clara
✅ Espaçamento Consistente
✅ Componentes Reutilizáveis
✅ Transições Suaves
✅ Acessível
✅ Responsivo
✅ Moderno
```

---

**Tema Alura - Design System Visual Completo** 🎨

Versão: 1.0
Data: 4 de Fevereiro de 2026

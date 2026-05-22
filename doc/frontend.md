# Frontend — Telas e componentes

SPA Vue 3 com padrão visual consistente: **listagem + modal** para todas as funcionalidades.

---

## Acesso

- Dev local: http://localhost:5173
- Docker: http://localhost:5173

---

## Navegação

Sidebar fixa com três módulos:

| Rota | Tela | Função |
|------|------|--------|
| `/produtos` | ProdutosPage | CRUD de produtos |
| `/compras` | ComprasPage | Listagem e registro de compras |
| `/vendas` | VendasPage | Listagem, registro e cancelamento de vendas |

---

## Padrão de telas

Toda funcionalidade segue:

1. **Listagem** com tabela
2. Botão **"Novo"** no topo
3. **Ações por linha** (ícones Lucide)
4. **Modal** para criar/editar (mesmo formulário reutilizado)
5. **SweetAlert2** para confirmações e feedback

```
Listagem → [Novo] → Modal + Form → API → Swal sucesso/erro → Refresh lista
Listagem → [Editar/Excluir] → Modal ou Swal confirm → API → Refresh
```

---

## Módulo Produtos

**Colunas:** Nome, Preço Venda, Custo Médio, Estoque, Ações

| Ação | Comportamento |
|------|---------------|
| + Novo | Abre modal com formulário vazio |
| Editar (lápis) | Abre modal preenchido |
| Excluir (lixeira) | Swal confirma → DELETE |

**Formulário:** nome, preço de venda sugerido

---

## Módulo Compras

**Colunas:** Data, Fornecedor, Qtd Itens, Total, Ações

| Ação | Comportamento |
|------|---------------|
| + Nova Compra | Modal com fornecedor + itens dinâmicos |
| Ver detalhes (olho) | Modal read-only com itens |

**Formulário:**

- Campo fornecedor
- Linhas: select produto (mostra estoque) + quantidade + preço unitário
- Botão "Adicionar linha"
- Total calculado em tempo real

> Compras **não** têm editar/deletar (integridade contábil).

---

## Módulo Vendas

**Colunas:** Data, Cliente, Total, Lucro, Status, Ações

| Ação | Comportamento |
|------|---------------|
| + Nova Venda | Modal com cliente + itens |
| Ver detalhes (olho) | Modal com itens, custo e lucro por linha |
| Cancelar (ban) | Swal confirma → reverte estoque |

**Formulário:**

- Campo cliente
- Linhas: select produto + quantidade + preço unitário
- **VendaResumo:** total e lucro estimado (calculado com `custo_medio` do produto)

Erro "Estoque insuficiente" exibido via Swal com mensagem da API.

---

## Componentes reutilizáveis

```
src/components/
├── layout/
│   ├── AppLayout.vue      # Sidebar + área principal
│   ├── Sidebar.vue        # Menu de navegação
│   └── PageHeader.vue     # Título + slot de ações
├── ui/
│   ├── BaseModal.vue      # Modal com overlay
│   ├── BaseTable.vue      # Tabela genérica
│   ├── BaseButton.vue     # Botão com loading
│   ├── BaseInput.vue      # Input com label e erro
│   └── IconButton.vue     # Botão só ícone (edit/delete/view/cancel)
├── produtos/ProdutoForm.vue
├── compras/CompraForm.vue
└── vendas/
    ├── VendaForm.vue
    └── VendaResumo.vue
```

---

## Composables

| Arquivo | Função |
|---------|--------|
| `useSwal.js` | `success()`, `error()`, `confirmDelete()`, `confirmAction()` |
| `useCrudModal.js` | Controla abertura do modal (create/edit) |
| `useAsync.js` | Estado de loading e erros em chamadas async |

---

## Serviços API

```
src/api/
├── http.js       # Axios + interceptor de erros
├── produtos.js
├── compras.js
└── vendas.js
```

---

## Estado (Pinia)

Store `produtos` — cache da lista de produtos usado nos `<select>` de Compras e Vendas.

---

## Stack UI

| Biblioteca | Uso |
|------------|-----|
| Tailwind CSS | Estilização |
| Lucide Vue | Ícones (Package, ShoppingCart, Pencil, Trash2…) |
| SweetAlert2 | Confirmações e toasts |
| Vue Router | Navegação SPA |
| Pinia | Cache de produtos |

---

## Desenvolvimento

```bash
cd frontend
nvm use
npm install
npm run dev
```

Build de produção:

```bash
npm run build
# Saída em frontend/dist/
```

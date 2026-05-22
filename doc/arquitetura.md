# Arquitetura do sistema

ERP de estoque **desacoplado**: frontend Vue consome API REST Laravel. Banco MySQL em produção/Docker; SQLite disponível para dev local.

---

## Visão geral

```
┌──────────────┐     HTTP/JSON      ┌──────────────┐     SQL      ┌──────────┐
│  Vue 3 SPA   │ ◄──────────────► │ Laravel API  │ ◄──────────► │  MySQL   │
│  (frontend/) │                  │  (backend/)  │              │          │
└──────────────┘                  └──────────────┘              └──────────┘
```

---

## Estrutura de pastas

```
Fone-Ninja-Teste-Tecnico/
├── docker-compose.yml
├── .env.example
├── doc/                          # Documentação
├── backend/                      # Laravel API
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/Api/
│   │   │   ├── Requests/
│   │   │   └── Resources/
│   │   ├── Models/
│   │   ├── Services/
│   │   └── Exceptions/
│   ├── routes/api.php
│   ├── database/migrations/
│   ├── tests/Feature/
│   └── Dockerfile
└── frontend/                     # Vue SPA
    ├── src/
    │   ├── api/
    │   ├── components/
    │   ├── composables/
    │   ├── pages/
    │   ├── router/
    │   └── stores/
    ├── .nvmrc
    └── Dockerfile
```

---

## Backend — camadas

| Camada | Responsabilidade |
|--------|------------------|
| **Controllers** | Recebem HTTP, delegam ao service, retornam JSON |
| **Form Requests** | Validação de entrada |
| **Services** | Regras de negócio (estoque, custo médio, lucro) |
| **Models** | Eloquent ORM e relacionamentos |
| **Resources** | Formatação da resposta JSON |

### Services principais

- `EstoqueService` — entrada, saída e reversão de estoque; recálculo de custo médio
- `CompraService` — orquestra registro de compra em transação
- `VendaService` — orquestra venda e cancelamento

---

## Frontend — camadas

| Camada | Responsabilidade |
|--------|------------------|
| **Pages** | Telas (Produtos, Compras, Vendas) |
| **Components** | UI reutilizável (modal, tabela, forms) |
| **api/** | Clientes Axios por domínio |
| **composables** | Lógica compartilhada (Swal, modal CRUD) |
| **stores** | Pinia — cache de produtos para selects |

---

## Stack tecnológica

| Camada | Tecnologia |
|--------|------------|
| Backend | Laravel 13, PHP 8.3 |
| Frontend | Vue 3, Vite, Vue Router, Pinia |
| UI | Tailwind CSS 4, Lucide Icons, SweetAlert2 |
| HTTP client | Axios |
| Banco | MySQL 8 (Docker) / SQLite (dev local) |
| Infra | Docker Compose |

---

## Modelo de dados (resumo)

```
produtos ──┬── compra_itens ── compras
           └── venda_itens  ── vendas
```

| Tabela | Descrição |
|--------|-----------|
| `produtos` | Cadastro, preço sugerido, custo médio, estoque |
| `compras` | Cabeçalho da compra (fornecedor, total) |
| `compra_itens` | Itens da compra (produto, qtd, preço) |
| `vendas` | Cabeçalho da venda (cliente, total, lucro, status) |
| `venda_itens` | Itens da venda com snapshot de custo e lucro |

Detalhes em [Regras de negócio](regras-de-negocio.md).

# API REST — Referência

Base URL: `http://localhost:8000/api`

Todas as respostas são JSON. Envie o header:

```
Accept: application/json
Content-Type: application/json
```

---

## Produtos

### Listar

```http
GET /api/produtos
```

**Resposta 200:**

```json
{
  "data": [
    {
      "id": 1,
      "nome": "Fone Bluetooth",
      "preco_venda": 150,
      "custo_medio": 20,
      "estoque": 48,
      "created_at": "...",
      "updated_at": "..."
    }
  ]
}
```

### Criar

```http
POST /api/produtos
```

**Body:**

```json
{
  "nome": "Fone Bluetooth",
  "preco_venda": 150
}
```

| Campo | Regras |
|-------|--------|
| `nome` | obrigatório, mínimo 3 caracteres, único |
| `preco_venda` | obrigatório, numérico, > 0 |

Estoque inicia em `0`, custo médio em `0`.

### Detalhe

```http
GET /api/produtos/{id}
```

### Atualizar

```http
PUT /api/produtos/{id}
```

**Body:** mesmo formato do criar. Apenas `nome` e `preco_venda` são alteráveis.

### Excluir

```http
DELETE /api/produtos/{id}
```

**Erros 422:**

- Produto com estoque > 0
- Produto com histórico de compras ou vendas

---

## Compras

### Listar (paginado)

```http
GET /api/compras?page=1
```

### Detalhe

```http
GET /api/compras/{id}
```

### Registrar compra

```http
POST /api/compras
```

**Body:**

```json
{
  "fornecedor": "Fornecedor X",
  "produtos": [
    { "id": 1, "quantidade": 50, "preco_unitario": 20 },
    { "id": 2, "quantidade": 30, "preco_unitario": 10 }
  ]
}
```

| Campo | Regras |
|-------|--------|
| `fornecedor` | obrigatório, mínimo 2 caracteres |
| `produtos` | array com pelo menos 1 item |
| `produtos.*.id` | deve existir em `produtos` |
| `produtos.*.quantidade` | inteiro ≥ 1 |
| `produtos.*.preco_unitario` | numérico > 0 |

**Efeito:** incrementa estoque e recalcula custo médio de cada produto.

**Resposta 201:** objeto compra com itens e totais.

---

## Vendas

### Listar (paginado)

```http
GET /api/vendas?page=1
```

### Detalhe

```http
GET /api/vendas/{id}
```

### Registrar venda

```http
POST /api/vendas
```

**Body:**

```json
{
  "cliente": "Fulano da Silva",
  "produtos": [
    { "id": 1, "quantidade": 2, "preco_unitario": 50 },
    { "id": 2, "quantidade": 1, "preco_unitario": 100 }
  ]
}
```

**Resposta 201:**

```json
{
  "data": {
    "id": 1,
    "cliente": "Fulano da Silva",
    "total": 200,
    "lucro": 150,
    "status": "ativa",
    "itens": [...]
  },
  "total": 200,
  "lucro": 150
}
```

### Cancelar venda

```http
POST /api/vendas/{id}/cancelar
```

**Efeito:** reverte estoque de todos os itens; marca `status = cancelada`.

**Erro 422:** venda já cancelada.

---

## Erros comuns

### Validação (422)

```json
{
  "message": "The nome field is required.",
  "errors": {
    "nome": ["O nome do produto é obrigatório."]
  }
}
```

### Estoque insuficiente (422)

```json
{
  "message": "Estoque insuficiente para o produto Fone Bluetooth"
}
```

### Produto duplicado no payload (422)

```json
{
  "message": "...",
  "errors": {
    "produtos": ["Não é permitido repetir o mesmo produto na venda."]
  }
}
```

---

## Exemplos com curl

```bash
# Listar produtos
curl -s http://localhost:8000/api/produtos -H "Accept: application/json"

# Criar produto
curl -s -X POST http://localhost:8000/api/produtos \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"nome":"Fone Bluetooth","preco_venda":150}'

# Registrar compra
curl -s -X POST http://localhost:8000/api/compras \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "fornecedor": "Fornecedor X",
    "produtos": [{"id": 1, "quantidade": 50, "preco_unitario": 20}]
  }'

# Registrar venda
curl -s -X POST http://localhost:8000/api/vendas \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "cliente": "Fulano",
    "produtos": [{"id": 1, "quantidade": 2, "preco_unitario": 50}]
  }'

# Cancelar venda
curl -s -X POST http://localhost:8000/api/vendas/1/cancelar \
  -H "Accept: application/json"
```

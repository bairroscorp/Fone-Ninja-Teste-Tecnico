# Regras de negócio

Documentação das regras implementadas no ERP de estoque.

---

## Produtos

| Campo | Comportamento |
|-------|---------------|
| `nome` | Obrigatório, mínimo 3 caracteres, único |
| `preco_venda` | Sugestão de preço; na venda pode ser diferente |
| `estoque` | Inicia em 0; só muda via compra/venda/cancelamento |
| `custo_medio` | Inicia em 0; só muda via compra |

### Edição

Permitido alterar apenas `nome` e `preco_venda`.

### Exclusão

Bloqueada quando:

- `estoque > 0`
- Existe histórico em `compra_itens` ou `venda_itens`

---

## Controle de estoque

| Operação | Estoque | Custo médio |
|----------|---------|-------------|
| Cadastro produto | 0 | 0 |
| Compra | `+= quantidade` | Recalcula |
| Venda | `-= quantidade` | **Não altera** |
| Cancelamento venda | `+= quantidade` | **Não altera** |

Todas as operações de compra, venda e cancelamento usam:

- `DB::transaction()`
- `lockForUpdate()` na linha do produto (evita race condition)

---

## Custo médio ponderado

Aplicado **a cada item de compra**:

```
novo_custo_medio = (estoque_atual × custo_medio_atual + quantidade × preco_unitario)
                   ÷ (estoque_atual + quantidade)
```

### Exemplos

**Primeira compra:** estoque 0, compra 50 un × R$ 20

- Custo médio = R$ 20,00
- Estoque = 50

**Segunda compra:** estoque 50 × R$ 20, compra 30 un × R$ 10

- Custo médio = (1000 + 300) / 80 = **R$ 16,25**
- Estoque = 80

### Arredondamento

- Banco: `decimal(12,4)` — 4 casas internas
- API/frontend: exibição com 2 casas

---

## Lucro nas vendas

Para cada item, **no momento da venda**:

```
custo_snapshot = produto.custo_medio   (valor ANTES da baixa)
lucro_item     = (preco_unitario - custo_snapshot) × quantidade
total_venda    = Σ (preco_unitario × quantidade)
lucro_venda    = Σ lucro_item
```

O `custo_unitario` é **persistido** em `venda_itens` como snapshot histórico. Relatórios futuros não são afetados por novas compras.

### Exemplo

Produto com custo médio R$ 20, venda de 2 un × R$ 50:

- Lucro item = (50 - 20) × 2 = **R$ 60**

---

## Validações

### Compra

- Fornecedor obrigatório (min 2 chars)
- Pelo menos 1 produto
- Produto duplicado no mesmo payload → **rejeitado**

### Venda

- Cliente obrigatório
- Pelo menos 1 produto
- `quantidade ≤ estoque_atual` — senão aborta transação inteira
- Produto duplicado no mesmo payload → **rejeitado**

### Cancelamento

- Apenas vendas com `status = 'ativa'`
- Reverte estoque item a item
- Não recalcula custo médio
- Não permite cancelar duas vezes

---

## Decisões de UX vs integridade

| Módulo | Criar | Editar | Excluir |
|--------|-------|--------|---------|
| Produtos | Sim | Sim | Sim (com regras) |
| Compras | Sim | **Não** | **Não** |
| Vendas | Sim | **Não** | Cancelar (reverte estoque) |

Compras são **imutáveis** porque alterar/deletar quebraria o histórico de custo médio.

---

## Pontos críticos (evitar erros)

1. **Lucro:** usar custo médio **antes** da baixa de estoque
2. **Venda não altera** custo médio
3. **Sempre** usar transação + lock em operações de estoque
4. **Não editar/deletar** compras após registradas
5. **Cancelamento** reverte estoque inteiro da venda, não parcial
6. **Frontend:** lucro estimado é preview; backend é fonte da verdade

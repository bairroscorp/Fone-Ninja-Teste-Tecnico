<?php

namespace Tests\Feature\Concerns;

trait CriaProduto
{
    protected function criarProduto(string $nome = 'Produto Teste', float $precoVenda = 100): array
    {
        return $this->postJson('/api/produtos', [
            'nome' => $nome,
            'preco_venda' => $precoVenda,
        ])->assertCreated()->json('data');
    }
}

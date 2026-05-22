<?php

namespace App\Services;

use App\Exceptions\EstoqueInsuficienteException;
use App\Models\Produto;

class EstoqueService
{
    public function registrarEntrada(Produto $produto, int $quantidade, float $precoUnitario): void
    {
        $produto = Produto::lockForUpdate()->findOrFail($produto->id);

        $estoqueAtual = $produto->estoque;
        $custoAtual = (float) $produto->custo_medio;

        $novoEstoque = $estoqueAtual + $quantidade;
        $novoCustoMedio = $novoEstoque > 0
            ? (($estoqueAtual * $custoAtual) + ($quantidade * $precoUnitario)) / $novoEstoque
            : 0;

        $produto->update([
            'estoque' => $novoEstoque,
            'custo_medio' => round($novoCustoMedio, 4),
        ]);
    }

    public function registrarSaida(Produto $produto, int $quantidade): float
    {
        $produto = Produto::lockForUpdate()->findOrFail($produto->id);

        if ($produto->estoque < $quantidade) {
            throw new EstoqueInsuficienteException($produto->nome);
        }

        $custoSnapshot = (float) $produto->custo_medio;

        $produto->update([
            'estoque' => $produto->estoque - $quantidade,
        ]);

        return $custoSnapshot;
    }

    public function reverterSaida(Produto $produto, int $quantidade): void
    {
        $produto = Produto::lockForUpdate()->findOrFail($produto->id);

        $produto->update([
            'estoque' => $produto->estoque + $quantidade,
        ]);
    }

    public function reverterEntrada(Produto $produto, int $quantidade, float $precoUnitario): void
    {
        $produto = Produto::lockForUpdate()->findOrFail($produto->id);

        if ($produto->estoque < $quantidade) {
            throw new EstoqueInsuficienteException($produto->nome);
        }

        $estoqueAtual = $produto->estoque;
        $custoAtual = (float) $produto->custo_medio;
        $novoEstoque = $estoqueAtual - $quantidade;

        $novoCustoMedio = $novoEstoque > 0
            ? max(0, (($estoqueAtual * $custoAtual) - ($quantidade * $precoUnitario)) / $novoEstoque)
            : 0;

        $produto->update([
            'estoque' => $novoEstoque,
            'custo_medio' => round($novoCustoMedio, 4),
        ]);
    }
}

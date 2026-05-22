<?php

namespace App\Services;

use App\Models\Compra;
use App\Models\Produto;
use Illuminate\Support\Facades\DB;

class CompraService
{
    public function __construct(
        private EstoqueService $estoqueService
    ) {}

    public function registrar(array $dados): Compra
    {
        return DB::transaction(function () use ($dados) {
            $total = 0;
            $itensData = [];

            foreach ($dados['produtos'] as $item) {
                $subtotal = $item['quantidade'] * $item['preco_unitario'];
                $total += $subtotal;
                $itensData[] = [
                    'produto_id' => $item['id'],
                    'quantidade' => $item['quantidade'],
                    'preco_unitario' => $item['preco_unitario'],
                    'subtotal' => $subtotal,
                ];
            }

            $compra = Compra::create([
                'fornecedor' => $dados['fornecedor'],
                'total' => $total,
                'status' => 'ativa',
                'created_at' => now(),
            ]);

            foreach ($itensData as $itemData) {
                $produto = Produto::findOrFail($itemData['produto_id']);

                $this->estoqueService->registrarEntrada(
                    $produto,
                    $itemData['quantidade'],
                    (float) $itemData['preco_unitario']
                );

                $compra->itens()->create($itemData);
            }

            return $compra->load('itens.produto');
        });
    }

    public function cancelar(Compra $compra): Compra
    {
        if (! $compra->isAtiva()) {
            throw new \InvalidArgumentException('Esta compra já foi cancelada.');
        }

        return DB::transaction(function () use ($compra) {
            $compra = Compra::lockForUpdate()->findOrFail($compra->id);
            $compra->load('itens.produto');

            if (! $compra->isAtiva()) {
                throw new \InvalidArgumentException('Esta compra já foi cancelada.');
            }

            foreach ($compra->itens as $item) {
                $this->estoqueService->reverterEntrada(
                    $item->produto,
                    $item->quantidade,
                    (float) $item->preco_unitario
                );
            }

            $compra->update(['status' => 'cancelada']);

            return $compra->fresh(['itens.produto']);
        });
    }
}

<?php

namespace App\Services;

use App\Models\Produto;
use App\Models\Venda;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class VendaService
{
    public function __construct(
        private EstoqueService $estoqueService
    ) {}

    public function registrar(array $dados): Venda
    {
        return DB::transaction(function () use ($dados) {
            $total = 0;
            $lucroTotal = 0;
            $itensProcessados = [];

            foreach ($dados['produtos'] as $item) {
                $produto = Produto::findOrFail($item['id']);
                $custoSnapshot = $this->estoqueService->registrarSaida($produto, $item['quantidade']);

                $subtotal = $item['quantidade'] * $item['preco_unitario'];
                $lucroItem = ($item['preco_unitario'] - $custoSnapshot) * $item['quantidade'];

                $total += $subtotal;
                $lucroTotal += $lucroItem;

                $itensProcessados[] = [
                    'produto_id' => $item['id'],
                    'quantidade' => $item['quantidade'],
                    'preco_unitario' => $item['preco_unitario'],
                    'custo_unitario' => $custoSnapshot,
                    'lucro' => round($lucroItem, 2),
                    'subtotal' => $subtotal,
                ];
            }

            $venda = Venda::create([
                'cliente' => $dados['cliente'],
                'total' => $total,
                'lucro' => round($lucroTotal, 2),
                'status' => 'ativa',
                'created_at' => now(),
            ]);

            foreach ($itensProcessados as $itemData) {
                $venda->itens()->create($itemData);
            }

            return $venda->load('itens.produto');
        });
    }

    public function cancelar(Venda $venda): Venda
    {
        if (! $venda->isAtiva()) {
            throw new InvalidArgumentException('Esta venda já foi cancelada.');
        }

        return DB::transaction(function () use ($venda) {
            $venda = Venda::lockForUpdate()->findOrFail($venda->id);
            $venda->load('itens.produto');

            if (! $venda->isAtiva()) {
                throw new InvalidArgumentException('Esta venda já foi cancelada.');
            }

            foreach ($venda->itens as $item) {
                $this->estoqueService->reverterSaida($item->produto, $item->quantidade);
            }

            $venda->update(['status' => 'cancelada']);

            return $venda->fresh(['itens.produto']);
        });
    }
}

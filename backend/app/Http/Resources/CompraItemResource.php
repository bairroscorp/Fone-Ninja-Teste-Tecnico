<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompraItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'produto_id' => $this->produto_id,
            'produto_nome' => $this->whenLoaded('produto', fn () => $this->produto->nome),
            'quantidade' => $this->quantidade,
            'preco_unitario' => (float) $this->preco_unitario,
            'subtotal' => (float) $this->subtotal,
        ];
    }
}

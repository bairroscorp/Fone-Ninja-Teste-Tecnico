<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompraResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'fornecedor' => $this->fornecedor,
            'total' => (float) $this->total,
            'status' => $this->status,
            'created_at' => $this->created_at?->toISOString(),
            'itens' => CompraItemResource::collection($this->whenLoaded('itens')),
        ];
    }
}

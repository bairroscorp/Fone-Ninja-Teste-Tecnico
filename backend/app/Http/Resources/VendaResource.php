<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'cliente' => $this->cliente,
            'total' => (float) $this->total,
            'lucro' => (float) $this->lucro,
            'status' => $this->status,
            'created_at' => $this->created_at?->toISOString(),
            'itens' => VendaItemResource::collection($this->whenLoaded('itens')),
        ];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Produto extends Model
{
    protected $fillable = [
        'nome',
        'preco_venda',
        'custo_medio',
        'estoque',
    ];

    protected function casts(): array
    {
        return [
            'preco_venda' => 'decimal:2',
            'custo_medio' => 'decimal:4',
            'estoque' => 'integer',
        ];
    }

    public function compraItens(): HasMany
    {
        return $this->hasMany(CompraItem::class);
    }

    public function vendaItens(): HasMany
    {
        return $this->hasMany(VendaItem::class);
    }
}

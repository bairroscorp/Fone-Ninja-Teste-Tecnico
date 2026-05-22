<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Compra extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'fornecedor',
        'total',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    public function itens(): HasMany
    {
        return $this->hasMany(CompraItem::class);
    }
}

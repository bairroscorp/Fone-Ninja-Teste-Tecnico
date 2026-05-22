<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venda extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'cliente',
        'total',
        'lucro',
        'status',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'lucro' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    public function itens(): HasMany
    {
        return $this->hasMany(VendaItem::class);
    }

    public function isAtiva(): bool
    {
        return $this->status === 'ativa';
    }
}

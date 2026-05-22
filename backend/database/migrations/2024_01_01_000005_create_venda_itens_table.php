<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venda_itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venda_id')->constrained('vendas')->cascadeOnDelete();
            $table->foreignId('produto_id')->constrained('produtos')->restrictOnDelete();
            $table->unsignedInteger('quantidade');
            $table->decimal('preco_unitario', 12, 2);
            $table->decimal('custo_unitario', 12, 4);
            $table->decimal('lucro', 12, 2);
            $table->decimal('subtotal', 12, 2);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venda_itens');
    }
};

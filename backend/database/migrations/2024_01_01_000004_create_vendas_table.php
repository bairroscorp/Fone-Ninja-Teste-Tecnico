<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendas', function (Blueprint $table) {
            $table->id();
            $table->string('cliente');
            $table->decimal('total', 12, 2);
            $table->decimal('lucro', 12, 2);
            $table->enum('status', ['ativa', 'cancelada'])->default('ativa');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendas');
    }
};

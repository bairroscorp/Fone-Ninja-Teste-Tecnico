<?php

namespace Tests\Feature;

use App\Models\Produto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ErpFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_erp_flow(): void
    {
        $produto1 = $this->postJson('/api/produtos', [
            'nome' => 'Fone Bluetooth',
            'preco_venda' => 150,
        ])->assertCreated()->json('data');

        $produto2 = $this->postJson('/api/produtos', [
            'nome' => 'Capa Silicone',
            'preco_venda' => 30,
        ])->assertCreated()->json('data');

        $this->postJson('/api/compras', [
            'fornecedor' => 'Fornecedor X',
            'produtos' => [
                ['id' => $produto1['id'], 'quantidade' => 50, 'preco_unitario' => 20],
                ['id' => $produto2['id'], 'quantidade' => 30, 'preco_unitario' => 10],
            ],
        ])->assertCreated();

        $produto1Atualizado = Produto::find($produto1['id']);
        $this->assertEquals(50, $produto1Atualizado->estoque);
        $this->assertEquals(20, (float) $produto1Atualizado->custo_medio);

        $vendaResponse = $this->postJson('/api/vendas', [
            'cliente' => 'Fulano da Silva',
            'produtos' => [
                ['id' => $produto1['id'], 'quantidade' => 2, 'preco_unitario' => 50],
                ['id' => $produto2['id'], 'quantidade' => 1, 'preco_unitario' => 100],
            ],
        ])->assertCreated();

        $vendaResponse->assertJsonPath('data.total', 200);
        $vendaResponse->assertJsonPath('data.lucro', 150);

        $this->postJson('/api/vendas', [
            'cliente' => 'Teste',
            'produtos' => [
                ['id' => $produto1['id'], 'quantidade' => 999, 'preco_unitario' => 50],
            ],
        ])->assertStatus(422)
            ->assertJsonPath('message', 'Estoque insuficiente para o produto Fone Bluetooth');

        $this->postJson("/api/vendas/{$vendaResponse->json('data.id')}/cancelar")
            ->assertOk()
            ->assertJsonPath('data.status', 'cancelada');

        $this->assertEquals(50, Produto::find($produto1['id'])->estoque);
        $this->assertEquals(30, Produto::find($produto2['id'])->estoque);
    }
}

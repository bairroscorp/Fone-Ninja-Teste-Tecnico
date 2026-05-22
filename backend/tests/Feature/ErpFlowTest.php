<?php

namespace Tests\Feature;

use App\Models\Produto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CriaProduto;
use Tests\TestCase;

/**
 * Fluxo integrado ponta a ponta conforme exemplos do README.
 */
class ErpFlowTest extends TestCase
{
    use CriaProduto;
    use RefreshDatabase;

    public function test_fluxo_completo_readme_produto_compra_venda_cancelamento(): void
    {
        $produto1 = $this->criarProduto('Fone Bluetooth', 150);
        $produto2 = $this->criarProduto('Capa Silicone', 30);

        $this->postJson('/api/compras', [
            'fornecedor' => 'Fornecedor X',
            'produtos' => [
                ['id' => $produto1['id'], 'quantidade' => 50, 'preco_unitario' => 20],
                ['id' => $produto2['id'], 'quantidade' => 30, 'preco_unitario' => 10],
            ],
        ])->assertCreated();

        $this->assertEquals(50, Produto::find($produto1['id'])->estoque);
        $this->assertEquals(20, (float) Produto::find($produto1['id'])->custo_medio);

        $vendaResponse = $this->postJson('/api/vendas', [
            'cliente' => 'Fulano da Silva',
            'produtos' => [
                ['id' => $produto1['id'], 'quantidade' => 2, 'preco_unitario' => 50],
                ['id' => $produto2['id'], 'quantidade' => 1, 'preco_unitario' => 100],
            ],
        ])->assertCreated();

        $vendaResponse->assertJsonPath('data.total', 200);
        $vendaResponse->assertJsonPath('data.lucro', 150);

        $this->postJson("/api/vendas/{$vendaResponse->json('data.id')}/cancelar")
            ->assertOk()
            ->assertJsonPath('data.status', 'cancelada');

        $this->assertEquals(50, Produto::find($produto1['id'])->estoque);
        $this->assertEquals(30, Produto::find($produto2['id'])->estoque);
    }
}

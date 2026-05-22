<?php

namespace Tests\Feature;

use App\Models\Produto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CriaProduto;
use Tests\TestCase;

/**
 * Regras do README — Compras:
 * - POST /api/compras atualiza estoque (entrada)
 * - POST /api/compras atualiza custo médio do produto
 */
class CompraRegrasTest extends TestCase
{
    use CriaProduto;
    use RefreshDatabase;

    public function test_compra_atualiza_estoque_entrada(): void
    {
        $produto = $this->criarProduto();

        $this->postJson('/api/compras', [
            'fornecedor' => 'Fornecedor X',
            'produtos' => [
                ['id' => $produto['id'], 'quantidade' => 50, 'preco_unitario' => 20],
            ],
        ])->assertCreated();

        $this->assertEquals(50, Produto::find($produto['id'])->estoque);
    }

    public function test_primeira_compra_define_custo_medio_igual_preco_unitario(): void
    {
        $produto = $this->criarProduto();

        $this->postJson('/api/compras', [
            'fornecedor' => 'Fornecedor X',
            'produtos' => [
                ['id' => $produto['id'], 'quantidade' => 50, 'preco_unitario' => 20],
            ],
        ])->assertCreated();

        $this->assertEquals(20, (float) Produto::find($produto['id'])->custo_medio);
    }

    public function test_compra_recalcula_custo_medio_ponderado(): void
    {
        $produto = $this->criarProduto();

        $this->postJson('/api/compras', [
            'fornecedor' => 'Fornecedor X',
            'produtos' => [
                ['id' => $produto['id'], 'quantidade' => 50, 'preco_unitario' => 20],
            ],
        ])->assertCreated();

        $this->postJson('/api/compras', [
            'fornecedor' => 'Fornecedor Y',
            'produtos' => [
                ['id' => $produto['id'], 'quantidade' => 30, 'preco_unitario' => 10],
            ],
        ])->assertCreated();

        $atualizado = Produto::find($produto['id']);
        $this->assertEquals(80, $atualizado->estoque);
        // (50*20 + 30*10) / 80 = 16.25
        $this->assertEquals(16.25, (float) $atualizado->custo_medio);
    }

    public function test_compra_com_multiplos_produtos_no_payload(): void
    {
        $produto1 = $this->criarProduto('Produto A', 100);
        $produto2 = $this->criarProduto('Produto B', 50);

        $this->postJson('/api/compras', [
            'fornecedor' => 'Fornecedor X',
            'produtos' => [
                ['id' => $produto1['id'], 'quantidade' => 50, 'preco_unitario' => 20],
                ['id' => $produto2['id'], 'quantidade' => 30, 'preco_unitario' => 10],
            ],
        ])->assertCreated()
            ->assertJsonPath('data.total', 1300);

        $this->assertEquals(50, Produto::find($produto1['id'])->estoque);
        $this->assertEquals(30, Produto::find($produto2['id'])->estoque);
    }

    public function test_rejeita_produto_duplicado_na_compra(): void
    {
        $produto = $this->criarProduto();

        $this->postJson('/api/compras', [
            'fornecedor' => 'Fornecedor X',
            'produtos' => [
                ['id' => $produto['id'], 'quantidade' => 10, 'preco_unitario' => 20],
                ['id' => $produto['id'], 'quantidade' => 5, 'preco_unitario' => 15],
            ],
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['produtos']);
    }

    public function test_cancelar_compra_reverte_estoque_e_custo_medio(): void
    {
        $produto = $this->criarProduto();

        $compraId = $this->postJson('/api/compras', [
            'fornecedor' => 'Fornecedor A',
            'produtos' => [
                ['id' => $produto['id'], 'quantidade' => 10, 'preco_unitario' => 20],
            ],
        ])->assertCreated()->json('data.id');

        $this->postJson("/api/compras/{$compraId}/cancelar")
            ->assertOk()
            ->assertJsonPath('data.status', 'cancelada');

        $atualizado = Produto::find($produto['id']);
        $this->assertEquals(0, $atualizado->estoque);
        $this->assertEquals(0, (float) $atualizado->custo_medio);
    }

    public function test_nao_cancela_compra_se_estoque_ja_foi_vendido(): void
    {
        $produto = $this->criarProduto('Produto Vendido');

        $compraId = $this->postJson('/api/compras', [
            'fornecedor' => 'Fornecedor B',
            'produtos' => [
                ['id' => $produto['id'], 'quantidade' => 10, 'preco_unitario' => 20],
            ],
        ])->assertCreated()->json('data.id');

        $this->postJson('/api/vendas', [
            'cliente' => 'Cliente',
            'produtos' => [
                ['id' => $produto['id'], 'quantidade' => 8, 'preco_unitario' => 50],
            ],
        ])->assertCreated();

        $this->postJson("/api/compras/{$compraId}/cancelar")
            ->assertStatus(422)
            ->assertJsonPath('message', 'Estoque insuficiente para o produto Produto Vendido');
    }

    public function test_nao_cancela_compra_ja_cancelada(): void
    {
        $produto = $this->criarProduto();

        $compraId = $this->postJson('/api/compras', [
            'fornecedor' => 'Fornecedor C',
            'produtos' => [
                ['id' => $produto['id'], 'quantidade' => 5, 'preco_unitario' => 10],
            ],
        ])->assertCreated()->json('data.id');

        $this->postJson("/api/compras/{$compraId}/cancelar")->assertOk();

        $this->postJson("/api/compras/{$compraId}/cancelar")
            ->assertStatus(422)
            ->assertJsonPath('message', 'Esta compra já foi cancelada.');
    }
}

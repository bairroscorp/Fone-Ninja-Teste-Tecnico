<?php

namespace Tests\Feature;

use App\Models\Produto;
use App\Models\Venda;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CriaProduto;
use Tests\TestCase;

/**
 * Regras do README — Vendas:
 * - Validar estoque suficiente
 * - Baixar estoque (saída)
 * - Calcular lucro da venda
 * - Retornar total e lucro no JSON
 * - Cancelar venda (opcional) e reverter estoque
 */
class VendaRegrasTest extends TestCase
{
    use CriaProduto;
    use RefreshDatabase;

    private function produtoComEstoque(int $estoque = 50, float $custoMedio = 20): array
    {
        $produto = $this->criarProduto('Fone Bluetooth', 150);

        $this->postJson('/api/compras', [
            'fornecedor' => 'Fornecedor X',
            'produtos' => [
                ['id' => $produto['id'], 'quantidade' => $estoque, 'preco_unitario' => $custoMedio],
            ],
        ])->assertCreated();

        return $produto;
    }

    public function test_valida_estoque_insuficiente(): void
    {
        $produto = $this->produtoComEstoque(4, 20);

        $this->postJson('/api/vendas', [
            'cliente' => 'Fulano da Silva',
            'produtos' => [
                ['id' => $produto['id'], 'quantidade' => 19, 'preco_unitario' => 333],
            ],
        ])->assertStatus(422)
            ->assertJsonPath('message', 'Estoque insuficiente para o produto Fone Bluetooth');

        $this->assertEquals(4, Produto::find($produto['id'])->estoque);
    }

    public function test_venda_baixa_estoque(): void
    {
        $produto = $this->produtoComEstoque(50, 20);

        $this->postJson('/api/vendas', [
            'cliente' => 'Fulano da Silva',
            'produtos' => [
                ['id' => $produto['id'], 'quantidade' => 2, 'preco_unitario' => 50],
            ],
        ])->assertCreated();

        $this->assertEquals(48, Produto::find($produto['id'])->estoque);
    }

    public function test_venda_calcula_lucro_corretamente(): void
    {
        $produto1 = $this->produtoComEstoque(50, 20);
        $produto2 = $this->criarProduto('Capa Silicone', 30);

        $this->postJson('/api/compras', [
            'fornecedor' => 'Fornecedor X',
            'produtos' => [
                ['id' => $produto2['id'], 'quantidade' => 30, 'preco_unitario' => 10],
            ],
        ])->assertCreated();

        $response = $this->postJson('/api/vendas', [
            'cliente' => 'Fulano da Silva',
            'produtos' => [
                ['id' => $produto1['id'], 'quantidade' => 2, 'preco_unitario' => 50],
                ['id' => $produto2['id'], 'quantidade' => 1, 'preco_unitario' => 100],
            ],
        ])->assertCreated();

        // total: 2*50 + 1*100 = 200
        // lucro: 2*(50-20) + 1*(100-10) = 60 + 90 = 150
        $response->assertJsonPath('data.total', 200);
        $response->assertJsonPath('data.lucro', 150);
    }

    public function test_venda_retorna_total_e_lucro_no_json(): void
    {
        $produto = $this->produtoComEstoque(10, 20);

        $response = $this->postJson('/api/vendas', [
            'cliente' => 'Cliente Teste',
            'produtos' => [
                ['id' => $produto['id'], 'quantidade' => 2, 'preco_unitario' => 50],
            ],
        ])->assertCreated();

        $response->assertJsonStructure([
            'data' => ['id', 'cliente', 'total', 'lucro', 'status', 'itens'],
            'total',
            'lucro',
        ]);

        $response->assertJsonPath('data.total', 100);
        $response->assertJsonPath('data.lucro', 60);
        $response->assertJsonPath('total', 100);
        $response->assertJsonPath('lucro', 60);
    }

    public function test_venda_nao_altera_custo_medio(): void
    {
        $produto = $this->produtoComEstoque(50, 20);

        $this->postJson('/api/vendas', [
            'cliente' => 'Cliente',
            'produtos' => [
                ['id' => $produto['id'], 'quantidade' => 5, 'preco_unitario' => 80],
            ],
        ])->assertCreated();

        $this->assertEquals(20, (float) Produto::find($produto['id'])->custo_medio);
    }

    public function test_venda_persiste_custo_unitario_snapshot_nos_itens(): void
    {
        $produto = $this->produtoComEstoque(50, 20);

        $vendaId = $this->postJson('/api/vendas', [
            'cliente' => 'Cliente',
            'produtos' => [
                ['id' => $produto['id'], 'quantidade' => 2, 'preco_unitario' => 50],
            ],
        ])->assertCreated()->json('data.id');

        $item = Venda::with('itens')->find($vendaId)->itens->first();
        $this->assertEquals(20, (float) $item->custo_unitario);
        $this->assertEquals(60, (float) $item->lucro);
    }

    public function test_cancelar_venda_reverte_estoque(): void
    {
        $produto = $this->produtoComEstoque(50, 20);

        $vendaId = $this->postJson('/api/vendas', [
            'cliente' => 'Fulano',
            'produtos' => [
                ['id' => $produto['id'], 'quantidade' => 2, 'preco_unitario' => 50],
            ],
        ])->assertCreated()->json('data.id');

        $this->assertEquals(48, Produto::find($produto['id'])->estoque);

        $this->postJson("/api/vendas/{$vendaId}/cancelar")
            ->assertOk()
            ->assertJsonPath('data.status', 'cancelada');

        $this->assertEquals(50, Produto::find($produto['id'])->estoque);
    }

    public function test_nao_cancela_venda_ja_cancelada(): void
    {
        $produto = $this->produtoComEstoque(10, 20);

        $vendaId = $this->postJson('/api/vendas', [
            'cliente' => 'Cliente',
            'produtos' => [
                ['id' => $produto['id'], 'quantidade' => 1, 'preco_unitario' => 50],
            ],
        ])->assertCreated()->json('data.id');

        $this->postJson("/api/vendas/{$vendaId}/cancelar")->assertOk();

        $this->postJson("/api/vendas/{$vendaId}/cancelar")
            ->assertStatus(422)
            ->assertJsonPath('message', 'Esta venda já foi cancelada.');
    }

    public function test_rejeita_produto_duplicado_na_venda(): void
    {
        $produto = $this->produtoComEstoque(50, 20);

        $this->postJson('/api/vendas', [
            'cliente' => 'Cliente',
            'produtos' => [
                ['id' => $produto['id'], 'quantidade' => 2, 'preco_unitario' => 50],
                ['id' => $produto['id'], 'quantidade' => 3, 'preco_unitario' => 40],
            ],
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['produtos']);
    }
}

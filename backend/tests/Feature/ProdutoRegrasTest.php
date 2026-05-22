<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CriaProduto;
use Tests\TestCase;

/**
 * Regras do README — Produtos:
 * - POST /api/produtos: nome (min 3), preco_venda (> 0), estoque_inicial = 0
 * - GET /api/produtos: id, nome, custo_medio, preco_venda, estoque
 */
class ProdutoRegrasTest extends TestCase
{
    use CriaProduto;
    use RefreshDatabase;

    public function test_cadastro_inicia_estoque_zero(): void
    {
        $produto = $this->criarProduto();

        $this->assertEquals(0, $produto['estoque']);
    }

    public function test_cadastro_inicia_custo_medio_zero(): void
    {
        $produto = $this->criarProduto();

        $this->assertEquals(0, $produto['custo_medio']);
    }

    public function test_valida_nome_obrigatorio(): void
    {
        $this->postJson('/api/produtos', [
            'preco_venda' => 10,
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['nome']);
    }

    public function test_valida_nome_minimo_tres_caracteres(): void
    {
        $this->postJson('/api/produtos', [
            'nome' => 'ab',
            'preco_venda' => 10,
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['nome']);
    }

    public function test_valida_preco_venda_deve_ser_positivo(): void
    {
        $this->postJson('/api/produtos', [
            'nome' => 'Produto Valido',
            'preco_venda' => 0,
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['preco_venda']);

        $this->postJson('/api/produtos', [
            'nome' => 'Produto Valido 2',
            'preco_venda' => -5,
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['preco_venda']);
    }

    public function test_listagem_retorna_campos_exigidos_pelo_readme(): void
    {
        $this->criarProduto('Fone Bluetooth', 150);

        $response = $this->getJson('/api/produtos')->assertOk();

        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'nome', 'custo_medio', 'preco_venda', 'estoque'],
            ],
        ]);

        $item = $response->json('data.0');
        $this->assertEquals('Fone Bluetooth', $item['nome']);
        $this->assertEquals(150, $item['preco_venda']);
        $this->assertEquals(0, $item['estoque']);
        $this->assertEquals(0, $item['custo_medio']);
    }
}

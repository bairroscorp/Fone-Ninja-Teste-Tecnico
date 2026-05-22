<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProdutoRequest;
use App\Http\Requests\UpdateProdutoRequest;
use App\Http\Resources\ProdutoResource;
use App\Models\Produto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProdutoController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $produtos = Produto::orderBy('nome')->get();

        return ProdutoResource::collection($produtos);
    }

    public function store(StoreProdutoRequest $request): JsonResponse
    {
        $produto = Produto::create([
            'nome' => $request->nome,
            'preco_venda' => $request->preco_venda,
            'custo_medio' => 0,
            'estoque' => 0,
        ]);

        return (new ProdutoResource($produto))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Produto $produto): ProdutoResource
    {
        return new ProdutoResource($produto);
    }

    public function update(UpdateProdutoRequest $request, Produto $produto): ProdutoResource
    {
        $produto->update($request->validated());

        return new ProdutoResource($produto->fresh());
    }

    public function destroy(Produto $produto): JsonResponse
    {
        if ($produto->estoque > 0) {
            return response()->json([
                'message' => 'Não é possível excluir produto com estoque disponível.',
            ], 422);
        }

        if ($produto->compraItens()->exists() || $produto->vendaItens()->exists()) {
            return response()->json([
                'message' => 'Não é possível excluir produto com histórico de compras ou vendas.',
            ], 422);
        }

        $produto->delete();

        return response()->json(['message' => 'Produto excluído com sucesso.']);
    }
}

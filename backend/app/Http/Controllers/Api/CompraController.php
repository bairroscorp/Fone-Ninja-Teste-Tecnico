<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompraRequest;
use App\Http\Resources\CompraResource;
use App\Models\Compra;
use App\Services\CompraService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use InvalidArgumentException;

class CompraController extends Controller
{
    public function __construct(
        private CompraService $compraService
    ) {}

    public function index(): AnonymousResourceCollection
    {
        $compras = Compra::with('itens.produto')
            ->orderByDesc('created_at')
            ->paginate(15);

        return CompraResource::collection($compras);
    }

    public function show(Compra $compra): CompraResource
    {
        $compra->load('itens.produto');

        return new CompraResource($compra);
    }

    public function store(StoreCompraRequest $request): JsonResponse
    {
        $compra = $this->compraService->registrar($request->validated());

        return (new CompraResource($compra))
            ->response()
            ->setStatusCode(201);
    }

    public function cancelar(Compra $compra): CompraResource|JsonResponse
    {
        try {
            $compra = $this->compraService->cancelar($compra);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return new CompraResource($compra);
    }
}

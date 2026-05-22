<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVendaRequest;
use App\Http\Resources\VendaResource;
use App\Models\Venda;
use App\Services\VendaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use InvalidArgumentException;

class VendaController extends Controller
{
    public function __construct(
        private VendaService $vendaService
    ) {}

    public function index(): AnonymousResourceCollection
    {
        $vendas = Venda::with('itens.produto')
            ->orderByDesc('created_at')
            ->paginate(15);

        return VendaResource::collection($vendas);
    }

    public function show(Venda $venda): VendaResource
    {
        $venda->load('itens.produto');

        return new VendaResource($venda);
    }

    public function store(StoreVendaRequest $request): JsonResponse
    {
        $venda = $this->vendaService->registrar($request->validated());

        return (new VendaResource($venda))
            ->additional([
                'total' => (float) $venda->total,
                'lucro' => (float) $venda->lucro,
            ])
            ->response()
            ->setStatusCode(201);
    }

    public function cancelar(Venda $venda): VendaResource|JsonResponse
    {
        try {
            $venda = $this->vendaService->cancelar($venda);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return new VendaResource($venda);
    }
}

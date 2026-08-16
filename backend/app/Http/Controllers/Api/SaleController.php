<?php

namespace App\Http\Controllers\Api;

use App\Application\UseCases\Sales\ManageSales;
use App\Http\Controllers\Controller;
use App\Http\Requests\SaleRequest;
use App\Http\Resources\SaleResource;
use App\Models\Sale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function __construct(private readonly ManageSales $useCase) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Sale::class);
        $sales = $this->useCase->list([
            'search' => $request->string('search')->toString(),
            'start_date' => $request->date('start_date'),
            'end_date' => $request->date('end_date'),
        ], $request->integer('per_page', 10));

        return response()->json(['data' => SaleResource::collection($sales)]);
    }

    public function store(SaleRequest $request): JsonResponse
    {
        $this->authorize('create', Sale::class);
        $sale = $this->useCase->create($request->validated(), $request->user()->id);

        return response()->json([
            'message' => 'Transaksi berhasil dibuat.',
            'data' => new SaleResource($sale),
        ], 201);
    }

    public function show(Sale $sale): JsonResponse
    {
        $this->authorize('view', $sale);

        return response()->json([
            'data' => new SaleResource($this->useCase->show($sale)),
        ]);
    }

    public function update(SaleRequest $request, Sale $sale): JsonResponse
    {
        $this->authorize('update', $sale);
        $sale = $this->useCase->update($sale->load('details.medicine'), $request->validated());

        return response()->json([
            'message' => 'Transaksi berhasil diperbarui.',
            'data' => new SaleResource($sale),
        ]);
    }

    public function destroy(Sale $sale): JsonResponse
    {
        $this->authorize('delete', $sale);
        $this->useCase->delete($sale->load('details.medicine'));

        return response()->json(['message' => 'Transaksi berhasil dihapus.']);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Application\UseCases\Purchases\ManagePurchases;
use App\Http\Controllers\Controller;
use App\Http\Requests\PurchaseRequest;
use App\Http\Requests\PurchaseReturnRequest;
use App\Http\Resources\PurchaseResource;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PurchaseController extends Controller
{
    public function __construct(private readonly ManagePurchases $useCase) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Purchase::class);
        $purchases = $this->useCase->list([
            'search' => $request->string('search')->toString(),
            'supplier_id' => $request->integer('supplier_id') ?: null,
            'retur_status' => $request->string('retur_status')->toString() ?: null,
        ], $request->integer('per_page', 10));

        return response()->json(['data' => PurchaseResource::collection($purchases)]);
    }

    public function store(PurchaseRequest $request): JsonResponse
    {
        $this->authorize('create', Purchase::class);
        $payload = $this->payloadWithUploadedInvoice($request);
        $purchase = $this->useCase->create($payload, $request->user()->id);

        return response()->json([
            'message' => 'Faktur pembelian berhasil dibuat.',
            'data' => new PurchaseResource($purchase),
        ], 201);
    }

    public function show(Purchase $purchase): JsonResponse
    {
        $this->authorize('view', $purchase);

        return response()->json([
            'data' => new PurchaseResource($this->useCase->show($purchase)),
        ]);
    }

    public function update(PurchaseRequest $request, Purchase $purchase): JsonResponse
    {
        $this->authorize('update', $purchase);
        $oldFile = $purchase->file_faktur;
        $payload = $this->payloadWithUploadedInvoice($request);
        $purchase = $this->useCase->update($purchase, $payload);

        if (($payload['file_faktur'] ?? null) && $oldFile) {
            Storage::disk('public')->delete($oldFile);
        }

        return response()->json([
            'message' => 'Faktur pembelian berhasil diperbarui.',
            'data' => new PurchaseResource($purchase),
        ]);
    }

    public function destroy(Purchase $purchase): JsonResponse
    {
        $this->authorize('delete', $purchase);
        $file = $purchase->file_faktur;
        $this->useCase->delete($purchase);

        if ($file) {
            Storage::disk('public')->delete($file);
        }

        return response()->json(['message' => 'Faktur pembelian berhasil dihapus.']);
    }

    public function markReturn(PurchaseReturnRequest $request, Purchase $purchase, PurchaseDetail $purchaseDetail): JsonResponse
    {
        $this->authorize('update', $purchase);
        $purchase = $this->useCase->markReturn($purchase, $purchaseDetail, $request->validated());

        return response()->json([
            'message' => 'Status retur faktur berhasil diperbarui.',
            'data' => new PurchaseResource($purchase),
        ]);
    }

    private function payloadWithUploadedInvoice(PurchaseRequest $request): array
    {
        $payload = $request->validated();

        if ($request->hasFile('file_faktur')) {
            $payload['file_faktur'] = $request->file('file_faktur')->store('faktur-pembelian', 'public');
        }

        return $payload;
    }
}

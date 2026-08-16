<?php

namespace App\Http\Controllers\Api;

use App\Application\UseCases\Suppliers\ManageSuppliers;
use App\Http\Controllers\Controller;
use App\Http\Requests\SupplierRequest;
use App\Http\Resources\SupplierResource;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function __construct(private readonly ManageSuppliers $useCase) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Supplier::class);
        $suppliers = $this->useCase->list([
            'search' => $request->string('search')->toString(),
        ], $request->integer('per_page', 10));

        return response()->json(['data' => SupplierResource::collection($suppliers)]);
    }

    public function store(SupplierRequest $request): JsonResponse
    {
        $this->authorize('create', Supplier::class);
        $supplier = $this->useCase->create($request->validated());

        return response()->json([
            'message' => 'Supplier berhasil dibuat.',
            'data' => new SupplierResource($supplier),
        ], 201);
    }

    public function show(Supplier $supplier): JsonResponse
    {
        $this->authorize('view', $supplier);

        return response()->json([
            'data' => new SupplierResource($this->useCase->show($supplier)),
        ]);
    }

    public function update(SupplierRequest $request, Supplier $supplier): JsonResponse
    {
        $this->authorize('update', $supplier);
        $supplier = $this->useCase->update($supplier, $request->validated());

        return response()->json([
            'message' => 'Supplier berhasil diperbarui.',
            'data' => new SupplierResource($supplier),
        ]);
    }

    public function destroy(Supplier $supplier): JsonResponse
    {
        $this->authorize('delete', $supplier);
        $this->useCase->delete($supplier);

        return response()->json(['message' => 'Supplier berhasil dihapus.']);
    }
}

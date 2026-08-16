<?php

namespace App\Http\Controllers\Api;

use App\Application\UseCases\Medicines\ManageMedicines;
use App\Http\Controllers\Controller;
use App\Http\Requests\MedicineRequest;
use App\Http\Resources\MedicineResource;
use App\Models\Medicine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MedicineController extends Controller
{
    public function __construct(private readonly ManageMedicines $useCase) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Medicine::class);
        $medicines = $this->useCase->list([
            'search' => $request->string('search')->toString(),
            'low_stock' => $request->boolean('low_stock'),
            'near_expired' => $request->boolean('near_expired'),
        ], $request->integer('per_page', 10));

        return response()->json(['data' => MedicineResource::collection($medicines)]);
    }

    public function store(MedicineRequest $request): JsonResponse
    {
        $this->authorize('create', Medicine::class);
        $medicine = $this->useCase->create($request->validated());

        return response()->json([
            'message' => 'Obat berhasil dibuat.',
            'data' => new MedicineResource($medicine),
        ], 201);
    }

    public function show(Medicine $medicine): JsonResponse
    {
        $this->authorize('view', $medicine);

        return response()->json([
            'data' => new MedicineResource($this->useCase->show($medicine)),
        ]);
    }

    public function update(MedicineRequest $request, Medicine $medicine): JsonResponse
    {
        $this->authorize('update', $medicine);
        $medicine = $this->useCase->update($medicine, $request->validated());

        return response()->json([
            'message' => 'Obat berhasil diperbarui.',
            'data' => new MedicineResource($medicine),
        ]);
    }

    public function destroy(Medicine $medicine): JsonResponse
    {
        $this->authorize('delete', $medicine);
        $this->useCase->delete($medicine);

        return response()->json(['message' => 'Obat berhasil dihapus.']);
    }
}

<?php

namespace App\Application\UseCases\Medicines;

use App\Domain\Contracts\MedicineRepository;
use App\Models\Medicine;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class ManageMedicines
{
    public function __construct(private readonly MedicineRepository $medicines) {}

    public function list(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->medicines->paginate($filters, min(max($perPage, 1), 100));
    }

    public function create(array $data): Medicine
    {
        return $this->medicines->create($data);
    }

    public function show(Medicine $medicine): Medicine
    {
        return $medicine->load('supplier');
    }

    public function update(Medicine $medicine, array $data): Medicine
    {
        return $this->medicines->update($medicine, $data);
    }

    public function delete(Medicine $medicine): void
    {
        if ($this->medicines->hasSales($medicine)) {
            throw ValidationException::withMessages([
                'medicine' => 'Obat yang sudah dipakai transaksi tidak dapat dihapus.',
            ]);
        }

        $this->medicines->delete($medicine);
    }
}

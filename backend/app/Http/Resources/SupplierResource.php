<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nama_supplier' => $this->nama_supplier,
            'alamat' => $this->alamat,
            'telepon' => $this->telepon,
            'medicines_count' => $this->whenCounted('medicines'),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}

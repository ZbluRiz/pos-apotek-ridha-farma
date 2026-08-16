<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'supplier_id' => $this->supplier_id,
            'supplier' => new SupplierResource($this->whenLoaded('supplier')),
            'kode_obat' => $this->kode_obat,
            'nama_obat' => $this->nama_obat,
            'kategori' => $this->kategori,
            'satuan' => $this->satuan,
            'harga_beli' => (float) $this->harga_beli,
            'harga_jual' => (float) $this->harga_jual,
            'stok' => $this->stok,
            'stok_minimum' => $this->stok_minimum,
            'tanggal_expired' => $this->tanggal_expired?->toDateString(),
            'prioritas_owner' => $this->prioritas_owner,
            'is_low_stock' => $this->stok <= $this->stok_minimum,
            'is_near_expired' => $this->tanggal_expired?->lte(now()->addDays(30)) ?? false,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}

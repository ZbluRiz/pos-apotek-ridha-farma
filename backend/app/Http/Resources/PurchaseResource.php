<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PurchaseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'supplier_id' => $this->supplier_id,
            'supplier' => new SupplierResource($this->whenLoaded('supplier')),
            'user_id' => $this->user_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'nomor_faktur' => $this->nomor_faktur,
            'tanggal_faktur' => $this->tanggal_faktur?->toDateString(),
            'total_harga' => (float) $this->total_harga,
            'file_faktur' => $this->file_faktur,
            'file_faktur_url' => $this->file_faktur ? Storage::url($this->file_faktur) : null,
            'catatan' => $this->catatan,
            'details' => PurchaseDetailResource::collection($this->whenLoaded('details')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}

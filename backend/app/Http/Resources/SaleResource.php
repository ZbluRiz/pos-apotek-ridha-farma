<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nomor_transaksi' => $this->nomor_transaksi,
            'tanggal' => $this->tanggal?->toISOString(),
            'total_harga' => (float) $this->total_harga,
            'user' => new UserResource($this->whenLoaded('user')),
            'details' => SaleDetailResource::collection($this->whenLoaded('details')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}

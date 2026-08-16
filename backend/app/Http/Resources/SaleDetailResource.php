<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'medicine_id' => $this->medicine_id,
            'medicine' => new MedicineResource($this->whenLoaded('medicine')),
            'qty' => $this->qty,
            'harga' => (float) $this->harga,
            'subtotal' => (float) $this->subtotal,
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isExpired = $this->tanggal_expired?->isPast() ?? false;

        return [
            'id' => $this->id,
            'medicine_id' => $this->medicine_id,
            'medicine' => new MedicineResource($this->whenLoaded('medicine')),
            'nomor_batch' => $this->nomor_batch,
            'qty' => $this->qty,
            'harga_beli' => (float) $this->harga_beli,
            'subtotal' => (float) $this->subtotal,
            'tanggal_expired' => $this->tanggal_expired?->toDateString(),
            'is_expired' => $isExpired,
            'days_to_expired' => $this->tanggal_expired ? now()->startOfDay()->diffInDays($this->tanggal_expired, false) : null,
            'qty_retur' => $this->qty_retur,
            'status_retur' => $this->status_retur,
            'tanggal_retur' => $this->tanggal_retur?->toDateString(),
            'catatan_retur' => $this->catatan_retur,
            'can_be_returned' => $isExpired && $this->status_retur !== 'diretur',
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SawRecommendationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'ranking' => $this['ranking'],
            'medicine_id' => $this['medicine']->id,
            'kode_obat' => $this['medicine']->kode_obat,
            'nama_obat' => $this['medicine']->nama_obat,
            'stok' => $this['medicine']->stok,
            'tanggal_expired' => $this['tanggal_expired_digunakan'],
            'sisa_hari_expired' => $this['sisa_hari_expired'],
            'prioritas_owner' => $this['medicine']->prioritas_owner,
            'nilai_preferensi' => round($this['score'], 4),
            'kriteria' => $this['criteria'],
            'normalisasi' => $this['normalized'],
            'perkalian_bobot' => $this['weighted'],
        ];
    }
}

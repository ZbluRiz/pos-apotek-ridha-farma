<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'medicine_id',
        'nomor_batch',
        'qty',
        'harga_beli',
        'subtotal',
        'tanggal_expired',
        'qty_retur',
        'status_retur',
        'tanggal_retur',
        'catatan_retur',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'integer',
            'harga_beli' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'tanggal_expired' => 'date',
            'qty_retur' => 'integer',
            'tanggal_retur' => 'date',
        ];
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }
}

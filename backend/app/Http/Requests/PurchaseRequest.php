<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $purchaseId = $this->route('purchase')?->id;

        return [
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'nomor_faktur' => ['required', 'string', 'max:100', Rule::unique('purchases', 'nomor_faktur')->ignore($purchaseId)],
            'tanggal_faktur' => ['required', 'date'],
            'file_faktur' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:2048'],
            'catatan' => ['nullable', 'string', 'max:1000'],
            'details' => ['required', 'array', 'min:1'],
            'details.*.medicine_id' => ['required', 'exists:medicines,id'],
            'details.*.nomor_batch' => ['nullable', 'string', 'max:100'],
            'details.*.qty' => ['required', 'integer', 'min:1'],
            'details.*.harga_beli' => ['required', 'numeric', 'min:0'],
            'details.*.tanggal_expired' => ['required', 'date'],
        ];
    }
}

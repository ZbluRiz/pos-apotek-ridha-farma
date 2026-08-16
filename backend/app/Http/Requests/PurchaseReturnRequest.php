<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PurchaseReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status_retur' => ['required', Rule::in(['belum_retur', 'diajukan_retur', 'diretur', 'ditolak'])],
            'qty_retur' => ['required', 'integer', 'min:0'],
            'tanggal_retur' => ['nullable', 'date'],
            'catatan_retur' => ['nullable', 'string', 'max:1000'],
        ];
    }
}

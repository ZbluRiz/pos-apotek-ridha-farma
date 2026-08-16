<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tanggal' => ['required', 'date'],
            'details' => ['required', 'array', 'min:1'],
            'details.*.medicine_id' => ['required', 'exists:medicines,id'],
            'details.*.qty' => ['required', 'integer', 'min:1'],
        ];
    }
}

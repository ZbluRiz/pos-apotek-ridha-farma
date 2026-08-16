<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MedicineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $medicineId = $this->route('medicine')?->id;

        return [
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'kode_obat' => ['required', 'string', 'max:100', Rule::unique('medicines', 'kode_obat')->ignore($medicineId)],
            'nama_obat' => ['required', 'string', 'max:255'],
            'kategori' => ['required', 'string', 'max:100'],
            'satuan' => ['required', 'string', 'max:50'],
            'harga_beli' => ['required', 'numeric', 'min:0'],
            'harga_jual' => ['required', 'numeric', 'min:0'],
            'stok' => ['required', 'integer', 'min:0'],
            'stok_minimum' => ['required', 'integer', 'min:0'],
            'tanggal_expired' => ['required', 'date'],
            'prioritas_owner' => ['required', 'integer', 'min:1', 'max:5'],
        ];
    }
}

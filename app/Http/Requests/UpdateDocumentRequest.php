<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'document_number' => ['nullable', 'string', 'max:100'],
            'entity_name' => ['nullable', 'string', 'max:255'],
            'document_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'total_amount' => ['nullable', 'numeric', 'min:0', 'max:9999999999999'],
            'category_id' => ['required', 'exists:categories,id'],
            'importance_level' => ['required', 'in:penting,biasa,rahasia'],
            'payment_status' => ['required', 'in:n/a,belum_lunas,lunas'],
            'items' => ['nullable', 'array', 'max:200'],
            'items.*.item_name' => ['nullable', 'string', 'max:255'],
            'items.*.quantity' => ['nullable', 'integer', 'min:1', 'max:1000000'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0', 'max:9999999999999'],
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'judul',
            'document_number' => 'nomor dokumen',
            'entity_name' => 'nama supplier/instansi',
            'document_date' => 'tanggal dokumen',
            'due_date' => 'tanggal jatuh tempo',
            'total_amount' => 'total nominal',
            'category_id' => 'kategori',
            'importance_level' => 'tingkat kepentingan',
            'payment_status' => 'status pembayaran',
            'items.*.item_name' => 'nama item',
            'items.*.quantity' => 'jumlah',
            'items.*.unit_price' => 'harga satuan',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'category_id.required' => 'Pilih kategori dokumen.',
            'numeric' => ':attribute harus berupa angka.',
            'integer' => ':attribute harus berupa bilangan bulat.',
            'date' => ':attribute bukan tanggal yang valid.',
            'min' => ':attribute tidak boleh kurang dari :min.',
            'max' => ':attribute terlalu besar/panjang.',
        ];
    }
}

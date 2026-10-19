<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'document' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'document.required' => 'Pilih file dokumen terlebih dahulu.',
            'document.file' => 'File tidak valid.',
            'document.mimes' => 'Format file harus JPG, PNG, WEBP, atau PDF.',
            'document.max' => 'Ukuran file maksimal 10 MB.',
            'document.uploaded' => 'File gagal diunggah. Cek batas ukuran upload di php.ini (upload_max_filesize).',
        ];
    }
}

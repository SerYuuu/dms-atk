<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['Faktur Supplier', 'faktur-supplier', 'Faktur dan nota pembelian dari supplier.'],
            ['Surat Kontrak', 'surat-kontrak', 'Perjanjian kerja sama dengan supplier, klien, atau instansi.'],
            ['Dokumen Pajak', 'dokumen-pajak', 'Bukti setor, SPT, dan dokumen perpajakan lainnya.'],
            ['Katalog Harga', 'katalog-harga', 'Daftar harga barang dari supplier.'],
            ['Lainnya', 'lainnya', 'Dokumen operasional yang tidak masuk kategori lain.'],
        ];

        foreach ($categories as [$name, $slug, $description]) {
            Category::updateOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'description' => $description]
            );
        }
    }
}

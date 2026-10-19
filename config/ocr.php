<?php

return [
    // Kalau tesseract sudah ada di PATH, cukup 'tesseract'.
    // Kalau tidak, isi lewat .env, misalnya:
    // TESSERACT_PATH="C:/Program Files/Tesseract-OCR/tesseract.exe"
    'tesseract_path' => env('TESSERACT_PATH', 'tesseract'),

    // Bahasa OCR. Kalau paket 'ind' belum terpasang, otomatis jatuh ke 'eng'.
    'language' => env('OCR_LANG', 'ind+eng'),

    // 11 = teks tersebar (sparse text): paling baik untuk nota dengan banyak blok/kolom.
    'psm' => (int) env('OCR_PSM', 11),

    // Kata dengan keyakinan di bawah ini dibuang (0-100). Menekan sampah dari tulisan tangan.
    'min_confidence' => (int) env('OCR_MIN_CONFIDENCE', 30),

    'timeout' => (int) env('OCR_TIMEOUT', 60),
];

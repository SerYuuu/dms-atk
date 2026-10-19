<?php

namespace App\Services;

use Carbon\Carbon;

class OcrEngineService
{
    public function __construct(
        private ImagePreprocessingService $preprocessor,
        private TesseractClient $tesseract,
    ) {
    }

    /** Tesseract hanya membaca gambar. PDF disimpan apa adanya dan diisi manual. */
    public function canRead(string $mimeType): bool
    {
        return in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp'], true);
    }

    /**
     * Jalankan seluruh pipeline OCR lokal untuk satu gambar.
     *
     * @return array{
     *     text: string,
     *     fields: array{entity_name: ?string, document_number: ?string, document_date: ?string, total_amount: float|int|null, payment_status: ?string},
     *     sources: array<string, ?string>,
     *     warnings: string[]
     * }
     */
    public function analyze(string $absolutePath): array
    {
        // Kalau GD tidak tersedia, pakai gambar asli untuk kedua jalur (zona angka dilewati).
        $variants = $this->preprocessor->variants($absolutePath)
            ?? ['gray' => $absolutePath, 'red' => $absolutePath, 'temp' => []];

        $warnings = [];

        try {
            $sameImage = $variants['gray'] === $variants['red'];
            $psm = (int) config('ocr.psm');

            // Pass 1 (gray): teks cetak. Pass 2 (red): tabel, stempel dan kertas berwarna hilang.
            $grayWords = $this->tesseract->words($variants['gray'], $psm);
            $redWords = $sameImage ? [] : $this->tesseract->words($variants['red'], $psm);
            $allWords = array_merge($grayWords, $redWords);

            $lines = $this->mergeLines(
                $this->linesFromWords($grayWords),
                $this->linesFromWords($redWords)
            );
            $text = implode("\n", $lines);

            $fields = $this->parse($text);
            $sources = [];
            foreach ($fields as $key => $value) {
                $sources[$key] = $value !== null ? 'text' : null;
            }

            // Area angka dengan whitelist: hanya dijalankan kalau teks tidak menemukan total
            // (mis. total tulisan tangan), supaya tidak membuang waktu OCR tambahan.
            if ($fields['total_amount'] === null) {
                $zoneTotal = $this->readTotalFromZone($variants, $allWords);

                if ($zoneTotal !== null) {
                    $fields['total_amount'] = $zoneTotal;
                    $sources['total_amount'] = 'zone';
                }
            }

            // Nomor dokumen: kalau tidak ketemu di teks, baca area di kanan kata "No/Nota/Invoice".
            if ($fields['document_number'] === null) {
                $zoneNumber = $this->readNumberFromZone($variants, $allWords);

                if ($zoneNumber !== null) {
                    $fields['document_number'] = $zoneNumber;
                    $sources['document_number'] = 'zone';
                }
            }

            // Status pembayaran dari stempel merah (mis. LUNAS) yang tidak terbaca di teks biasa.
            if ($fields['payment_status'] === null && ! empty($variants['stamp'])) {
                $stampStatus = $this->readStampStatus($variants['stamp']);

                if ($stampStatus !== null) {
                    $fields['payment_status'] = $stampStatus;
                    $sources['payment_status'] = 'stamp';
                }
            }

            if ($text === '') {
                $warnings[] = 'Tidak ada teks yang terbaca dari gambar. Coba foto yang lebih jelas, atau isi manual.';
            } elseif ($this->lowConfidenceRatio($allWords) > 0.4) {
                $warnings[] = 'Sebagian teks (kemungkinan tulisan tangan) kurang terbaca. Periksa kembali semua isian.';
            }

            return [
                'text' => $text,
                'fields' => $fields,
                'sources' => $sources,
                'warnings' => $warnings,
            ];
        } finally {
            $this->preprocessor->cleanup($variants['temp']);
        }
    }

    /**
     * Susun kata (dengan posisi) menjadi baris teks. Kata dikelompokkan menurut tinggi (y);
     * celah horizontal lebar ditandai " | " supaya kolom yang berjauhan tidak menyatu.
     *
     * @param array<int, array{text: string, left: int, top: int, width: int, height: int, conf: float}> $words
     * @return string[]
     */
    private function linesFromWords(array $words): array
    {
        $min = (float) config('ocr.min_confidence', 30);

        $words = array_values(array_filter(
            $words,
            fn ($w) => $w['conf'] >= $min && preg_match('/[\pL\pN]/u', $w['text'])
        ));

        if (! $words) {
            return [];
        }

        usort($words, fn ($a, $b) => $a['top'] <=> $b['top']);

        $rows = [];

        foreach ($words as $word) {
            $center = $word['top'] + $word['height'] / 2;
            $best = null;
            $bestDistance = INF;

            foreach ($rows as $index => $row) {
                $distance = abs($center - $row['center']);

                if ($distance <= max($row['height'], $word['height']) * 0.6 && $distance < $bestDistance) {
                    $best = $index;
                    $bestDistance = $distance;
                }
            }

            if ($best === null) {
                $rows[] = ['center' => $center, 'height' => (float) $word['height'], 'words' => [$word]];

                continue;
            }

            $count = count($rows[$best]['words']);
            $rows[$best]['center'] = ($rows[$best]['center'] * $count + $center) / ($count + 1);
            $rows[$best]['height'] = ($rows[$best]['height'] * $count + $word['height']) / ($count + 1);
            $rows[$best]['words'][] = $word;
        }

        usort($rows, fn ($a, $b) => $a['center'] <=> $b['center']);

        $lines = [];

        foreach ($rows as $row) {
            $rowWords = $row['words'];
            usort($rowWords, fn ($a, $b) => $a['left'] <=> $b['left']);

            $line = '';
            $previousRight = null;

            foreach ($rowWords as $word) {
                if ($previousRight !== null) {
                    $line .= ($word['left'] - $previousRight) > 3 * $row['height'] ? ' | ' : ' ';
                }

                $line .= $word['text'];
                $previousRight = $word['left'] + $word['width'];
            }

            $lines[] = $line;
        }

        return $lines;
    }

    /**
     * @param string[] $primary
     * @param string[] $secondary
     * @return string[]
     */
    private function mergeLines(array $primary, array $secondary): array
    {
        $seen = [];

        foreach ($primary as $line) {
            $seen[$this->normalizeLine($line)] = true;
        }

        foreach ($secondary as $line) {
            $key = $this->normalizeLine($line);

            if ($key !== '' && ! isset($seen[$key])) {
                $primary[] = $line;
                $seen[$key] = true;
            }
        }

        return $primary;
    }

    private function normalizeLine(string $line): string
    {
        return preg_replace('/[^\pL\pN]/u', '', mb_strtolower($line));
    }

    private function lowConfidenceRatio(array $words): float
    {
        if (count($words) < 8) {
            return 0.0;
        }

        $low = count(array_filter($words, fn ($w) => $w['conf'] < 50));

        return $low / count($words);
    }

    /** Kata kunci (cocok seluruh kata, tanpa tanda baca di tepi). */
    private function keywordHits(array $words, string $regex): array
    {
        return array_values(array_filter($words, function ($w) use ($regex) {
            $text = trim($w['text'], " \t.:;,-|");

            return $w['conf'] >= 20 && preg_match($regex, $text);
        }));
    }

    /**
     * Baca stempel dengan mencoba beberapa sudut (stempel biasanya miring) dan margin
     * pemotongan bingkai. Berhenti pada bacaan pertama yang berarti LUNAS / BELUM LUNAS.
     */
    private function readStampStatus(string $stampPng): ?string
    {
        foreach ([0.16, 0.12, 0.20] as $margin) {
            foreach ([-6, -8, -4, 0, 6, 8] as $angle) {
                $candidate = $this->preprocessor->stampCandidate($stampPng, $angle, $margin);

                if (! $candidate) {
                    continue;
                }

                try {
                    $text = $this->tesseract->text($candidate, 7, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'eng');
                } finally {
                    $this->preprocessor->cleanup([$candidate]);
                }

                $status = $this->guessPaymentStatus($text, true);

                if ($status !== null) {
                    return $status;
                }
            }
        }

        return null;
    }

    private function readTotalFromZone(array $variants, array $words): ?float
    {
        $hits = $this->keywordHits($words, '/^(?:jumlah|total|tagihan|grand)$/iu');
        usort($hits, fn ($a, $b) => $b['top'] <=> $a['top']); // yang paling bawah dulu: biasanya total akhir

        foreach (array_slice($hits, 0, 3) as $hit) {
            foreach (array_unique([$variants['red'], $variants['gray']]) as $image) {
                $raw = $this->readZoneRightOf($image, $hit, '0123456789.,');
                $amounts = $raw ? $this->extractAmounts($raw) : [];

                if ($amounts) {
                    // Untuk zona di kanan label, nilai terakhir lebih sering merupakan nominal utuh.
                    return (float) end($amounts);
                }
            }
        }

        return null;
    }

    private function readNumberFromZone(array $variants, array $words): ?string
    {
        $hits = $this->keywordHits($words, '/^(?:no|nomor|invoice|inv|faktur|nota)$/iu');
        usort($hits, fn ($a, $b) => $a['top'] <=> $b['top']);

        // Maksimal 2 percobaan (jalur merah saja): nomor biasanya dicetak gelap, dan banyak
        // nota memang tidak punya nomor sehingga percobaan lebih banyak hanya membuang waktu.
        foreach (array_slice($hits, 0, 2) as $hit) {
            $raw = $this->readZoneRightOf($variants['red'], $hit, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789/-.');

            foreach (preg_split('/\R/', (string) $raw) as $line) {
                $candidate = $this->cleanNumber($line);

                if ($candidate !== null) {
                    return $candidate;
                }
            }
        }

        return null;
    }

    private function cleanNumber(string $raw): ?string
    {
        $raw = preg_replace('/\s+/', '', $raw);
        $raw = preg_replace('/^(?:NOMOR|NOTA|NO)[.\-]*/i', '', $raw);
        $raw = trim($raw, '.-/');

        $digits = preg_match_all('/\d/', $raw);

        // Sampah dari area non-angka (huruf dipaksa whitelist) dibuang: minimal separuh karakter harus angka.
        if ($digits < 2 || strlen($raw) > 30 || $digits < strlen($raw) / 2) {
            return null;
        }

        return $raw;
    }

    /**
     * Potong area di kanan sebuah kata (satu baris ke atas dan bawah), perbesar,
     * lalu OCR dengan whitelist karakter.
     */
    private function readZoneRightOf(string $image, array $word, string $whitelist): ?string
    {
        $size = @getimagesize($image);

        if (! $size) {
            return null;
        }

        $x = $word['left'] + $word['width'];
        // Tulisan tangan sering naik/turun dari garis cetak, jadi zonanya lebih tinggi dari kata kuncinya.
        $y = max(0, $word['top'] - (int) round($word['height'] * 1.8));
        $crop = $this->preprocessor->crop($image, $x, $y, $size[0] - $x, (int) round($word['height'] * 5));

        if (! $crop) {
            return null;
        }

        try {
            return $this->tesseract->text($crop, 6, $whitelist, 'eng');
        } finally {
            $this->preprocessor->cleanup([$crop]);
        }
    }

    /**
     * Tebak metadata dari teks OCR. Hasilnya hanya saran awal:
     * pengguna tetap memeriksa dan mengoreksinya di halaman verifikasi.
     *
     * @return array{entity_name: ?string, document_number: ?string, document_date: ?string, total_amount: ?float, payment_status: ?string}
     */
    public function parse(string $text): array
    {
        $lines = collect(preg_split('/\R/u', $text))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values();

        return [
            'entity_name' => $this->guessEntity($lines),
            'document_number' => $this->guessNumber($text),
            'document_date' => $this->guessDate($text),
            'total_amount' => $this->guessTotal($lines),
            'payment_status' => $this->guessPaymentStatus($text),
        ];
    }

    /** Cap "LUNAS" / "BELUM LUNAS" pada dokumen. Null kalau tidak ada kata kuncinya. */
    private function guessPaymentStatus(string $text, bool $fuzzy = false): ?string
    {
        if (preg_match('/belum\s*lunas|belum\s*dibayar|unpaid/iu', $text)) {
            return 'belum_lunas';
        }

        if (preg_match('/\b(?:lunas|paid)\b/iu', $text)) {
            return 'lunas';
        }

        // Toleransi salah baca hanya untuk teks stempel (bukan teks umum, supaya nama seperti "Lukas" aman).
        if ($fuzzy) {
            foreach (preg_split('/[^\pL\pN]+/u', mb_strtolower($text), -1, PREG_SPLIT_NO_EMPTY) as $word) {
                $length = strlen($word);

                if ($length >= 5 && $length <= 6 && levenshtein($word, 'lunas') <= ($length === 5 ? 1 : 2)) {
                    return 'lunas';
                }
            }
        }

        return null;
    }

    private function guessEntity($lines): ?string
    {
        // 1) Baris dengan awalan badan usaha: UD., PT., CV., TB., Toko, Koperasi.
        foreach ($lines->take(10) as $line) {
            if (preg_match('/\b(?:UD|PT|CV|PD|TB)\b\.?\s*\pL|(?i:\b(?:toko|koperasi)\s+\pL)/u', $line)) {
                $clean = $this->cleanEntity($line);

                if ($clean !== null) {
                    return $clean;
                }
            }
        }

        // 2) Cadangan: baris awal yang tampak seperti nama (bukan judul, tanggal, atau angka).
        foreach ($lines->take(6) as $line) {
            $letters = preg_match_all('/\pL/u', $line);
            $digits = preg_match_all('/\d/', $line);

            if ($letters < 3
                || $digits > $letters
                || preg_match('/^(nota|faktur|invoice|struk|kwitansi|tanggal|tgl|no\b|nomor|kasir)/iu', $line)
                || preg_match('/\d{1,2}\s+[a-z]{3,9}\.?\s+\d{4}/iu', $line)) {
                continue;
            }

            $clean = $this->cleanEntity($line);

            if ($clean !== null) {
                return $clean;
            }
        }

        return null;
    }

    private function cleanEntity(string $line): ?string
    {
        // Ambil kolom paling kiri saja (kolom lain dipisah " | "), lalu buang "Tgl. ..." yang ikut terbaca.
        $line = explode(' | ', $line)[0];
        $line = preg_replace('/\s+(?:tgl|tanggal)\b.*$/iu', '', $line);
        $clean = trim(preg_replace('/^[^\pL\pN]+|[^\pL\pN.)]+$/u', '', $line));

        return $clean !== '' ? mb_substr($clean, 0, 255) : null;
    }

    private function guessNumber(string $text): ?string
    {
        $pattern = '/\b(?:nomor|no\b\.?|invoice|inv\b\.?|faktur)(?:\s+(?:faktur|invoice|nota|surat))?[\s|]*[:.#\-]?[\s|]*([A-Z0-9][A-Z0-9\/\-\.]{2,})/iu';

        // "No. 77" pada alamat (Blok/Jl./Telp) bukan nomor dokumen.
        $text = implode("\n", array_filter(
            preg_split('/\R/u', $text),
            fn ($line) => ! preg_match('/\b(?:blok|jl|jalan|lt|los|telp|tlp|rt|rw)\b/iu', $line)
        ));

        if (preg_match_all($pattern, $text, $matches)) {
            foreach ($matches[1] as $candidate) {
                $candidate = rtrim($candidate, '.-/');

                if (strlen($candidate) >= 3 && preg_match('/\d/', $candidate)) {
                    return mb_substr($candidate, 0, 100);
                }
            }
        }

        return null;
    }

    private function guessDate(string $text): ?string
    {
        $months = [
            'jan' => 1, 'feb' => 2, 'mar' => 3, 'apr' => 4, 'mei' => 5, 'jun' => 6,
            'jul' => 7, 'agu' => 8, 'agt' => 8, 'aug' => 8, 'sep' => 9, 'okt' => 10,
            'oct' => 10, 'nov' => 11, 'des' => 12, 'dec' => 12, 'may' => 5,
        ];

        // 12 Januari 2026 / 12 Jan 2026
        if (preg_match('/\b(\d{1,2})\s+([a-z]{3,9})\.?\s+(\d{4})\b/iu', $text, $m)) {
            $month = $months[mb_strtolower(mb_substr($m[2], 0, 3))] ?? null;

            if ($month && checkdate($month, (int) $m[1], (int) $m[3])) {
                return sprintf('%04d-%02d-%02d', $m[3], $month, $m[1]);
            }
        }

        // 2026-01-12
        if (preg_match('/\b(\d{4})-(\d{2})-(\d{2})\b/', $text, $m) && checkdate((int) $m[2], (int) $m[3], (int) $m[1])) {
            return "{$m[1]}-{$m[2]}-{$m[3]}";
        }

        // 12/01/2026, 12-01-26, 12.01.2026 (urutan hari-bulan-tahun)
        if (preg_match_all('/\b(\d{1,2})[\/\-.](\d{1,2})[\/\-.](\d{2,4})\b/', $text, $all, PREG_SET_ORDER)) {
            foreach ($all as $m) {
                $year = (int) $m[3] < 100 ? 2000 + (int) $m[3] : (int) $m[3];

                if (checkdate((int) $m[2], (int) $m[1], $year)) {
                    return Carbon::create($year, (int) $m[2], (int) $m[1])->format('Y-m-d');
                }
            }
        }

        return null;
    }

    private function guessTotal($lines): ?float
    {
        // Tingkat 1: "total / jumlah". Tingkat 2: "bayar / tagihan" (dipakai hanya jika tingkat 1 kosong,
        // karena uang yang dibayar bisa lebih besar dari total).
        $tiers = [
            '/grand\s*total|total|jumlah/iu',
            '/tagihan|bayar|amount/iu',
        ];

        foreach ($tiers as $keywords) {
            $candidates = [];

            foreach ($lines as $i => $line) {
                if (! preg_match($keywords, $line) || preg_match('/qty|item|\bkembali(?:an)?\b|banyak|sub\s*total/iu', $line)) {
                    continue;
                }

                $amounts = $this->extractAmounts($line);

                // Angka kadang jatuh di baris berikutnya; hanya dipakai kalau baris itu murni angka.
                if (! $amounts && isset($lines[$i + 1]) && ! preg_match('/\pL{3,}/u', $lines[$i + 1])) {
                    $amounts = $this->extractAmounts($lines[$i + 1]);
                }

                array_push($candidates, ...$amounts);
            }

            if ($candidates) {
                // Utamakan kandidat terakhir sesuai urutan baca, bukan otomatis nominal terbesar.
                // Nominal terbesar dapat berupa uang tunai atau kembalian, bukan total dokumen.
                return (float) end($candidates);
            }
        }

        // Cadangan: angka terbesar berformat ribuan (mis. 530.000) di seluruh teks.
        $all = [];

        foreach ($lines as $line) {
            preg_match_all('/\d{1,3}(?:[.,]\d{3})+/', $line, $m);

            foreach ($m[0] as $raw) {
                $value = $this->parseAmount($raw);

                if ($value >= 100 && $value < 10000000000) {
                    $all[] = $value;
                }
            }
        }

        return $all ? max($all) : null;
    }

    /** @return float[] */
    private function extractAmounts(string $line): array
    {
        preg_match_all('/\d{1,3}(?:[.,]\d{3})+(?:[.,]\d{1,2})?|\d+(?:[.,]\d{1,2})?/', $line, $m);

        return collect($m[0])
            ->map(fn ($raw) => $this->parseAmount($raw))
            ->filter(fn ($value) => $value >= 100)
            ->values()
            ->all();
    }

    private function parseAmount(string $raw): float
    {
        // 1.250.000 / 1,250,000 / 1.250.000,50 / 12.50
        if (preg_match('/[.,](\d{1,2})$/', $raw, $decimal)) {
            $integer = preg_replace('/[.,]/', '', substr($raw, 0, -strlen($decimal[0])));

            return (float) ($integer . '.' . $decimal[1]);
        }

        return (float) preg_replace('/[.,]/', '', $raw);
    }
}

<?php

namespace App\Services;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/** Pembungkus tipis untuk perintah `tesseract`. Tidak tahu apa pun soal nota. */
class TesseractClient
{
    /**
     * Baca gambar dan kembalikan tiap kata beserta posisinya.
     *
     * @return array<int, array{text: string, left: int, top: int, width: int, height: int, conf: float}>
     */
    public function words(string $imagePath, ?int $psm = null): array
    {
        $tsv = $this->run($imagePath, $psm ?? (int) config('ocr.psm'), null, [], ['tsv']);

        $words = [];

        foreach (preg_split('/\R/', $tsv) as $line) {
            $cols = explode("\t", $line, 12);

            // Baris level 5 = kata. Header dan baris level lain dilewati.
            if (count($cols) < 12 || (int) $cols[0] !== 5) {
                continue;
            }

            $text = trim(mb_convert_encoding($cols[11], 'UTF-8', 'UTF-8'));

            if ($text === '') {
                continue;
            }

            $words[] = [
                'text' => $text,
                'left' => (int) $cols[6],
                'top' => (int) $cols[7],
                'width' => (int) $cols[8],
                'height' => (int) $cols[9],
                'conf' => (float) $cols[10],
            ];
        }

        return $words;
    }

    /**
     * Baca gambar sebagai teks biasa. $whitelist membatasi karakter yang boleh keluar
     * (mis. '0123456789.,' untuk area angka).
     */
    public function text(string $imagePath, int $psm, ?string $whitelist = null, ?string $language = null): string
    {
        $options = $whitelist !== null ? ['-c', 'tessedit_char_whitelist=' . $whitelist] : [];

        return mb_convert_encoding(trim($this->run($imagePath, $psm, $language, $options)), 'UTF-8', 'UTF-8');
    }

    private function run(string $imagePath, int $psm, ?string $language, array $options = [], array $configs = []): string
    {
        $language ??= config('ocr.language');

        try {
            return $this->exec($imagePath, $psm, $language, $options, $configs);
        } catch (ProcessFailedException $e) {
            if ($language === 'eng') {
                throw $e;
            }

            // Paket bahasa (mis. 'ind') mungkin belum terpasang: coba bahasa Inggris saja.
            return $this->exec($imagePath, $psm, 'eng', $options, $configs);
        }
    }

    private function exec(string $imagePath, int $psm, string $language, array $options, array $configs): string
    {
        $process = new Process([
            config('ocr.tesseract_path'),
            $imagePath,
            'stdout',
            '-l', $language,
            '--psm', (string) $psm,
            ...$options,
            ...$configs,
        ]);
        $process->setTimeout(config('ocr.timeout'));
        $process->mustRun();

        return $process->getOutput();
    }
}

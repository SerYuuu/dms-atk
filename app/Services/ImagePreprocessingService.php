<?php

namespace App\Services;

use Throwable;

/**
 * Praproses gambar dengan PHP GD (tanpa OpenCV), dua jalur:
 *  - gray : abu-abu + kontras. Untuk teks cetak (termasuk cetakan berwarna merah).
 *  - red  : hanya channel merah. Kertas pink/merah dan stempel merah menjadi putih,
 *           tinta biru/hitam tetap gelap. Untuk area tabel dan angka.
 *  - stamp: kebalikannya, hanya tinta merah pekat (stempel) yang tersisa. Dipotong pas pada
 *           stempel; null kalau tidak ada tinta merah yang berbentuk stempel.
 * Jalur gray dan red berukuran sama, jadi koordinat kata dari satu jalur berlaku di jalur lain.
 */
class ImagePreprocessingService
{
    private const MAX_PIXELS = 25000000;
    private const MIN_WIDTH = 1200;
    private const MAX_WIDTH = 1800;

    /**
     * @return array{gray: string, red: string, stamp: ?string, temp: string[]}|null  null kalau gagal / GD tidak aktif
     */
    public function variants(string $path): ?array
    {
        if (! function_exists('imagecreatetruecolor')) {
            return null;
        }

        try {
            $info = @getimagesize($path);

            if (! $info || ($info[0] * $info[1]) > self::MAX_PIXELS) {
                return null;
            }

            // Jangan menaikkan memory_limit secara paksa; biarkan batas server mengendalikan RAM.

            $src = $this->load($path, $info[2]);

            if (! $src) {
                return null;
            }

            $src = $this->fixOrientation($src, $path, $info[2]);
            $src = $this->normalizeWidth($src);

            $width = imagesx($src);
            $height = imagesy($src);

            // Satu putaran piksel untuk dua jalur:
            //  - red  : nilai R dijadikan abu-abu.
            //  - stamp: "kemerahan" (R dikurangi G/B terbesar) dibalik, sehingga hanya merah pekat yang gelap.
            $red = imagecreatetruecolor($width, $height);
            $stamp = imagecreatetruecolor($width, $height);
            imagefill($stamp, 0, 0, imagecolorallocate($stamp, 255, 255, 255));

            $ink = 0;
            $minX = $width;
            $minY = $height;
            $maxX = -1;
            $maxY = -1;

            for ($y = 0; $y < $height; $y++) {
                for ($x = 0; $x < $width; $x++) {
                    $rgb = imagecolorat($src, $x, $y);
                    $r = ($rgb >> 16) & 0xFF;
                    $g = ($rgb >> 8) & 0xFF;
                    $b = $rgb & 0xFF;

                    imagesetpixel($red, $x, $y, ($r << 16) | ($r << 8) | $r);

                    $redness = $r - max($g, $b);

                    if ($redness > 20) {
                        $v = max(0, 255 - (int) round($redness * 1.6));
                        imagesetpixel($stamp, $x, $y, ($v << 16) | ($v << 8) | $v);

                        if ($v < 128) {
                            $ink++;
                            $minX = min($minX, $x);
                            $minY = min($minY, $y);
                            $maxX = max($maxX, $x);
                            $maxY = max($maxY, $y);
                        }
                    }
                }
            }
            imagefilter($red, IMG_FILTER_CONTRAST, -25);

            $stampPath = $this->saveStampCrop($stamp, $width, $height, $ink, $minX, $minY, $maxX, $maxY);
            imagedestroy($stamp);

            // Jalur abu-abu biasa.
            imagefilter($src, IMG_FILTER_GRAYSCALE);
            imagefilter($src, IMG_FILTER_CONTRAST, -25);

            $grayPath = $this->tempPath();
            $redPath = $this->tempPath();
            imagepng($src, $grayPath);
            imagepng($red, $redPath);
            imagedestroy($src);
            imagedestroy($red);

            return [
                'gray' => $grayPath,
                'red' => $redPath,
                'stamp' => $stampPath,
                'temp' => array_values(array_filter([$grayPath, $redPath, $stampPath])),
            ];
        } catch (Throwable $e) {
            report($e);

            return null;
        }
    }

    /**
     * Potong area dari file PNG hasil variants(), perbesar, beri bingkai putih.
     * Mengembalikan path PNG sementara (hapus dengan cleanup()), atau null kalau area terlalu kecil.
     */
    public function crop(string $pngPath, int $x, int $y, int $width, int $height, int $scale = 3): ?string
    {
        if (! function_exists('imagecreatefrompng')) {
            return null;
        }

        try {
            $image = @imagecreatefrompng($pngPath);

            if (! $image) {
                return null;
            }

            $x = max(0, $x);
            $y = max(0, $y);
            $width = min($width, imagesx($image) - $x);
            $height = min($height, imagesy($image) - $y);

            if ($x >= imagesx($image) || $y >= imagesy($image) || $width < 20 || $height < 10) {
                imagedestroy($image);

                return null;
            }

            $cropped = imagecrop($image, ['x' => $x, 'y' => $y, 'width' => $width, 'height' => $height]);
            imagedestroy($image);

            if (! $cropped) {
                return null;
            }

            $scaled = imagescale($cropped, $width * $scale, -1, IMG_BICUBIC);
            imagedestroy($cropped);

            if (! $scaled) {
                return null;
            }

            $pad = 24;
            $sw = imagesx($scaled);
            $sh = imagesy($scaled);
            $canvas = imagecreatetruecolor($sw + 2 * $pad, $sh + 2 * $pad);
            imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
            imagecopy($canvas, $scaled, $pad, $pad, 0, 0, $sw, $sh);
            imagedestroy($scaled);

            $output = $this->tempPath();
            imagepng($canvas, $output);
            imagedestroy($canvas);

            return is_file($output) ? $output : null;
        } catch (Throwable $e) {
            report($e);

            return null;
        }
    }

    /**
     * Satu kandidat bacaan stempel: putar (meluruskan stempel yang miring), buang bingkai
     * dengan memotong bagian tepi, lalu perbesar 2x. Dicoba beberapa sudut/margin oleh pemanggil.
     */
    public function stampCandidate(string $stampPng, int $angle, float $margin): ?string
    {
        try {
            $image = @imagecreatefrompng($stampPng);

            if (! $image) {
                return null;
            }

            if ($angle !== 0) {
                $rotated = imagerotate($image, $angle, imagecolorallocate($image, 255, 255, 255));
                imagedestroy($image);
                $image = $rotated;
            }

            if (! $image) {
                return null;
            }

            $rw = imagesx($image);
            $rh = imagesy($image);
            $x = (int) round($rw * $margin * 0.8);
            $y = (int) round($rh * $margin * 1.3);
            $w = (int) round($rw * (1 - 1.6 * $margin));
            $h = (int) round($rh * (1 - 2.6 * $margin));

            if ($w < 30 || $h < 20) {
                imagedestroy($image);

                return null;
            }

            $inner = imagecrop($image, ['x' => $x, 'y' => $y, 'width' => $w, 'height' => $h]);
            imagedestroy($image);

            if (! $inner) {
                return null;
            }

            $scaled = imagescale($inner, $w * 2, -1, IMG_BICUBIC);
            imagedestroy($inner);

            if (! $scaled) {
                return null;
            }

            $output = $this->tempPath();
            imagepng($scaled, $output);
            imagedestroy($scaled);

            return is_file($output) ? $output : null;
        } catch (Throwable $e) {
            report($e);

            return null;
        }
    }

    /** Simpan potongan stempel kalau tinta merahnya kompak dan berbentuk stempel (persegi panjang). */
    private function saveStampCrop($stamp, int $width, int $height, int $ink, int $minX, int $minY, int $maxX, int $maxY): ?string
    {
        $area = $width * $height;

        if ($maxX < 0 || $ink < $area * 0.002 || $ink > $area * 0.15) {
            return null;
        }

        $boxW = $maxX - $minX + 1;
        $boxH = $maxY - $minY + 1;
        $ratio = $boxW / max(1, $boxH);

        // Tinta merah yang tersebar ke seluruh halaman (logo, teks cetak merah) bukan stempel.
        if ($boxW * $boxH > $area * 0.35 || $ratio < 1.2 || $ratio > 10) {
            return null;
        }

        $pad = 20;
        $x = max(0, $minX - $pad);
        $y = max(0, $minY - $pad);
        $cropped = imagecrop($stamp, [
            'x' => $x,
            'y' => $y,
            'width' => min($width - $x, $boxW + 2 * $pad),
            'height' => min($height - $y, $boxH + 2 * $pad),
        ]);

        if (! $cropped) {
            return null;
        }

        $path = $this->tempPath();
        imagepng($cropped, $path);
        imagedestroy($cropped);

        return is_file($path) ? $path : null;
    }

    /** @param string[] $paths */
    public function cleanup(array $paths): void
    {
        foreach ($paths as $path) {
            if (is_file($path)) {
                @unlink($path);
            }
        }
    }

    private function load(string $path, int $type)
    {
        $image = match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_PNG => @imagecreatefrompng($path),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            default => false,
        };

        if (! $image) {
            return false;
        }

        // Latar transparan (PNG/WEBP) dijadikan putih, kalau tidak jadi hitam.
        $width = imagesx($image);
        $height = imagesy($image);
        $canvas = imagecreatetruecolor($width, $height);
        imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
        imagecopy($canvas, $image, 0, 0, 0, 0, $width, $height);
        imagedestroy($image);

        return $canvas;
    }

    private function normalizeWidth($image)
    {
        $width = imagesx($image);

        if ($width >= self::MIN_WIDTH && $width <= self::MAX_WIDTH) {
            return $image;
        }

        $target = $width < self::MIN_WIDTH
            ? (int) round($width * min(3, self::MIN_WIDTH / $width))
            : self::MAX_WIDTH;

        $scaled = imagescale($image, $target, -1, IMG_BICUBIC);

        if ($scaled) {
            imagedestroy($image);

            return $scaled;
        }

        return $image;
    }

    private function fixOrientation($image, string $path, int $type)
    {
        if ($type !== IMAGETYPE_JPEG || ! function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($path);
        $angle = match ($exif['Orientation'] ?? 1) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => 0,
        };

        if ($angle === 0) {
            return $image;
        }

        // Area kosong setelah rotasi harus putih, bukan hitam.
        $rotated = imagerotate($image, $angle, imagecolorallocate($image, 255, 255, 255));

        if ($rotated) {
            imagedestroy($image);

            return $rotated;
        }

        return $image;
    }

    private function tempPath(): string
    {
        // tempnam() membuat nama unik secara atomik, lebih aman daripada uniqid().
        $base = tempnam(sys_get_temp_dir(), 'ocr_');

        if ($base === false) {
            throw new \RuntimeException('Tidak dapat membuat file sementara OCR.');
        }

        $path = $base . '.png';

        if (! @rename($base, $path)) {
            @unlink($base);
            throw new \RuntimeException('Tidak dapat menyiapkan file sementara OCR.');
        }

        return $path;
    }
}

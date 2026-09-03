<?php

namespace App\Support;

/**
 * Kompresi gambar lampiran (JPEG/PNG) di tempat.
 *
 * Di-extract dari Sekretaris\AgendaController::compressImage (desain §7.6)
 * supaya bisa dipakai guru + wakasek (TeacherStatusService) dan controller
 * lain tanpa duplikasi.
 */
class ImageCompressor
{
    /**
     * Kompres gambar yang tersimpan di disk public.
     *
     * @param  string  $path  path relatif terhadap disk 'public'
     *                        (mis. "agendas/xxx.jpg")
     */
    public static function compress(string $path): void
    {
        // Jika ekstensi GD tidak tersedia (mis. deploy VPS tanpa php-gd),
        // lewati kompresi — upload tetap berhasil tanpa fatal error.
        if (! function_exists('imagecreatefromjpeg') || ! function_exists('imagecreatefrompng')) {
            return;
        }

        $fullPath = storage_path('app/public/'.$path);
        if (! file_exists($fullPath)) {
            return;
        }

        $info = @getimagesize($fullPath);
        if (! $info) {
            return;
        }

        $mime = $info['mime'];

        if ($mime === 'image/jpeg') {
            $img = @imagecreatefromjpeg($fullPath);
            if ($img) {
                imagejpeg($img, $fullPath, 60);
                imagedestroy($img);
            }
        } elseif ($mime === 'image/png') {
            $img = @imagecreatefrompng($fullPath);
            if ($img) {
                imagepng($img, $fullPath, 6);
                imagedestroy($img);
            }
        }
    }
}

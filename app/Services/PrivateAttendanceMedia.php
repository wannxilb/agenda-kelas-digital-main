<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

class PrivateAttendanceMedia
{
    public function store(string $contents, string $directory, string $extension = 'bin'): string
    {
        $path = trim($directory, '/').'/'.bin2hex(random_bytes(16)).'.'.$extension;
        Storage::disk('local')->put($path, Crypt::encryptString(base64_encode($contents)));

        return $path;
    }

    public function read(string $path): string
    {
        $encrypted = Storage::disk('local')->get($path);
        $contents = base64_decode(Crypt::decryptString($encrypted), true);

        abort_if($contents === false, 404, 'Media tidak valid.');

        return $contents;
    }

    public function importPublic(string $publicPath, string $privateDirectory, string $extension = 'bin'): ?string
    {
        if (! Storage::disk('public')->exists($publicPath)) {
            return null;
        }

        $privatePath = $this->store(Storage::disk('public')->get($publicPath), $privateDirectory, $extension);
        Storage::disk('public')->delete($publicPath);

        return $privatePath;
    }

    public function delete(?string $path): void
    {
        if ($path) {
            Storage::disk('local')->delete($path);
        }
    }
}

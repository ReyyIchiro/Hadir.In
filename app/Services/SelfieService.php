<?php

namespace App\Services;

use App\Models\AttendanceEvent;
use App\Models\Tenant;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SelfieService
{
    public function store(Tenant $tenant, UploadedFile $file): string
    {
        if (! function_exists('imagecreatefromstring')) {
            throw ValidationException::withMessages([
                'selfie' => 'Pemrosesan gambar tidak tersedia di server ini.',
            ]);
        }

        $contents = file_get_contents($file->getRealPath());

        if ($contents === false || $contents === '') {
            throw ValidationException::withMessages(['selfie' => 'Berkas selfie tidak dapat dibaca.']);
        }

        $info = @getimagesizefromstring($contents);

        if ($info === false || ! in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
            throw ValidationException::withMessages(['selfie' => 'Berkas selfie bukan gambar yang sah.']);
        }

        $source = @imagecreatefromstring($contents);

        if ($source === false) {
            throw ValidationException::withMessages(['selfie' => 'Berkas selfie bukan gambar yang sah.']);
        }

        $width = imagesx($source);
        $height = imagesy($source);
        $max = 1280;

        if ($width > $max || $height > $max) {
            $ratio = min($max / $width, $max / $height);
            $newWidth = max(1, (int) floor($width * $ratio));
            $newHeight = max(1, (int) floor($height * $ratio));

            $resized = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($resized, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagedestroy($source);
            $source = $resized;
        }

        // A new truecolor canvas drops EXIF and any embedded metadata.
        $clean = imagecreatetruecolor(imagesx($source), imagesy($source));
        imagecopy($clean, $source, 0, 0, 0, 0, imagesx($source), imagesy($source));
        imagedestroy($source);

        ob_start();
        imagejpeg($clean, null, 80);
        $jpeg = ob_get_clean();
        imagedestroy($clean);

        if ($jpeg === false || $jpeg === '') {
            throw ValidationException::withMessages(['selfie' => 'Selfie gagal diproses. Coba lagi.']);
        }

        $path = sprintf(
            'selfies/%d/%s/%s/%s.jpg',
            $tenant->id,
            now()->format('Y'),
            now()->format('m'),
            (string) Str::uuid(),
        );

        Storage::disk('local')->put($path, $jpeg);

        return $path;
    }

    public function delete(?string $path): void
    {
        if ($path) {
            Storage::disk('local')->delete($path);
        }
    }

    /**
     * Signed URL for an event selfie, or null when the event has no file
     * (never captured, or already purged by the retention job).
     */
    public function temporaryUrl(?int $eventId, int $minutes = 5): ?string
    {
        if (! $eventId) {
            return null;
        }

        $event = AttendanceEvent::find($eventId);

        if (! $event?->selfie_path || ! Storage::disk('local')->exists($event->selfie_path)) {
            return null;
        }

        return url()->temporarySignedRoute(
            'selfie.show',
            now()->addMinutes($minutes),
            ['event' => $eventId],
        );
    }
}

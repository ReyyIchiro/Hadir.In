<?php

use App\Services\SelfieService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

function pngUpload(int $width, int $height): UploadedFile
{
    $image = imagecreatetruecolor($width, $height);
    imagefill($image, 0, 0, imagecolorallocate($image, 90, 120, 160));
    ob_start();
    imagepng($image);
    $bytes = ob_get_clean();
    imagedestroy($image);

    return UploadedFile::fake()->createWithContent('selfie.png', $bytes);
}

it('memperkecil selfie besar sampai sisi terpanjang 1280 piksel', function () {
    Storage::fake('local');
    $tenant = makeTenant();

    $path = app(SelfieService::class)->store($tenant, pngUpload(3000, 2000));

    $stored = Storage::disk('local')->get($path);
    $info = getimagesizefromstring($stored);

    expect($info[0])->toBe(1280)
        ->and($info[1])->toBe(853)
        ->and($info[2])->toBe(IMAGETYPE_JPEG);
});

it('tidak memperbesar selfie yang sudah kecil', function () {
    Storage::fake('local');
    $tenant = makeTenant();

    $path = app(SelfieService::class)->store($tenant, pngUpload(320, 240));

    $info = getimagesizefromstring(Storage::disk('local')->get($path));

    expect($info[0])->toBe(320)
        ->and($info[1])->toBe(240);
});

it('membuang metadata EXIF dari berkas tersimpan', function () {
    Storage::fake('local');
    $tenant = makeTenant();

    // JPEG dengan segmen APP1 berisi teks EXIF buatan.
    $image = imagecreatetruecolor(200, 200);
    ob_start();
    imagejpeg($image, null, 90);
    $jpeg = ob_get_clean();
    imagedestroy($image);

    $marker = "Exif\0\0GPSLatitude";
    $app1 = "\xFF\xE1".pack('n', strlen($marker) + 2).$marker;
    $withExif = substr($jpeg, 0, 2).$app1.substr($jpeg, 2);

    expect($withExif)->toContain('GPSLatitude');

    $path = app(SelfieService::class)->store(
        $tenant,
        UploadedFile::fake()->createWithContent('selfie.jpg', $withExif),
    );

    expect(Storage::disk('local')->get($path))->not->toContain('GPSLatitude');
});

it('menolak berkas yang bukan gambar', function () {
    Storage::fake('local');
    $tenant = makeTenant();

    expect(fn () => app(SelfieService::class)->store(
        $tenant,
        UploadedFile::fake()->createWithContent('selfie.jpg', 'bukan gambar'),
    ))->toThrow(ValidationException::class);
});

it('menolak gambar di atas batas megapiksel sebelum dekode', function () {
    Storage::fake('local');
    $tenant = makeTenant();

    // JPEG 6000x5000 (30 MP) berdimensi sah tetapi melewati batas 24 MP.
    $canvas = imagecreatetruecolor(6000, 5000);
    ob_start();
    imagejpeg($canvas, null, 40);
    $bytes = ob_get_clean();
    imagedestroy($canvas);

    expect(fn () => app(SelfieService::class)->store(
        $tenant,
        UploadedFile::fake()->createWithContent('selfie.jpg', $bytes),
    ))->toThrow(ValidationException::class);
});

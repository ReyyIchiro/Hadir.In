<?php

use App\Enums\MembershipRole;
use App\Enums\TenantStatus;
use App\Models\Division;
use App\Models\Location;
use App\Models\Membership;
use App\Models\Schedule;
use App\Models\Tenant;
use App\Models\TenantSetting;
use App\Models\User;
use App\Support\TenantContext;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test case
|--------------------------------------------------------------------------
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature', 'Unit');

afterEach(function () {
    // Waktu beku dan konteks tenant tidak boleh bocor ke tes berikutnya.
    Carbon\Carbon::setTestNow();
    Carbon\CarbonImmutable::setTestNow();
    app(App\Support\TenantContext::class)->clear();
});

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function makeTenant(array $attributes = []): Tenant
{
    $tenant = Tenant::create(array_merge([
        'name' => 'Organisasi Uji',
        'slug' => 'organisasi-uji-'.Str::lower(Str::random(6)),
        'timezone' => 'Asia/Jakarta',
        'status' => TenantStatus::Active,
        'plan' => 'standard',
    ], $attributes));

    TenantSetting::create(array_merge([
        'tenant_id' => $tenant->id,
    ], $attributes['settings'] ?? []));

    return $tenant->refresh();
}

function makeUser(array $attributes = []): User
{
    return User::create(array_merge([
        'name' => 'Pengguna Uji',
        'email' => 'pengguna-'.Str::lower(Str::random(8)).'@hadir.test',
        'password' => 'password123',
    ], $attributes));
}

function makeMembership(Tenant $tenant, User $user, string $role = 'member', array $attributes = []): Membership
{
    return Membership::create(array_merge([
        'tenant_id' => $tenant->id,
        'user_id' => $user->id,
        'role' => $role,
        'is_active' => true,
        'joined_at' => now(),
    ], $attributes));
}

function makeLocation(Tenant $tenant, array $attributes = []): Location
{
    return Location::create(array_merge([
        'tenant_id' => $tenant->id,
        'name' => 'Kantor Uji',
        'address' => 'Jalan Uji 1',
        'latitude' => -6.2000000,
        'longitude' => 106.8166667,
        'radius_meters' => 50,
        'is_active' => true,
    ], $attributes));
}

function makeDivision(Tenant $tenant, array $attributes = []): Division
{
    return Division::create(array_merge([
        'tenant_id' => $tenant->id,
        'name' => 'Divisi Uji',
        'is_active' => true,
    ], $attributes));
}

function makeSchedule(Tenant $tenant, User $user, string $date, string $start = '08:00', string $end = '17:00'): Schedule
{
    return Schedule::create([
        'tenant_id' => $tenant->id,
        'user_id' => $user->id,
        'work_date' => $date,
        'is_day_off' => false,
        'start_time' => $start.':00',
        'end_time' => $end.':00',
    ]);
}

function actingAsMember(Tenant $tenant, array $attributes = []): User
{
    $user = makeUser($attributes);
    makeMembership($tenant, $user, MembershipRole::Member->value);

    test()->actingAs($user);
    setTenant($tenant);

    return $user;
}

function actingAsAdmin(Tenant $tenant, array $attributes = []): User
{
    $user = makeUser($attributes);
    makeMembership($tenant, $user, MembershipRole::Admin->value);

    test()->actingAs($user);
    setTenant($tenant);

    return $user;
}

function actingAsReviewer(Tenant $tenant, Location $location, array $attributes = []): User
{
    $user = makeUser($attributes);
    makeMembership($tenant, $user, MembershipRole::Reviewer->value, [
        'primary_location_id' => $location->id,
    ]);

    test()->actingAs($user);
    setTenant($tenant);

    return $user;
}

function setTenant(?Tenant $tenant): void
{
    $context = app(TenantContext::class);
    $context->clear();

    if ($tenant === null) {
        return;
    }

    // Utamakan keanggotaan milik pengguna yang sedang login. Tanpa ini,
    // setTenant() bisa memilih keanggotaan orang lain dan uji kebijakan
    // lolos atau gagal karena alasan yang salah.
    $membership = null;
    $currentId = auth()->id();

    if ($currentId !== null) {
        $membership = Membership::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('user_id', $currentId)
            ->where('is_active', true)
            ->first();
    }

    $membership ??= Membership::withoutGlobalScopes()
        ->where('tenant_id', $tenant->id)
        ->where('is_active', true)
        ->first();

    if ($membership === null) {
        $user = makeUser();
        $membership = makeMembership($tenant, $user, MembershipRole::Member->value);
    }

    $context->set($tenant, $membership);
}

function selfieFile(): UploadedFile
{
    $image = imagecreatetruecolor(40, 40);
    imagefill($image, 0, 0, imagecolorallocate($image, 200, 120, 90));
    ob_start();
    imagejpeg($image, null, 70);
    $bytes = ob_get_clean();
    imagedestroy($image);

    return UploadedFile::fake()->createWithContent('selfie.jpg', $bytes);
}

function freezeAt(string $utc): CarbonImmutable
{
    $moment = CarbonImmutable::parse($utc, 'UTC');
    CarbonImmutable::setTestNow($moment);
    Carbon\Carbon::setTestNow($moment);

    return $moment;
}

/**
 * Mengekstrak teks dari berkas PDF hasil dompdf tanpa bergantung pada
 * binari luar seperti pdftotext, supaya pengujian juga jalan di CI.
 * Membaca ToUnicode CMap tiap font subset lalu menerjemahkan string
 * heksadesimal pada content stream.
 */
function pdfText(string $content): string
{
    $objects = [];
    preg_match_all('/(\d+)\s+0\s+obj(.*?)endobj/s', $content, $matches, PREG_SET_ORDER);

    foreach ($matches as $match) {
        $body = $match[2];
        $stream = null;

        if (preg_match('/stream\r?\n(.*?)\r?\nendstream/s', $body, $streamMatch)) {
            $stream = $streamMatch[1];
            if (preg_match('/\/Filter\s*\/FlateDecode/', $body)) {
                $decoded = @gzuncompress($stream);
                // ToUnicode CMap kadang ditulis tanpa kompresi walau dict
                // menyebut FlateDecode; biarkan teks aslinya bila gagal.
                $stream = $decoded === false ? $stream : $decoded;
            }
        }

        $objects[(int) $match[1]] = ['body' => $body, 'stream' => $stream];
    }

    $cmaps = [];

    foreach ($objects as $id => $object) {
        if (! preg_match('/\/ToUnicode\s+(\d+)\s+0\s+R/', $object['body'], $ref)) {
            continue;
        }

        $cmapObject = $objects[(int) $ref[1]] ?? null;

        if ($cmapObject === null || $cmapObject['stream'] === null) {
            continue;
        }

        $map = [];

        preg_match_all('/beginbfchar(.*?)endbfchar/s', $cmapObject['stream'], $blocks);

        foreach ($blocks[1] as $block) {
            preg_match_all('/<([0-9A-Fa-f]+)>\s*<([0-9A-Fa-f]+)>/', $block, $pairs, PREG_SET_ORDER);
            foreach ($pairs as $pair) {
                $map[hexdec($pair[1])] = hexUtf16($pair[2]);
            }
        }

        preg_match_all('/beginbfrange(.*?)endbfrange/s', $cmapObject['stream'], $blocks);

        foreach ($blocks[1] as $block) {
            preg_match_all('/<([0-9A-Fa-f]+)>\s*<([0-9A-Fa-f]+)>\s*<([0-9A-Fa-f]+)>/', $block, $ranges, PREG_SET_ORDER);
            foreach ($ranges as $range) {
                $from = hexdec($range[1]);
                $to = hexdec($range[2]);
                $base = hexdec($range[3]);
                for ($glyph = $from; $glyph <= $to; $glyph++) {
                    $map[$glyph] = mb_chr($base + ($glyph - $from), 'UTF-8');
                }
            }
        }

        $cmaps[$id] = $map;
    }

    $fontRefs = [];

    foreach ($objects as $id => $object) {
        if (preg_match('/\/ToUnicode\s+(\d+)\s+0\s+R/', $object['body'], $ref)) {
            if (preg_match('/\/BaseFont\s*\/([A-Za-z0-9+\-]+)/', $object['body'], $base)) {
                $includesIdentity = str_contains($object['body'], '/Identity-H');

                if ($includesIdentity) {
                    $fontRefs[$base[1]] = ['identity' => true, 'map' => $cmaps[$id] ?? []];
                } else {
                    // Font Type1 (Helvetica footer FPDF) memakai satu byte per
                    // karakter, langsung WinAnsi.
                    $fontRefs[$base[1]] = ['identity' => false, 'map' => $cmaps[$id] ?? []];
                }
            }
        }
    }

    $pageRefs = [];

    // dompdf menaruh /Resources di node /Pages dan halaman mewarisinya, jadi
    // peta font diambil dari halaman atau dari induk /Pages.
    $inheritedFonts = [];

    foreach ($objects as $object) {
        if (preg_match('/\/Type\s*\/Pages/', $object['body']) && preg_match('/\/Font\s*<<(.*?)>>/s', $object['body'], $fontBlock)) {
            preg_match_all('/\/([A-Za-z0-9]+)\s+(\d+)\s+0\s+R/', $fontBlock[1], $refs, PREG_SET_ORDER);
            foreach ($refs as $ref) {
                $fontObject = $objects[(int) $ref[2]] ?? null;
                if ($fontObject !== null && preg_match('/\/BaseFont\s*\/([A-Za-z0-9+\-]+)/', $fontObject['body'], $base)) {
                    $inheritedFonts[$ref[1]] = $fontRefs[$base[1]] ?? [];
                }
            }
        }
    }

    foreach ($objects as $object) {
        if (! preg_match('/\/Type\s*\/Page[^s]/', $object['body'])) {
            continue;
        }

        $fonts = $inheritedFonts;

        // FPDI menaruh resource sebagai referensi objek, bukan inline.
        if (preg_match('/\/Resources\s+(\d+)\s+0\s+R/', $object['body'], $resRef)) {
            $resBody = $objects[(int) $resRef[1]]['body'] ?? '';
            if (preg_match('/\/Font\s*<<(.*?)>>/s', $resBody, $fontBlock)) {
                preg_match_all('/\/([A-Za-z0-9]+)\s+(\d+)\s+0\s+R/', $fontBlock[1], $refs, PREG_SET_ORDER);
                foreach ($refs as $ref) {
                    $fontObject = $objects[(int) $ref[2]] ?? null;
                    if ($fontObject !== null && preg_match('/\/BaseFont\s*\/([A-Za-z0-9+\-]+)/', $fontObject['body'], $base)) {
                        $fonts[$ref[1]] = $fontRefs[$base[1]] ?? [];
                    }
                }
            }
        }

        if (preg_match('/\/Font\s*<<(.*?)>>/s', $object['body'], $fontBlock)) {
            preg_match_all('/\/([A-Za-z0-9]+)\s+(\d+)\s+0\s+R/', $fontBlock[1], $refs, PREG_SET_ORDER);
            foreach ($refs as $ref) {
                $fontObject = $objects[(int) $ref[2]] ?? null;
                if ($fontObject !== null && preg_match('/\/BaseFont\s*\/([A-Za-z0-9+\-]+)/', $fontObject['body'], $base)) {
                    $fonts[$ref[1]] = $fontRefs[$base[1]] ?? [];
                }
            }
        }

        $contents = [];

        if (preg_match('/\/Contents\s+(\d+)\s+0\s+R/', $object['body'], $ref)) {
            $contents[] = $objects[(int) $ref[1]]['stream'] ?? '';
        } elseif (preg_match('/\/Contents\s*\[(.*?)\]/s', $object['body'], $ref)) {
            preg_match_all('/(\d+)\s+0\s+R/', $ref[1], $refs);
            foreach ($refs[1] as $id) {
                $contents[] = $objects[(int) $id]['stream'] ?? '';
            }
        }

        $xobjects = [];

        if (preg_match('/\/XObject\s*<<(.*?)>>/s', $object['body'], $xBlock)
            || (isset($resBody) && preg_match('/\/XObject\s*<<(.*?)>>/s', $resBody, $xBlock))) {
            preg_match_all('/\/([A-Za-z0-9]+)\s+(\d+)\s+0\s+R/', $xBlock[1], $refs, PREG_SET_ORDER);
            foreach ($refs as $ref) {
                $xobjects[$ref[1]] = (int) $ref[2];
            }
        }

        $pageRefs[] = ['fonts' => $fonts, 'content' => implode("\n", $contents), 'xobjects' => $xobjects, 'resources' => $resBody ?? ''];
    }

    $text = [];

    $decode = function (string $content, array $fonts) use (&$decode): string {
        $out = [];
        $map = [];

        preg_match_all(
            '/\/([A-Za-z0-9]+)\s+[\d.]+\s+Tf|\[([^\[\]]*)\]\s*TJ|<([0-9A-Fa-f\s]+)>\s*Tj|\(((?:\\\\.|[^\\\\()])*)\)\s*Tj|\/([A-Za-z0-9]+)\s+Do/s',
            $content,
            $tokens,
            PREG_SET_ORDER
        );

        foreach ($tokens as $token) {
            if (isset($token[1]) && $token[1] !== '') {
                $map = $fonts[$token[1]] ?? [];

                continue;
            }

            if (isset($token[2]) && $token[2] !== '') {
                preg_match_all('/<([0-9A-Fa-f\s]+)>|\(((?:\\\\.|[^\\\\()])*)\)/', $token[2], $parts, PREG_SET_ORDER);
                foreach ($parts as $part) {
                    if (isset($part[1]) && $part[1] !== '') {
                        $out[] = decodeHexString($part[1], $map);
                    } elseif (isset($part[2]) && $part[2] !== '') {
                        $out[] = decodeLiteralString($part[2], $map);
                    }
                }

                continue;
            }

            if (isset($token[3]) && $token[3] !== '') {
                $out[] = decodeHexString($token[3], $map);

                continue;
            }

            if (isset($token[4]) && $token[4] !== '') {
                $out[] = decodeLiteralString($token[4], $map);
            }
        }

        return implode('', $out);
    };

    // FPDI membungkus tiap halaman dompdf sebagai Form XObject (/TPL0 Do),
    // jadi teks di dalamnya dibaca dari objek rujukan, dengan peta fontnya
    // sendiri.
    $walk = function (string $content, array $fonts, array $xobjects) use (&$walk, $decode, $objects, $fontRefs): string {
        $out = $decode($content, $fonts);

        preg_match_all('/\/([A-Za-z0-9]+)\s+Do/', $content, $calls);

        foreach ($calls[1] as $name) {
            $id = $xobjects[$name] ?? null;
            $form = $id !== null ? ($objects[$id]['stream'] ?? null) : null;

            if ($form === null) {
                continue;
            }

            $formFonts = [];

            if (preg_match('/\/Font\s*<<(.*?)>>/s', $objects[$id]['body'] ?? '', $fontBlock)) {
                preg_match_all('/\/([A-Za-z0-9]+)\s+(\d+)\s+0\s+R/', $fontBlock[1], $refs, PREG_SET_ORDER);
                foreach ($refs as $ref) {
                    $fontObject = $objects[(int) $ref[2]] ?? null;
                    if ($fontObject !== null && preg_match('/\/BaseFont\s*\/([A-Za-z0-9+\-]+)/', $fontObject['body'], $base)) {
                        $formFonts[$ref[1]] = $fontRefs[$base[1]] ?? [];
                    }
                }
            }

            $formXObjects = [];

            if (preg_match('/\/XObject\s*<<(.*?)>>/s', $objects[$id]['body'] ?? '', $xBlock)) {
                preg_match_all('/\/([A-Za-z0-9]+)\s+(\d+)\s+0\s+R/', $xBlock[1], $refs, PREG_SET_ORDER);
                foreach ($refs as $ref) {
                    $formXObjects[$ref[1]] = (int) $ref[2];
                }
            }

            $out .= $walk($form, $formFonts, $formXObjects);
        }

        return $out;
    };

    foreach ($pageRefs as $page) {
        $text[] = $walk($page['content'], $page['fonts'], $page['xobjects']);
        $text[] = "\n";
    }

    return implode('', $text);
}

function decodeHexString(string $hex, array $font): string
{
    $hex = preg_replace('/\s+/', '', $hex);
    $out = '';

    if (! ($font['identity'] ?? true)) {
        for ($i = 0; $i + 2 <= strlen($hex); $i += 2) {
            $code = hexdec(substr($hex, $i, 2));
            $out .= $font['map'][$code] ?? chr($code);
        }

        return $out;
    }

    for ($i = 0; $i + 4 <= strlen($hex); $i += 4) {
        $glyph = hexdec(substr($hex, $i, 4));
        $out .= $font['map'][$glyph] ?? '';
    }

    return $out;
}

function decodeLiteralString(string $literal, array $font): string
{
    $bytes = stripcslashes($literal);
    $out = '';

    if (! ($font['identity'] ?? true)) {
        for ($i = 0; $i < strlen($bytes); $i++) {
            $code = ord($bytes[$i]);
            $out .= $font['map'][$code] ?? $bytes[$i];
        }

        return $out;
    }

    // Font Identity-H menyimpan glyph sebagai dua byte di dalam string literal.
    for ($i = 0; $i + 1 < strlen($bytes); $i += 2) {
        $glyph = (ord($bytes[$i]) << 8) | ord($bytes[$i + 1]);
        $out .= $font['map'][$glyph] ?? '';
    }

    return $out;
}

function hexUtf16(string $hex): string
{
    $hex = preg_replace('/\s+/', '', $hex);
    $out = '';

    for ($i = 0; $i + 4 <= strlen($hex); $i += 4) {
        $out .= mb_chr(hexdec(substr($hex, $i, 4)), 'UTF-8');
    }

    return $out;
}

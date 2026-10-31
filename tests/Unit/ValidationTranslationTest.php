<?php

it('menerjemahkan setiap pesan validasi bawaan ke bahasa Indonesia', function () {
    $english = require base_path('vendor/laravel/framework/src/Illuminate/Translation/lang/en/validation.php');
    $indonesian = require lang_path('id/validation.php');

    $flatten = function (array $lines, string $prefix = '') use (&$flatten): array {
        $keys = [];

        foreach ($lines as $key => $value) {
            $path = $prefix === '' ? (string) $key : $prefix.'.'.$key;

            if (is_array($value)) {
                $keys = array_merge($keys, $flatten($value, $path));

                continue;
            }

            $keys[] = $path;
        }

        return $keys;
    };

    $missing = array_values(array_diff(
        $flatten(Arr::except($english, ['custom'])),
        $flatten($indonesian),
    ));

    expect($missing)->toBe([]);
});

it('tidak menyisakan pesan validasi berbahasa Inggris', function () {
    $english = require base_path('vendor/laravel/framework/src/Illuminate/Translation/lang/en/validation.php');
    $indonesian = require lang_path('id/validation.php');

    foreach (Arr::dot(Arr::except($english, ['custom'])) as $key => $message) {
        $translated = trans('validation.'.$key);

        expect($translated)->not->toBe($message)
            ->and($translated)->not->toBe('validation.'.$key);
    }
});

it('menerjemahkan label paginasi bawaan', function () {
    expect(trans('pagination.previous'))->toContain('Sebelumnya')
        ->and(trans('pagination.next'))->toContain('Berikutnya')
        ->and(trans('Pagination Navigation'))->toBe('Navigasi halaman')
        ->and(trans('Go to page :page', ['page' => 4]))->toBe('Buka halaman 4');
});

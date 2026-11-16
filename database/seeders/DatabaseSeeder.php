<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Data demo hanya untuk lingkungan non-produksi: produksi tidak boleh
        // memiliki akun contoh dengan kata sandi yang diketahui umum.
        if (app()->environment('production')) {
            $this->command?->info('Lingkungan produksi: seeder demo dilewati.');

            return;
        }

        $this->call(DemoSeeder::class);
    }
}

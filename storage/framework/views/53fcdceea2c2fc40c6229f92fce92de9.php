<?php if (isset($component)) { $__componentOriginal8c0e86a062c1c5bb6d0e151b7076f3fd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c0e86a062c1c5bb6d0e151b7076f3fd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.public','data' => ['title' => 'Hadir.In - Presensi dengan lokasi dan selfie','description' => 'Hadir.In mencatat kehadiran dengan lokasi dan selfie, lalu merapikan jadwal, izin, dan laporan bulanan untuk kantor, toko, dan tim lapangan.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.public'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Hadir.In - Presensi dengan lokasi dan selfie','description' => 'Hadir.In mencatat kehadiran dengan lokasi dan selfie, lalu merapikan jadwal, izin, dan laporan bulanan untuk kantor, toko, dan tim lapangan.']); ?>
    <section class="border-b border-divider">
        <div class="mx-auto grid max-w-[1120px] gap-10 px-6 py-14 md:py-20 lg:grid-cols-[1.1fr_0.9fr] lg:items-center">
            <div>
                <p class="eyebrow">Presensi untuk organisasi Indonesia</p>
                <h1 class="mt-3 max-w-[520px] font-display text-[40px] font-semibold leading-[1.08] tracking-[-0.03em] md:text-[56px]">
                    Absensi yang jelas, tanpa ribet.
                </h1>
                <p class="mt-4 max-w-[460px] text-[16px] leading-7 text-secondary">
                    Catat kehadiran dengan lokasi dan selfie, lalu tutup bulan dengan laporan yang siap diunduh.
                </p>

                <div class="mt-7 flex flex-wrap items-center gap-3">
                    <a href="<?php echo e(route('register')); ?>" class="btn btn-primary btn-lg">Mulai dengan organisasi</a>
                    <a href="<?php echo e(route('login')); ?>" class="btn btn-secondary btn-lg">Masuk</a>
                </div>

                <dl class="mt-10 grid max-w-[460px] grid-cols-3 border-t border-divider pt-5">
                    <div>
                        <dt class="text-[11px] uppercase tracking-[0.08em] text-muted">Lokasi</dt>
                        <dd class="mt-1 text-[13px] leading-5">Radius dan buffer per titik kerja</dd>
                    </div>
                    <div class="border-l border-divider pl-4">
                        <dt class="text-[11px] uppercase tracking-[0.08em] text-muted">Selfie</dt>
                        <dd class="mt-1 text-[13px] leading-5">Tersimpan privat, akses bertanda tangan</dd>
                    </div>
                    <div class="border-l border-divider pl-4">
                        <dt class="text-[11px] uppercase tracking-[0.08em] text-muted">Laporan</dt>
                        <dd class="mt-1 text-[13px] leading-5">CSV bulanan siap untuk Excel</dd>
                    </div>
                </dl>
            </div>

            <div class="relative">
                <p class="mb-2 text-right text-[11px] uppercase tracking-[0.08em] text-muted">Contoh tampilan</p>
                <div class="rounded-[8px] border-[1.5px] border-divider bg-surface p-5">
                    <div class="flex items-start justify-between gap-3 border-b border-divider pb-4">
                        <div>
                            <p class="text-[11px] uppercase tracking-[0.08em] text-muted">Kamis, 29 Oktober 2026</p>
                            <p class="mt-1 font-display text-[18px] font-semibold">Kantor Pusat</p>
                            <p class="text-[12px] text-muted">08.00 - 17.00, radius 50 m</p>
                        </div>
                        <span class="stamp">Hadir</span>
                    </div>
                    <div class="grid grid-cols-2 divide-x divide-divider border-b border-divider py-4">
                        <div>
                            <p class="text-[11px] uppercase tracking-[0.08em] text-muted">Masuk</p>
                            <p class="mt-1 font-display text-[26px] font-semibold tabular-nums leading-none">07.56</p>
                        </div>
                        <div class="pl-4">
                            <p class="text-[11px] uppercase tracking-[0.08em] text-muted">Pulang</p>
                            <p class="mt-1 font-display text-[26px] font-semibold tabular-nums leading-none">17.04</p>
                        </div>
                    </div>
                    <ul class="mt-4 space-y-2 text-[13px] text-muted">
                        <li class="flex items-center gap-2"><span class="inline-block h-1 w-1 rounded-full bg-present" aria-hidden="true"></span>12 m dari titik lokasi</li>
                        <li class="flex items-center gap-2"><span class="inline-block h-1 w-1 rounded-full bg-present" aria-hidden="true"></span>Akurasi GPS 8 m</li>
                        <li class="flex items-center gap-2"><span class="inline-block h-1 w-1 rounded-full bg-muted" aria-hidden="true"></span>Selfie tersimpan, akses terbatas</li>
                    </ul>
                </div>
                <div class="mt-3 flex items-center justify-between border-t border-divider pt-3 text-[12px] text-muted">
                    <span>Angka dan jarak adalah contoh, bukan data pelanggan.</span>
                    <span class="font-mono tabular-nums">07.56</span>
                </div>
            </div>
        </div>
    </section>

    <section class="border-b border-divider">
        <div class="mx-auto max-w-[1120px] px-6 py-14 md:py-16">
            <p class="eyebrow">Masalah yang sering muncul</p>
            <h2 class="mt-2 max-w-[620px] font-display text-[28px] font-semibold leading-tight tracking-[-0.02em] md:text-[34px]">
                Absensi manual menyisakan pertanyaan di akhir bulan.
            </h2>

            <div class="mt-8 grid gap-6 border-t border-divider pt-6 md:grid-cols-3">
                <div>
                    <p class="font-mono text-[12px] tabular-nums text-muted">01</p>
                    <h3 class="mt-2 text-[15px] font-semibold">Tidak jelas siapa yang benar-benar hadir</h3>
                    <p class="mt-1.5 text-[14px] leading-6 text-muted">
                        Catatan di grup pesan mudah terlewat. Titik lokasi dan waktu tidak tercatat, sehingga sulit ditelusuri.
                    </p>
                </div>
                <div class="md:border-l md:border-divider md:pl-6">
                    <p class="font-mono text-[12px] tabular-nums text-muted">02</p>
                    <h3 class="mt-2 text-[15px] font-semibold">Izin tercampur dengan obrolan harian</h3>
                    <p class="mt-1.5 text-[14px] leading-6 text-muted">
                        Pengajuan cuti dan sakit hilang di antara pesan lain. Persetujuan tidak tercatat rapi.
                    </p>
                </div>
                <div class="md:border-l md:border-divider md:pl-6">
                    <p class="font-mono text-[12px] tabular-nums text-muted">03</p>
                    <h3 class="mt-2 text-[15px] font-semibold">Rekap bulanan dikerjakan ulang</h3>
                    <p class="mt-1.5 text-[14px] leading-6 text-muted">
                        Data disalin dari beberapa tempat, lalu dihitung manual. Selisih angka baru ketahuan belakangan.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="border-b border-divider">
        <div class="mx-auto max-w-[1120px] px-6 py-14 md:py-16">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="eyebrow">Cara kerja</p>
                    <h2 class="mt-2 font-display text-[28px] font-semibold leading-tight tracking-[-0.02em] md:text-[34px]">Empat langkah, satu alur.</h2>
                </div>
                <a href="<?php echo e(route('public.how')); ?>" class="link text-[13px]">Baca cara kerja lengkap</a>
            </div>

            <ol class="mt-8 grid gap-px overflow-hidden border-y border-divider bg-divider sm:grid-cols-2 lg:grid-cols-4">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [
                    ['01', 'Atur organisasi', 'Tentukan zona waktu, jam kerja, lokasi, dan radius.'],
                    ['02', 'Susun jadwal', 'Tetapkan jadwal per anggota atau terapkan massal per divisi.'],
                    ['03', 'Anggota absen', 'Absen masuk dan pulang dengan lokasi serta selfie.'],
                    ['04', 'Tinjau dan ekspor', 'Selesaikan pengecualian, lalu unduh laporan bulanan.'],
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$number, $title, $body]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="bg-paper px-5 py-6">
                        <p class="font-mono text-[12px] tabular-nums text-stamp"><?php echo e($number); ?></p>
                        <h3 class="mt-2 text-[15px] font-semibold"><?php echo e($title); ?></h3>
                        <p class="mt-1.5 text-[13px] leading-6 text-muted"><?php echo e($body); ?></p>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </ol>
        </div>
    </section>

    <section class="border-b border-divider">
        <div class="mx-auto max-w-[1120px] px-6 py-14 md:py-16">
            <p class="eyebrow">Untuk siapa</p>
            <h2 class="mt-2 max-w-[560px] font-display text-[28px] font-semibold leading-tight tracking-[-0.02em] md:text-[34px]">
                Dibuat untuk kerja yang berpindah tempat.
            </h2>

            <div class="mt-8 grid gap-6 md:grid-cols-3">
                <div class="border-t border-divider pt-5">
                    <h3 class="text-[15px] font-semibold">Kantor dengan jam tetap</h3>
                    <p class="mt-1.5 text-[14px] leading-6 text-muted">Satu lokasi utama, jam kerja jelas, dan rekap bulanan untuk HR.</p>
                </div>
                <div class="border-t border-divider pt-5">
                    <h3 class="text-[15px] font-semibold">Toko dengan beberapa cabang</h3>
                    <p class="mt-1.5 text-[14px] leading-6 text-muted">Setiap cabang punya titik dan radius sendiri. Peninjau melihat cabangnya.</p>
                </div>
                <div class="border-t border-divider pt-5">
                    <h3 class="text-[15px] font-semibold">Tim lapangan</h3>
                    <p class="mt-1.5 text-[14px] leading-6 text-muted">Absen dari lokasi kerja mana pun yang terdaftar, dengan catatan jarak.</p>
                </div>
            </div>

            <p class="mt-8">
                <a href="<?php echo e(route('public.who')); ?>" class="link text-[13px]">Lihat contoh penggunaan</a>
            </p>
        </div>
    </section>

    <section class="border-b border-divider">
        <div class="mx-auto max-w-[1120px] px-6 py-14 md:py-16">
            <div class="grid gap-8 lg:grid-cols-[0.8fr_1.2fr]">
                <div>
                    <p class="eyebrow">Pertanyaan</p>
                    <h2 class="mt-2 font-display text-[28px] font-semibold leading-tight tracking-[-0.02em] md:text-[34px]">Yang sering ditanyakan.</h2>
                    <p class="mt-3 max-w-[340px] text-[14px] leading-6 text-muted">
                        Bila ada yang belum terjawab, halaman pertanyaan memuat daftar yang lebih lengkap.
                    </p>
                    <a href="<?php echo e(route('public.faq')); ?>" class="link mt-4 inline-block text-[13px]">Semua pertanyaan</a>
                </div>

                <div class="divide-y divide-divider border-y border-divider">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [
                        ['Apakah selfie wajib?', 'Bisa diatur per organisasi. Bila diaktifkan, selfie diambil saat absen dan disimpan privat.'],
                        ['Bagaimana bila sinyal GPS buruk?', 'Absen tetap dicatat dengan tanda akurasi rendah. Peninjau dapat memeriksa dan menerima.'],
                        ['Apakah bisa dipakai di beberapa cabang?', 'Ya. Setiap cabang punya lokasi dan radius sendiri dalam satu organisasi.'],
                        ['Apakah ada aplikasi ponsel?', 'Hadir.In dibuka lewat peramban ponsel. Tidak ada unduhan aplikasi.'],
                    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$question, $answer]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <details class="group py-4">
                            <summary class="flex cursor-pointer items-center justify-between gap-4 text-[14px] font-medium">
                                <span><?php echo e($question); ?></span>
                                <span class="text-muted transition-transform group-open:rotate-45" aria-hidden="true">+</span>
                            </summary>
                            <p class="mt-2 max-w-[620px] text-[13px] leading-6 text-muted"><?php echo e($answer); ?></p>
                        </details>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <section>
        <div class="mx-auto flex max-w-[1120px] flex-col items-start justify-between gap-6 px-6 py-14 md:flex-row md:items-center md:py-16">
            <div>
                <h2 class="font-display text-[28px] font-semibold leading-tight tracking-[-0.02em] md:text-[34px]">Siap merapikan absensi?</h2>
                <p class="mt-2 max-w-[420px] text-[14px] leading-6 text-muted">
                    Daftarkan organisasi, undang anggota, dan mulai dari satu lokasi kerja.
                </p>
            </div>
            <a href="<?php echo e(route('register')); ?>" class="btn btn-primary btn-lg">Mulai dengan organisasi</a>
        </div>
    </section>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8c0e86a062c1c5bb6d0e151b7076f3fd)): ?>
<?php $attributes = $__attributesOriginal8c0e86a062c1c5bb6d0e151b7076f3fd; ?>
<?php unset($__attributesOriginal8c0e86a062c1c5bb6d0e151b7076f3fd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8c0e86a062c1c5bb6d0e151b7076f3fd)): ?>
<?php $component = $__componentOriginal8c0e86a062c1c5bb6d0e151b7076f3fd; ?>
<?php unset($__componentOriginal8c0e86a062c1c5bb6d0e151b7076f3fd); ?>
<?php endif; ?>
<?php /**PATH /home/raihan/Workspace/coders/Hadir.In/resources/views/public/home.blade.php ENDPATH**/ ?>
<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\TenantStatusController;
use App\Http\Controllers\Auth\TenantSwitchController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\Platform\TenantStatusController as PlatformTenantStatusController;
use App\Http\Controllers\PublicPageController;
use App\Http\Controllers\ReportExportController;
use App\Http\Controllers\SelfieController;
use App\Http\Controllers\SeoController;
use App\Livewire\Attendance;
use App\Livewire\Dashboard;
use App\Livewire\Divisions;
use App\Livewire\Holidays;
use App\Livewire\Leave;
use App\Livewire\Locations;
use App\Livewire\Members;
use App\Livewire\Platform;
use App\Livewire\Reports;
use App\Livewire\Reviews;
use App\Livewire\Schedules;
use App\Livewire\Settings;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Publik
|--------------------------------------------------------------------------
*/

Route::get('/', [PublicPageController::class, 'home'])->name('home')->middleware('cache.headers:private;max_age=300;etag');
Route::get('/cara-kerja', [PublicPageController::class, 'how'])->name('public.how')->middleware('cache.headers:private;max_age=300;etag');
Route::get('/untuk-siapa', [PublicPageController::class, 'who'])->name('public.who')->middleware('cache.headers:private;max_age=300;etag');
Route::get('/pertanyaan', [PublicPageController::class, 'faq'])->name('public.faq')->middleware('cache.headers:private;max_age=300;etag');
Route::get('/privasi', [PublicPageController::class, 'privacy'])->name('public.privacy')->middleware('cache.headers:private;max_age=300;etag');
Route::get('/ketentuan', [PublicPageController::class, 'terms'])->name('public.terms')->middleware('cache.headers:private;max_age=300;etag');

Route::get('/health', [HealthController::class, 'show'])->name('health');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');

Route::middleware('guest')->group(function () {
    Route::get('/masuk', [LoginController::class, 'show'])->name('login');
    Route::post('/masuk', [LoginController::class, 'store'])
        ->middleware('throttle:login')
        ->name('login.store');

    Route::get('/daftar', [RegisterController::class, 'show'])->name('register');
    Route::post('/daftar', [RegisterController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('register.store');

    Route::get('/lupa-kata-sandi', [PasswordResetController::class, 'request'])->name('password.request');
    Route::post('/lupa-kata-sandi', [PasswordResetController::class, 'send'])
        ->middleware('throttle:6,1')
        ->name('password.email');
    Route::get('/atur-ulang-kata-sandi/{token}', [PasswordResetController::class, 'edit'])->name('password.reset');
    Route::post('/atur-ulang-kata-sandi', [PasswordResetController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('password.update');
});

Route::get('/undangan/{token}', [InvitationController::class, 'show'])->name('invitations.show');
Route::post('/undangan/{token}', [InvitationController::class, 'accept'])
    ->middleware('throttle:10,1')
    ->name('invitations.accept');

Route::middleware('auth')->group(function () {
    Route::post('/keluar', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/status', [TenantStatusController::class, 'show'])->name('tenant.status');
});

/*
|--------------------------------------------------------------------------
| Aplikasi
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'tenant', 'tenant.active'])
    ->prefix('app')
    ->name('app.')
    ->group(function () {
        Route::get('/', Dashboard\Home::class)->name('home');
        Route::get('/ringkasan', Dashboard\Overview::class)->name('overview');

        Route::get('/presensi', Attendance\Index::class)->name('attendance.index');
        Route::get('/presensi/masuk', Attendance\CheckIn::class)->name('attendance.check-in');
        Route::get('/presensi/pulang', Attendance\CheckOut::class)->name('attendance.check-out');
        Route::get('/presensi/{attendanceDay}', Attendance\Show::class)->name('attendance.show');

        Route::get('/jadwal', Schedules\Index::class)->name('schedules.index');
        Route::get('/jadwal/terapkan', Schedules\BulkApply::class)
            ->middleware('can:manage-schedules')
            ->name('schedules.bulk');
        Route::get('/jadwal/{schedule}/ubah', Schedules\Edit::class)->name('schedules.edit');

        Route::get('/izin', Leave\Index::class)->name('leave.index');
        Route::get('/izin/baru', Leave\Create::class)->name('leave.create');
        Route::get('/izin/{leaveRequest}', Leave\Show::class)->name('leave.show');

        Route::middleware('can:review-attendance')->group(function () {
            Route::get('/tinjauan', Reviews\Index::class)->name('reviews.index');
            Route::get('/tinjauan/presensi/{review}', Reviews\AttendanceReview::class)->name('reviews.attendance');
            Route::get('/tinjauan/koreksi/{correction}', Reviews\CorrectionReview::class)->name('reviews.correction');
            Route::get('/tinjauan/izin/{leaveRequest}', Reviews\LeaveReview::class)->name('reviews.leave');
        });

        Route::get('/laporan', Reports\Index::class)->name('reports.index');
        Route::get('/laporan/ekspor', [ReportExportController::class, 'csv'])->name('reports.export');

        Route::middleware('can:manage-members')->group(function () {
            Route::get('/anggota', Members\Index::class)->name('members.index');
            Route::get('/anggota/undang', Members\Invite::class)->name('members.invite');
            Route::get('/anggota/undangan', Members\Invitations::class)->name('invitations.index');
            Route::get('/anggota/{membership}/ubah', Members\Edit::class)->name('members.edit');
        });

        Route::middleware('can:manage-settings')->group(function () {
            Route::get('/lokasi', Locations\Index::class)->name('locations.index');
            Route::get('/lokasi/baru', Locations\Form::class)->name('locations.create');
            Route::get('/lokasi/{location}/ubah', Locations\Form::class)->name('locations.edit');
            Route::get('/divisi', Divisions\Index::class)->name('divisions.index');
            Route::get('/hari-libur', Holidays\Index::class)->name('holidays.index');
            Route::get('/pengaturan', Settings\Index::class)->name('settings.index');
        });

        Route::get('/profil', Settings\Profile::class)->name('profile');
        Route::get('/tampilan', Settings\Appearance::class)->name('appearance');
    });

// Perpindahan organisasi tidak memakai tenant.active: pengguna organisasi
// yang ditangguhkan tetap harus bisa berpindah ke organisasi aktifnya (BR-64).
Route::middleware('auth')
    ->prefix('app')
    ->name('app.')
    ->group(function () {
        Route::post('/organisasi/{tenant}/pilih', [TenantSwitchController::class, 'store'])
            ->name('tenants.switch');
    });

Route::get('/selfie/{event}', [SelfieController::class, 'show'])
    ->middleware(['auth', 'tenant', 'signed'])
    ->name('selfie.show');

/*
|--------------------------------------------------------------------------
| Platform
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'platform'])
    ->prefix('platform')
    ->name('platform.')
    ->group(function () {
        Route::get('/', Platform\Dashboard::class)->name('dashboard');
        Route::get('/tenant', Platform\Tenants\Index::class)->name('tenants.index');
        Route::get('/tenant/{tenant}', Platform\Tenants\Show::class)->name('tenants.show');
        Route::post('/tenant/{tenant}/aktifkan', [PlatformTenantStatusController::class, 'activate'])->name('tenants.activate');
        Route::post('/tenant/{tenant}/tangguhkan', [PlatformTenantStatusController::class, 'suspend'])->name('tenants.suspend');
        Route::post('/tenant/{tenant}/aktifkan-kembali', [PlatformTenantStatusController::class, 'restore'])->name('tenants.restore');
    });

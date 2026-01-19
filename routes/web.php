<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/email', function () { return view('mail'); });
Route::get('/features', function () { return view('features'); });

Route::get('/documentation', function () {
    return view('documentation', [
        'totalAgenda' => \App\Models\Agenda::count(),
        'systemStatus' => 'Active',
        'lastBackup' => now()->format('d M Y'),
    ]);
});

Route::get('/test-otomatis', function () {
    return response()->json([
        'status' => 'Berhasil!',
        'message' => 'Sistem otomatisasi Laravel Marison sudah aktif.',
        'timestamp' => now()->toDateTimeString(),
        'source' => 'Folder Repositories (Tanpa Copy Manual)'
    ]);
});

/*
|--------------------------------------------------------------------------
| Authenticated & Verified Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {

    /* --- SEMUA ROLE (Staff, Manager, Admin) --- */
    Volt::route('/dashboard', 'dashboard.dashboard')->name('dashboard');
    Volt::route('/calendar', 'calendar.index')->name('calendar');
    Volt::route('/dashboard/monitoring', 'dashboard.monitoring-list')->name('dashboard.monitoring');
    Volt::route('/agenda/create', 'agenda.create')->name('agenda.create');
    Volt::route('/dashboard/history', 'dashboard.history-list')->name('dashboard.history');

    /* --- MANAGERIAL AREA (Manager & Admin) --- */
    Route::middleware(['role:manager,admin'])->group(function () {
        Volt::route('/manager/approval', 'dashboard.approval-list')
            ->name('manager.approval');
            Volt::route('/manager/issues', 'dashboard.issue')->name('manager.issues');
    });

    /* --- ADMIN ONLY AREA --- */
    Route::middleware(['role:admin'])->group(function () {
        // Halaman Manajemen User (untuk ganti role & parent_id)
        Volt::route('/admin/users', 'admin.user-index')
            ->name('admin.users');

        Volt::route('/dashboard/logs', 'dashboard.logs')
            ->name('dashboard.logs');
    });

    /* --- USER SETTINGS --- */
    Route::prefix('settings')->group(function () {
        Route::redirect('/', 'settings/profile');
        Volt::route('profile', 'settings.profile')->name('profile.edit');
        Volt::route('password', 'settings.password')->name('user-password.edit');
        Volt::route('appearance', 'settings.appearance')->name('appearance.edit');

        Volt::route('two-factor', 'settings.two-factor')
            ->middleware(when(
                Features::canManageTwoFactorAuthentication() &&
                Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'], []
            ))
            ->name('two-factor.show');
    });
});

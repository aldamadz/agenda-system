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

/*
|--------------------------------------------------------------------------
| Authenticated & Verified Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {

    /* --- DASHBOARD CENTRAL --- */
    // Dashboard Utama (Overview Statistik)
    Volt::route('/dashboard', 'dashboard.dashboard')
        ->name('dashboard');

    /* --- AGENDA & PROGRESS (STAFF / MANAGER) --- */

    // Halaman Kalender (Tampilan Visual Jadwal)
    Volt::route('/calendar', 'calendar.index')
        ->name('calendar');

    // Halaman Monitoring (List Detail Progres)
    Volt::route('/dashboard/monitoring', 'dashboard.monitoring-list')
        ->name('dashboard.monitoring');

    // Halaman Buat Agenda Baru
    Volt::route('/agenda/create', 'agenda.create')
        ->name('agenda.create');

    // Halaman Riwayat (Arsip Selesai)
    Volt::route('/dashboard/history', 'dashboard.history-list')
        ->name('dashboard.history');

    /* --- MANAGERIAL AREA --- */

    // Halaman Persetujuan (Hanya muncul jika user adalah Manager)
    Volt::route('/manager/approval', 'dashboard.approval-list')
        ->name('manager.approval');

    Volt::route('/dashboard/logs', 'dashboard.logs')
        ->name('dashboard.logs');

    /* --- USER SETTINGS (PROFILE, PASSWORD, ETC) --- */
    Route::prefix('settings')->group(function () {
        Route::redirect('/', 'settings/profile');

        Volt::route('profile', 'settings.profile')
            ->name('profile.edit');

        Volt::route('password', 'settings.password')
            ->name('user-password.edit');

        Volt::route('appearance', 'settings.appearance')
            ->name('appearance.edit');

        // Fitur Keamanan (Two Factor Authentication)
        Volt::route('two-factor', 'settings.two-factor')
            ->middleware(
                when(
                    Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(
                        Features::twoFactorAuthentication(),
                        'confirmPassword'
                    ),
                    ['password.confirm'],
                    []
                )
            )
            ->name('two-factor.show');
    });
});

<?php

namespace App\Providers;

use App\Models\AgendaStep;
use App\Observers\AgendaStepObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        AgendaStep::observe(AgendaStepObserver::class);
        \Carbon\Carbon::setLocale('id');
    date_default_timezone_set('Asia/Jakarta');
    }
}

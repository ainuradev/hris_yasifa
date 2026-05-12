<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
        // Observer untuk Audit Trail
        \App\Models\Employee::observe(\App\Observers\AuditObserver::class);
        \App\Models\Payroll::observe(\App\Observers\AuditObserver::class);
        \App\Models\SalaryComponent::observe(\App\Observers\AuditObserver::class);
        \App\Models\SalaryRate::observe(\App\Observers\AuditObserver::class);

        // Paksa HTTPS hanya untuk request yang datang lewat tunnel ngrok.
        if (! app()->runningInConsole() && str_contains(request()->getHost(), 'ngrok-free.')) {
            URL::forceScheme('https');
        }
    }
}

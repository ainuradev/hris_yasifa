<?php

namespace App\Providers;

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
        \App\Models\Employee::observe(\App\Observers\AuditObserver::class);
        \App\Models\Payroll::observe(\App\Observers\AuditObserver::class);
        \App\Models\SalaryComponent::observe(\App\Observers\AuditObserver::class);
        \App\Models\SalaryRate::observe(\App\Observers\AuditObserver::class);
    }
}

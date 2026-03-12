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
        if (request()->has('token') && !request()->header('Authorization')) {
            request()->headers->set('Authorization', 'Bearer ' . request()->query('token'));
        }
    }
}

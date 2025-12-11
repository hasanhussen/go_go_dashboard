<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;

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
        Paginator::useBootstrapFive();
        // RateLimiter
        RateLimiter::for('global', function () {
            return Limit::perMinute(5)->by(request()->ip())->response(function (array $headers) {
                return response()->json(['message' => 'Too many requests. Please slow down.'], 429)->withHeaders($headers);
            });             
        });
    }
}

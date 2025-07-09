<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
        $rateLimit = config('app.rate_limite');
        RateLimiter::for('api', function (Request $request) use ($rateLimit) {
            return Limit::perMinute($rateLimit)->by($request->user()?->id ?: $request->ip());
        });
    }
}

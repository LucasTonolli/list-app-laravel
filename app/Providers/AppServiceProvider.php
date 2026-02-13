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
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(50)->by($request->user()->uuid ?: $request->ip());
        });

        RateLimiter::for('invitations', function (Request $request) {
            return Limit::perMinute(5)->by($request->user()->uuid ?: $request->ip());
        });

        RateLimiter::for('accept_invite', function (Request $request) {
            return Limit::perMinute(5)->by($request->user()->uuid ?: $request->ip());
        });

        RateLimiter::for('identities', function (Request $request) {
            return Limit::perMinute(5)->by($request->user()->uuid ?: $request->ip());
        });
    }
}

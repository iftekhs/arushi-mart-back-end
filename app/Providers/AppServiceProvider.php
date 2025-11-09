<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

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
        Model::unguard();

        RateLimiter::for('auth-requests', function (Request $request) {
            $max = app()->environment('local') ? 1000 : 3;
            $by = $request->input('email') ? $request->input('email') . '|' . $request->ip() : $request->ip();
            return Limit::perMinute($max)->by($by);
        });

        RateLimiter::for('resend-otp', function (Request $request) {
            $max = app()->environment('local') ? 1000 : 1;
            $by = $request->input('email') ? $request->input('email') . '|' . $request->ip() : $request->ip();
            return Limit::perMinute($max)->by($by);
        });

        RateLimiter::for('otp-checks', function (Request $request) {
            $max = app()->environment('local') ? 1000 : config('otp.verify_max', 6);
            $by = $request->input('email') ? $request->input('email') . '|' . $request->ip() : $request->ip();
            return Limit::perMinute($max)->by($by);
        });

        RateLimiter::for('otp-verifies', function (Request $request) {
            $max = app()->environment('local') ? 1000 : config('otp.verify_max', 5);
            $by = $request->input('email') ? $request->input('email') . '|' . $request->ip() : $request->ip();
            return Limit::perMinute($max)->by($by);
        });
    }
}

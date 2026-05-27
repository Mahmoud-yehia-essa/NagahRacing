<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Http\Kernel;
use App\Http\Middleware\CheckUserRole; // Import the middleware
use Illuminate\Pagination\Paginator;

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
    public function boot(Kernel $kernel): void
    {
        // Register middleware
        // $kernel->appendMiddlewareToGroup('web', CheckUserRole::class);
            Paginator::useBootstrapFive();

            \DB::listen(function($query) {
                if ($query->time > 100 || request()->has('debug_queries') || env('APP_DEBUG')) {
                    \Log::warning("Slow SQL (>100ms) on URL " . request()->fullUrl() . ": {$query->sql} | Time: {$query->time}ms | Bindings: " . json_encode($query->bindings));
                }
            });
    }
}

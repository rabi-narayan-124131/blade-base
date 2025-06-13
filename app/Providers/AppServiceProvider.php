<?php

namespace App\Providers;

use App\Console\Commands\FixComposer;
use App\Console\Commands\SetupDb;
use Illuminate\Console\Application;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register the FixComposer command
        $this->app->singleton(FixComposer::class);

        // Register the SetupDb command
        $this->app->singleton(SetupDb::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                SetupDb::class,
                FixComposer::class,
            ]);
        }

        // Enable Laravel Debugbar only in local environments
        if ($this->app->environment('local')) {
            Debugbar::enable();
        }
    }
}

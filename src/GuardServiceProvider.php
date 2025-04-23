<?php

namespace Supaapps\Guard;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Supaapps\Guard\Auth\DynamicJwtAuthDriver;
use Supaapps\Guard\Auth\JwtAuthDriver;

class GuardServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
            $this->publishes([
                __DIR__ . '/../config/sguard.php'
                    => config_path('sguard.php'),
                __DIR__ . '/../database/migrations'
                    => database_path('migrations')
            ]);
        }

        Auth::extend('supaapps-guard', function (Application $app, string $name, array $config) {
            return new JwtAuthDriver(
                Auth::createUserProvider($config['provider'])
            );
        });

        Auth::extend('supaapps-dynamic-guard', function (Application $app, string $name, array $config) {
            return new DynamicJwtAuthDriver(
                Auth::createUserProvider($config['provider'])
            );
        });
    }

    /**
     * Register any package services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/sguard.php', 'sguard');
    }
}

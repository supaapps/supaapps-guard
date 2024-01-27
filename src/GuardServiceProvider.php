<?php

namespace Supaapps\Guard;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
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
                __DIR__ . '/../config/supaapps-guard.php'
                    => config_path('supaapps-guard.php'),
            ]);
            $this->registerCommands();
        }

        Auth::extend('supaapps-guard', function (Application $app, string $name, array $config) {
            return new JwtAuthDriver(
                Auth::createUserProvider($config['provider'])
            );
        });
    }



}

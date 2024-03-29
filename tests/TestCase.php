<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Supaapps\Guard\GuardServiceProvider;
use Supaapps\Guard\Tests\Concerns\GenerateJwtToken;

use function Orchestra\Testbench\laravel_migration_path;

class TestCase extends BaseTestCase
{
    use RefreshDatabase;
    use GenerateJwtToken;

    protected function getPackageProviders($app)
    {
        return [
            GuardServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('auth.defaults.guard', 'jwt');
        $app['config']->set('auth.guards.jwt', [
            'driver' => 'supaapps-guard',
            'provider' => 'users',
        ]);

        $this->setAuthServerUrl();

        $app->afterResolving('migrator', static function ($migrator) {
            $migrator->path(laravel_migration_path());
        });
    }
}

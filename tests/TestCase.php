<?php

namespace Tests;

use Firebase\JWT\JWT;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Supaapps\Guard\GuardServiceProvider;

use function Orchestra\Testbench\laravel_migration_path;

class TestCase extends BaseTestCase
{
    use RefreshDatabase;

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
        $app['config']->set('sguard.auth_server_url', __DIR__);

        $app->afterResolving('migrator', static function ($migrator) {
            $migrator->path(laravel_migration_path());
        });
    }

    public function generateTestingJwtToken(array $payload = []): string
    {
        $payload = $payload + [
            'id' => (int) microtime(true), // token id
            'iss' => 'localhost',
            'sub' => (int) microtime(true),
            'first_name' => fake()->firstName,
            'last_name' => fake()->lastName,
            'email' => fake()->email,
            'aud' => config('sguard.realm_name'),
            'iat' => now()->timestamp,
            'exp' => now()->addMinutes(2)->timestamp,
            'scopes' => '/' . config('sguard.realm_name') . '/*',
        ];

        return JWT::encode(
            $payload,
            File::get(__DIR__ . '/keys/private_key'),
            File::get(__DIR__ . '/keys/algo')
        );
    }
}

<?php

namespace Supaapps\Guard\Tests\Concerns;

use Firebase\JWT\JWT;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\Concerns\MakesHttpRequests;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

trait GenerateJwtToken
{
    use MakesHttpRequests;

    public function withAccessTokenFor(Authenticatable $user, array $payload = []): string
    {
        $accessToken = $this->generateTestingJwtToken($payload + [
            'sub' => $user->id
        ]);

        request()->headers->add([
            'Authorization' => "Bearer {$accessToken}"
        ]);

        auth('jwt')->setRequest(
            $this->app->refresh('request', auth('jwt'), 'setRequest')
        );

        auth('jwt')->setUser($user);

        $this->withToken($accessToken);

        return $accessToken;
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
            File::get(__DIR__ . '/../../../tests/public/private_key'),
            File::get(__DIR__ . '/../../../tests/public/algo')
        );
    }

    public function setAuthServerUrl()
    {
        Config::set('sguard.auth_server_url', __DIR__ . '/../../../tests');
    }
}

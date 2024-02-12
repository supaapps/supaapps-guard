# Supaapps Auth Guard <!-- omit in toc -->

- [Installation](#installation)
  - [ENV vars](#env-vars)
  - [Add new custom guard](#add-new-custom-guard)
- [Usage example](#usage-example)
- [Testing](#testing)
  - [HTTP testing](#http-testing)

## Installation

`composer require supaapps/supaapps-guard`

### ENV vars

add env vars to your `.env`:

```env
SUPAAPPS_GUARD_AUTH_SERVER_URL=http://example.com
SUPAAPPS_GUARD_AUTH_REALM_NAME=myapp
```

### Add new custom guard

On `config/auth.php` add the new guard

```php
'guards' => [
    'jwt' => [
        'driver' => 'supaapps-guard',
        'provider' => 'users',
    ],
],
```

Also, set the default guard to `jwt`

```php
'defaults' => [
    'guard' => 'jwt',
    ...
```

## Usage example

on `routes/api.php`, add following lines

```php
Route::middleware('auth:jwt')->get('/user', function (Request $request) {
    return [
        $request->user(),
        auth()->firstName(),
        auth()->lastName(),
        auth()->email(),
        auth()->scopes(),
        auth()->scopesArray(),
    ];
});
```

*note*: `auth()` uses the default drive by default. If you didn't set the `jwt` as default driver then you need to call `auth('jwt')` on the previous usage example

## Testing

You can generate JWT token for testing. It will be generated with [private_key](./tests/public/private_key) from tests folder. And will be compared with `public_key` on same folder as well. **example**

```php
use Tests\TestCase;
use Supaapps\Guard\Tests\Concerns\GenerateJwtToken;

class CustomTest extends TestCase
{
    use GenerateJwtToken;

    public function testThatIAmActingAsUser(): void
    {
        $user = User::factory()->create();

        $this->withAccessTokenFor($user);

        $this->assertTrue(auth('jwt')->check());
        $this->assertTrue($user->id, auth('jwt')->id());
    }
}
```

### HTTP testing

`withAccessTokenFor` method is adding the `Bearer` token to `headers` which are sent by http tests. But you need to specify the server url somewhere on your tests. eg. `tests/CreatesApplication`:

```php
<?php
use Supaapps\Guard\Tests\Concerns\GenerateJwtToken;

trait CreatesApplication
{
    use GenerateJwtToken;

    public function createApplication(): Application
    {
        ...

        $this->setAuthServerUrl();
        return $app;
    }
}
```

Next run your http tests, for example:

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class CustomTest extends TestCase
{
    public function itReturnsTheAuthUser(): void
    {
        $user = User::factory()->create();

        $this->withAccessTokenFor($user);

        // assume you have /user endpoint that
        // - uses auth:jwt middleware
        // - and returns auth user
        $response = $this->getJson('/user');

        $response->assertOk()
            ->assertJson($user->toArray());
    }
}
```

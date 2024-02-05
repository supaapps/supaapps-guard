# Supaapps Auth Guard <!-- omit in toc -->

- [Installation](#installation)
  - [ENV vars](#env-vars)
  - [Add new custom guard](#add-new-custom-guard)
- [Usage example](#usage-example)
- [Testing](#testing)

## Installation

`composer require supaapps/supaapps-guard`

### ENV vars

add env vars to your `.env`:

```env
`SUPAAPPS_GUARD_AUTH_SERVER_URL=http://example.com`
`SUPAAPPS_GUARD_AUTH_REALM_NAME=myapp`
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

You can generate JWT token for testing. It will be generated with [private_key](./tests/keys/private_key) from tests folder. And will be compared with `public_key` on same folder as well. **example**

```php
use Tests\TestCase;
use Supaapps\Guard\Tests\Concerns\GenerateJwtToken;

class CustomTest extends TestCase
{
    use GenerateJwtToken;

    public function testThatIAmActingAsUser(): void
    {
        $user = User::create();

        $this->withAccessTokenFor($user);

        $this->assertTrue(auth('jwt')->check());
        $this->assertTrue($user->id, auth('jwt')->id());
    }
}
```

## TODO



`composer require supaapps/supaapps-guard`


env vars:

- `SUPAAPPS_GUARD_AUTH_SERVER_URL`
- `SUPAAPPS_GUARD_AUTH_REALM`


---


### auth.php

```php

    'guards' => [
        'jwt' => [
            'driver' => 'supaapps-guard',
            'provider' => 'users',
        ],
    ],

```


### example route

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

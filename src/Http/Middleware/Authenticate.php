<?php

namespace Supaapps\Guard\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class Authenticate extends Middleware
{
    protected function authenticate($request, array $guards)
    {
        $jwtGuard = Arr::first(
            $guards,
            fn (string $guard) => Str::startsWith($guard, "jwt(")
        );

        $jwtGuardIndex = array_search($jwtGuard, $guards);

        if ($jwtGuardIndex !== false) {
            array_splice($guards, $jwtGuardIndex, 1);
            preg_match("/^jwt\((.*)\)$/i", $jwtGuard, $matches);

            if (
                count($matches) == 2 &&
                $this->auth->guard('jwt')->check($matches[1])
            ) {
                return $this->auth->shouldUse('jwt');
            }
        }

        return parent::authenticate($request, $guards);
    }
}

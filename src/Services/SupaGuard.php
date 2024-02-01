<?php

namespace Supaapps\Guard\Services;

use Illuminate\Contracts\Auth\Authenticatable;

class SupaGuard
{
    public static function actingAs(Authenticatable $user)
    {
        // $user->withAccessToken($token);

        // if (isset($user->wasRecentlyCreated) && $user->wasRecentlyCreated) {
        //     $user->wasRecentlyCreated = false;
        // }

        // app('auth')->guard($guard)->setUser($user);

        // app('auth')->shouldUse($guard);

        // return $user;
    }
}

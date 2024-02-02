<?php

namespace Tests\Unit\Services;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Auth\User;
use Supaapps\Guard\Services\SupaGuard;
use Tests\TestCase;

class SupaGuardTest extends TestCase
{
    public function testCanBeActingAsAUser(): void
    {
        $user = User::create();

        try {
            // this should throw exception
            auth()->user();
        } catch (AuthenticationException $x) {
            $this->assertEquals(
                "A valid bearer token is required",
                $x->getMessage()
            );
        }

        SupaGuard::actingAs($user);

        dd(auth()->user());
    }
}

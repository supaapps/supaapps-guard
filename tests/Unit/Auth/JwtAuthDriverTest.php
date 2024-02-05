<?php

namespace Tests\Unit\Auth;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Auth\User;
use Tests\TestCase;

class JwtAuthDriverTest extends TestCase
{
    public function testCanBeActingAsAUser(): void
    {
        $user = User::create();

        try {
            // this should throw exception
            $this->assertFalse(auth()->check());
        } catch (AuthenticationException $x) {
            $this->assertEquals(
                'A valid bearer token is required',
                $x->getMessage()
            );
        }

        $this->withAccessTokenFor($user);

        $this->assertTrue(auth()->check());
        $this->assertEquals(1, auth()->user()?->id);
    }
}

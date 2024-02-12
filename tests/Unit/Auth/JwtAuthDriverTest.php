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

    public function testItChecksForRoles(): void
    {
        $user = User::create();

        $this->withAccessTokenFor($user, [
            'scopes' => '/root/* /foo/bar'
        ]);

        $this->assertEquals($user->id, auth()->id());
        $this->assertTrue(auth()->hasRole('\/root\/*'));
    }

    public function testItReturnsARole(): void
    {
        $user = User::create();

        $this->withAccessTokenFor($user, [
            'scopes' => '/foo/bar /root/user:1 /root/user /root/users:u'
        ]);

        $this->assertEquals($user->id, auth()->id());
        // $this->assertFalse(auth()->hasRole('/root/*'));
        $this->assertEquals([
            '/root/user:1',
            '1',
        ], auth()->role('\/root\/user:(\d+(?!\S))'));
    }
}

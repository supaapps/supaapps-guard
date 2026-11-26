<?php

namespace Tests\Unit\Auth;

use DomainException;
use Firebase\JWT\JWT;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class JwtSecurityTest extends TestCase
{
    public function testExpiredTokenIsRejected(): void
    {
        $this->assertRejected($this->generateTestingJwtToken([
            'exp' => now()->subMinute()->timestamp,
        ]));
    }

    public function testWrongRealmIsRejected(): void
    {
        $this->assertRejected($this->generateTestingJwtToken([
            'aud' => 'another-application',
        ]));
    }

    public function testModifiedSignatureIsRejected(): void
    {
        $parts = explode('.', $this->generateTestingJwtToken());
        $parts[2][0] = $parts[2][0] === 'A' ? 'B' : 'A';
        $this->assertRejected(implode('.', $parts));
    }

    public function testMalformedTokenIsRejected(): void
    {
        $this->assertRejected('not-a-jwt');
    }

    public function testUndersizedHmacKeyIsRejected(): void
    {
        $this->expectException(DomainException::class);
        JWT::encode(['sub' => 'fixture-user'], 'short-key', 'HS256');
    }

    private function assertRejected(string $token): void
    {
        Cache::put('supaapps_jwt/revoked_ids', [], 60);
        request()->headers->set('Authorization', 'Bearer ' . $token);
        auth()->forgetGuards();
        $this->expectException(AuthenticationException::class);
        auth()->check();
    }
}

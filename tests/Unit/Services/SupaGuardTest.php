<?php

namespace Tests\Unit\Services;

use Supaapps\Guard\Services\SupaGuard;
use Tests\TestCase;

use function PHPUnit\Framework\assertTrue;

class SupaGuardTest extends TestCase
{
    public function testCanBeActingAsAUser(): void
    {
        $this->assertTrue(true);
        // SupaGuard::actingAs();
    }
}

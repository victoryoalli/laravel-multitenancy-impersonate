<?php

namespace VictorYoalli\MultitenancyImpersonate\Tests\Models;

use Spatie\Multitenancy\Models\Tenant;
use VictorYoalli\MultitenancyImpersonate\Models\ImpersonateToken;
use VictorYoalli\MultitenancyImpersonate\Tests\TestCase;

class ImpersonateTokenTest extends TestCase {

    /** @test */
    public function it_runs()
    {
        // ImpersonateToken::create(['tenant_id'=>1]);
        $this->assertTrue(true);
    }
}

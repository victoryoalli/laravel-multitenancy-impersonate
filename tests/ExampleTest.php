<?php

namespace VictorYoalli\MultitenancyImpersonate\Tests;

use Orchestra\Testbench\TestCase;
use VictorYoalli\MultitenancyImpersonate\MultitenancyImpersonateServiceProvider;

class ExampleTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [MultitenancyImpersonateServiceProvider::class];
    }

    /** @test */
    public function true_is_true()
    {
        $this->assertTrue(true);
    }
}

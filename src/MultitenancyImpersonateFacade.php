<?php

namespace VictorYoalli\MultitenancyImpersonate;

use Illuminate\Support\Facades\Facade;

/**
 * @see \VictorYoalli\MultitenancyImpersonate\MultitenancyImpersonate\MultitenancyImpersonateClass
 */
class MultitenancyImpersonateFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'multitenancy-impersonate';
    }
}

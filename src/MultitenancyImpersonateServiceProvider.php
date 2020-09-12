<?php

namespace VictorYoalli\MultitenancyImpersonate;

use Illuminate\Support\ServiceProvider;

class MultitenancyImpersonateServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('multitenancy-impersonate.php'),
            ], 'config');

            if (! class_exists('CreateMultitenancyImpersonateTables')) {
                $this->publishes([
                    __DIR__.'/../database/migrations/create_multitenancy_impersonate_tables.php.stub' => database_path('migrations/'.date('Y_m_d_His', time()).'_create_multitenancy_impersonate_tables.php'),

                ], 'migrations');
            }
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'multitenancy-impersonate');
    }
}

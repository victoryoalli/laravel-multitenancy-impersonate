<?php

namespace VictorYoalli\MultitenancyImpersonate\Tests;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Orchestra\Testbench\TestCase as Orchestra;
use VictorYoalli\MultitenancyImpersonate\MultitenancyImpersonateServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
    }

    protected function getPackageProviders($app): array
    {
        return [
            MultitenancyImpersonateServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('database.connections.tenant', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('multitenancy-impersonate.ttl', 60);
        $app['config']->set('multitenancy-impersonate.redirect_path', '/home');
        $app['config']->set('multitenancy-impersonate.auth_guard', 'web');
        $app['config']->set('multitenancy-impersonate.rate_limit.max_attempts', 5);
        $app['config']->set('multitenancy-impersonate.rate_limit.decay_minutes', 1);
    }

    protected function setUpDatabase(): void
    {
        $this->app['db']->connection()->getSchemaBuilder()->create('impersonate_tokens', function ($table) {
            $table->bigIncrements('id');
            $table->string('token')->unique();
            $table->unsignedBigInteger('impersonator_id')->index();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('redirect_url')->nullable();
            $table->dateTime('expired_at')->nullable()->index();
            $table->string('auth_guard')->nullable();
            $table->dateTime('impersonated_at')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });

        $this->app['db']->connection()->getSchemaBuilder()->create('users', function ($table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();
        });
    }

    protected function createUser(array $attributes = []): Authenticatable
    {
        return TestUser::create(array_merge([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ], $attributes));
    }
}

class TestUser extends Authenticatable
{
    protected $table = 'users';
    protected $guarded = [];
}

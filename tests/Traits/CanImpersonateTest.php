<?php

namespace VictorYoalli\MultitenancyImpersonate\Tests\Traits;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use VictorYoalli\MultitenancyImpersonate\Exceptions\TooManyAttemptsException;
use VictorYoalli\MultitenancyImpersonate\Tests\Models\TestableImpersonateToken;
use VictorYoalli\MultitenancyImpersonate\Tests\TestCase;

class CanImpersonateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::clear('impersonate:127.0.0.1');
    }

    protected function impersonate(string $token, Authenticatable $user)
    {
        $this->checkRateLimit();

        $impersonate = TestableImpersonateToken::live()->whereToken($token)->firstOrFail();

        auth($impersonate->auth_guard)->login($user);
        $impersonate->markAsUsed();

        Log::info('User impersonated', [
            'impersonator_id' => $impersonate->impersonator_id,
            'user_id' => $user->id,
            'ip_address' => request()->ip(),
        ]);

        return redirect($impersonate->redirect_url);
    }

    protected function checkRateLimit(): void
    {
        $key = 'impersonate:' . request()->ip();
        $maxAttempts = config('multitenancy-impersonate.rate_limit.max_attempts', 5);
        $decayMinutes = config('multitenancy-impersonate.rate_limit.decay_minutes', 1);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);

            Log::warning('Too many impersonation attempts', [
                'ip_address' => request()->ip(),
                'retry_after' => $seconds,
            ]);

            throw new TooManyAttemptsException(
                "Too many impersonation attempts. Please try again in {$seconds} seconds."
            );
        }

        RateLimiter::hit($key, $decayMinutes * 60);
    }

    #[Test]
    public function it_can_impersonate_with_valid_token(): void
    {
        $user = $this->createUser();

        $token = TestableImpersonateToken::create([
            'token' => Str::uuid(),
            'impersonator_id' => 999,
            'redirect_url' => 'https://example.com/dashboard',
            'expired_at' => now()->addMinutes(5),
            'auth_guard' => 'web',
        ]);

        $response = $this->impersonate($token->token, $user);

        $this->assertTrue(auth()->check());
        $this->assertEquals($user->id, auth()->id());
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertStringContainsString('example.com/dashboard', $response->getTargetUrl());
    }

    #[Test]
    public function it_marks_token_as_used_after_impersonation(): void
    {
        $user = $this->createUser();

        $token = TestableImpersonateToken::create([
            'token' => Str::uuid(),
            'impersonator_id' => 999,
            'redirect_url' => 'https://example.com/dashboard',
            'expired_at' => now()->addMinutes(5),
            'auth_guard' => 'web',
        ]);

        $this->assertNull($token->impersonated_at);

        $this->impersonate($token->token, $user);

        $token->refresh();
        $this->assertNotNull($token->impersonated_at);
        $this->assertEquals($user->id, $token->user_id);
    }

    #[Test]
    public function it_fails_with_expired_token(): void
    {
        $user = $this->createUser();

        TestableImpersonateToken::create([
            'token' => 'expired-token',
            'impersonator_id' => 999,
            'redirect_url' => 'https://example.com/dashboard',
            'expired_at' => now()->subMinutes(1),
            'auth_guard' => 'web',
        ]);

        $this->expectException(ModelNotFoundException::class);

        $this->impersonate('expired-token', $user);
    }

    #[Test]
    public function it_fails_with_already_used_token(): void
    {
        $user = $this->createUser();

        TestableImpersonateToken::create([
            'token' => 'used-token',
            'impersonator_id' => 999,
            'redirect_url' => 'https://example.com/dashboard',
            'expired_at' => now()->addMinutes(5),
            'auth_guard' => 'web',
            'impersonated_at' => now(),
        ]);

        $this->expectException(ModelNotFoundException::class);

        $this->impersonate('used-token', $user);
    }

    #[Test]
    public function it_fails_with_invalid_token(): void
    {
        $user = $this->createUser();

        $this->expectException(ModelNotFoundException::class);

        $this->impersonate('invalid-token', $user);
    }

    #[Test]
    public function it_throws_exception_after_too_many_attempts(): void
    {
        $user = $this->createUser();

        $this->app['config']->set('multitenancy-impersonate.rate_limit.max_attempts', 3);

        for ($i = 0; $i < 3; $i++) {
            try {
                $this->impersonate('invalid-token-' . $i, $user);
            } catch (ModelNotFoundException $e) {
                // Expected for invalid tokens
            }
        }

        $this->expectException(TooManyAttemptsException::class);

        $this->impersonate('another-invalid-token', $user);
    }

    #[Test]
    public function it_respects_custom_auth_guard(): void
    {
        $this->app['config']->set('auth.guards.custom', [
            'driver' => 'session',
            'provider' => 'users',
        ]);

        $user = $this->createUser();

        $token = TestableImpersonateToken::create([
            'token' => Str::uuid(),
            'impersonator_id' => 999,
            'redirect_url' => 'https://example.com/dashboard',
            'expired_at' => now()->addMinutes(5),
            'auth_guard' => 'custom',
        ]);

        $this->impersonate($token->token, $user);

        $this->assertTrue(auth('custom')->check());
        $this->assertEquals($user->id, auth('custom')->id());
    }
}

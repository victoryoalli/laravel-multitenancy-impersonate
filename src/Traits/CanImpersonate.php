<?php

namespace VictorYoalli\MultitenancyImpersonate\Traits;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Spatie\Multitenancy\Models\Tenant;
use VictorYoalli\MultitenancyImpersonate\Exceptions\TooManyAttemptsException;
use VictorYoalli\MultitenancyImpersonate\Models\ImpersonateToken;

trait CanImpersonate
{
    public function createToken(Tenant $tenant, Authenticatable $user, ?string $redirect_url = null, ?string $auth_guard = null): ImpersonateToken
    {
        $tenant->makeCurrent();

        $redirect_url = $redirect_url ?? "https://{$tenant->domain}".config('multitenancy-impersonate.redirect_path', '/home');
        $auth_guard = $auth_guard ?? config('multitenancy-impersonate.auth_guard', 'web');

        $token = ImpersonateToken::create([
            'token' => Str::uuid(),
            'impersonator_id' => $user->id,
            'redirect_url' => $redirect_url,
            'expired_at' => now()->addSeconds(config('multitenancy-impersonate.ttl', 60)),
            'auth_guard' => $auth_guard,
        ]);

        $tenant->forgetCurrent();

        Log::info('Impersonation token created', [
            'tenant_id' => $tenant->id,
            'impersonator_id' => $user->id,
            'token_id' => $token->id,
        ]);

        return $token;
    }

    public function impersonate(string $token, Authenticatable $user)
    {
        $this->checkRateLimit();

        $impersonate = ImpersonateToken::live()->whereToken($token)->firstOrFail();

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
}

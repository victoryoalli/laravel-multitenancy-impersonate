<?php

namespace VictorYoalli\MultitenancyImpersonate\Traits;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;
use Spatie\Multitenancy\Models\Tenant;
use VictorYoalli\MultitenancyImpersonate\Models\ImpersonateToken;

/**
 *
 */
trait CanImpersonate
{
    public function createToken(Tenant $tenant, Authenticatable $user, $redirect_url = null, $auth_guard = null)
    {
        $tenant->makeCurrent();

        $redirect_url = $redirect_url ?? "https://{$tenant->domain}".config('multitenancy-impersonate.redirect_path', '/home');
        $auth_guard = $auth_guard ?? config('multitenancy-impersonate.auth_guard', 'web');

        $token = ImpersonateToken::create([
            'token' => Str::uuid(),
            'impersonator_id' => $user->id,
            'redirect_url' => $redirect_url,
            'expired_at' => now()->addSeconds(config('multitenancy-impersonate.ttl', 1)),
            'auth_guard' => $auth_guard,
        ]);
        $tenant->forgetCurrent();

        return $token;
    }

    public function impersonate(string $token, Authenticatable $user)
    {
        $impersonate = ImpersonateToken::live()->whereToken($token)->firstOrFail();
        auth($impersonate->auth_guard)->login($user);
        $impersonate->touch();

        return redirect($impersonate->redirect_url, 301);
    }
}

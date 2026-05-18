---
name: laravel-multitenancy-impersonate-development
description: Build and work with VictorYoalli Laravel Multitenancy Impersonate features — generate and consume secure landlord→tenant impersonation tokens, handle rate limiting, and configure TTLs and auth guards.
---

# Laravel Multitenancy Impersonate Development

## When to use this skill

Use this skill when working with user impersonation across tenants, logging in as a tenant user from a landlord application, or building controllers that use `CanImpersonate`, `ImpersonateToken`, or cross-domain authentication flows with `spatie/laravel-multitenancy`.

## Core Concepts

- **Landlord creates, tenant consumes.** The landlord generates a short-lived UUID token; the tenant validates it and logs in the target user.
- **Token table lives in the tenant database.** `ImpersonateToken` uses `Spatie\Multitenancy\Models\Concerns\UsesTenantConnection` — the `impersonate_tokens` table is in each tenant's DB, not the landlord's.
- **`createToken` manages tenant context automatically.** It calls `$tenant->makeCurrent()` and `$tenant->forgetCurrent()` internally — you don't need to switch the connection yourself.
- **Tokens are single-use and time-limited.** A token becomes invalid once consumed (`impersonated_at` is set) or once its TTL expires (`expired_at`).
- **Rate limiting is per-IP.** The `impersonate()` method throttles by `request()->ip()`, not by user or token.

## Setup

```bash
composer require victoryoalli/laravel-multitenancy-impersonate
```

Publish config and migrations:

```bash
php artisan vendor:publish --provider="VictorYoalli\MultitenancyImpersonate\MultitenancyImpersonateServiceProvider"
```

Run migrations **on each tenant database** (not the landlord):

```bash
php artisan migrate
```

## Using the Trait

Add `CanImpersonate` to any class that needs to create or consume tokens — typically a controller on each side:

```php
use VictorYoalli\MultitenancyImpersonate\Traits\CanImpersonate;

class ImpersonateController
{
    use CanImpersonate;
}
```

## Creating Tokens (Landlord)

```php
use VictorYoalli\MultitenancyImpersonate\Traits\CanImpersonate;

class ImpersonateController
{
    use CanImpersonate;

    public function store(Request $request)
    {
        $tenant = Tenant::findOrFail($request->get('tenant_id'));

        // Custom redirect URL on the tenant side (optional)
        $redirect_url = "https://{$tenant->domain}/admin/dashboard";

        // Custom auth guard (optional, defaults to config 'auth_guard')
        $auth_guard = 'web';

        $impersonate = $this->createToken($tenant, auth()->user(), $redirect_url, $auth_guard);

        return redirect("https://{$tenant->domain}/impersonate/{$impersonate->token}");
    }
}
```

`createToken` signature:

```php
createToken(
    Tenant $tenant,
    Authenticatable $user,
    ?string $redirect_url = null,  // defaults to "https://{tenant->domain}" + config redirect_path
    ?string $auth_guard = null      // defaults to config auth_guard
): ImpersonateToken
```

## Consuming Tokens (Tenant)

```php
use VictorYoalli\MultitenancyImpersonate\Traits\CanImpersonate;
use VictorYoalli\MultitenancyImpersonate\Exceptions\TooManyAttemptsException;

class TenantImpersonateController
{
    use CanImpersonate;

    public function __invoke(Request $request, string $token)
    {
        try {
            $user = User::findOrFail($request->get('user_id'));

            return $this->impersonate($token, $user);
            // Returns a RedirectResponse to the redirect_url stored with the token
        } catch (TooManyAttemptsException $e) {
            abort(429, 'Too many impersonation attempts.');
        }
    }
}
```

`impersonate` signature:

```php
impersonate(string $token, Authenticatable $user): RedirectResponse
```

It validates the token, logs in `$user` via the stored `auth_guard`, marks the token as used, and redirects to the stored `redirect_url`.

## Routes Example

```php
// landlord/routes/web.php — protected by admin/superadmin middleware
Route::post('/impersonate', [ImpersonateController::class, 'store'])
    ->middleware(['auth', 'can:impersonate-users']);

// tenant/routes/web.php — public endpoint, protected only by the built-in rate limiter
Route::get('/impersonate/{token}', TenantImpersonateController::class);
```

## Configuration

After publishing, edit `config/multitenancy-impersonate.php`:

```php
return [
    'ttl' => 60,                // Token lifetime in seconds (default: 60)
    'redirect_path' => '/home', // Appended to tenant domain when redirect_url is null (default: '/home')
    'auth_guard' => 'web',      // Auth guard used to log in the tenant user (default: 'web')
    'rate_limit' => [
        'max_attempts' => 5,    // Max validation attempts per IP before throttling (default: 5)
        'decay_minutes' => 1,   // Minutes until the attempt counter resets (default: 1)
    ],
];
```

## Rate Limiting & Exception Handling

The `impersonate()` method uses Laravel's `RateLimiter` internally, keyed on `'impersonate:' . request()->ip()`. When the limit is exceeded it throws:

```php
VictorYoalli\MultitenancyImpersonate\Exceptions\TooManyAttemptsException
```

Handle it in a route exception handler or wrap the call in a try/catch:

```php
use VictorYoalli\MultitenancyImpersonate\Exceptions\TooManyAttemptsException;

// In app/Exceptions/Handler.php (Laravel 10) or bootstrap/app.php (Laravel 11/12)
$exceptions->render(function (TooManyAttemptsException $e, Request $request) {
    return response()->json(['message' => 'Too many attempts.'], 429);
});
```

## Token Lifecycle

```php
use VictorYoalli\MultitenancyImpersonate\Models\ImpersonateToken;

// Scope: only non-expired, non-consumed tokens
ImpersonateToken::live()->where('token', $uuid)->first();

// After a successful impersonate() call, the token is automatically marked used:
// $token->markAsUsed() sets:
//   impersonated_at = now()
//   ip_address      = request()->ip()
//   user_id         = auth()->id()   ← the authenticated user at consumption time
```

## Security Considerations

- **Keep TTL short.** The default 60 seconds is intentional — tokens travel in a URL. Only increase it if your redirect chain requires more time.
- **`user_id` on the token is not the `$user` you passed.** `markAsUsed()` records `auth()->id()` at the moment of consumption. If no user is authenticated yet, this will be `null`. Use it only for audit logs.
- **Authorize `createToken` on the landlord side.** The package does not enforce who can call `createToken` — add a gate, policy, or middleware to restrict it to superadmins.
- **The tenant endpoint must be public** (no auth middleware) — the user is not yet logged in. The built-in rate limiter is the only automatic protection on that route.
- **Always use HTTPS** on both landlord and tenant domains. Tokens in URLs over plain HTTP can be captured in server logs or referrer headers.

## Testing

The package's own tests use Orchestra Testbench with an in-memory SQLite database. When writing your own feature tests for impersonation flows:

- **Use a test model without `UsesTenantConnection`** — similar to `tests/Models/TestableImpersonateToken.php` in the package source. This lets you test against the default test connection instead of a real tenant DB.
- **Mock or swap the Tenant** — call `$tenant->makeCurrent()` in test setup to simulate the tenant context before asserting against `ImpersonateToken`.

```php
// Example: verify token is invalidated after use
$token = ImpersonateToken::create([...]);
$this->assertNull($token->impersonated_at);

$this->impersonate($token->token, $user);

$this->assertNotNull($token->fresh()->impersonated_at);
```

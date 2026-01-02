# Laravel Multitenancy Impersonate

Laravel multitenancy impersonation from landlord to tenant.

This package is made to be used with [Spatie Laravel Multitenancy](https://github.com/spatie/laravel-multitenancy).

[![Latest Version on Packagist](https://img.shields.io/packagist/v/victoryoalli/laravel-multitenancy-impersonate.svg?style=flat-square)](https://packagist.org/packages/victoryoalli/laravel-multitenancy-impersonate)
[![Tests](https://github.com/victoryoalli/laravel-multitenancy-impersonate/actions/workflows/tests.yml/badge.svg)](https://github.com/victoryoalli/laravel-multitenancy-impersonate/actions/workflows/tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/victoryoalli/laravel-multitenancy-impersonate.svg?style=flat-square)](https://packagist.org/packages/victoryoalli/laravel-multitenancy-impersonate)

This package allows you to impersonate users from a landlord (main) application into tenant applications. It generates secure, time-limited tokens that enable seamless authentication across tenant databases.

## Requirements

- PHP ^8.2
- Laravel ^10.0, ^11.0 or ^12.0
- [Spatie Laravel Multitenancy](https://github.com/spatie/laravel-multitenancy) ^3.0 or ^4.0

## Installation

You can install the package via composer:

```bash
composer require victoryoalli/laravel-multitenancy-impersonate
```
## Publish Config and Migrations
```bash
php artisan vendor:publish --provider="VictorYoalli\MultitenancyImpersonate\MultitenancyImpersonateServiceProvider"
```

## Configuration

After publishing, you can modify `config/multitenancy-impersonate.php`:

```php
return [
    'ttl' => 60,                // Token lifetime in seconds
    'redirect_path' => '/home', // Default redirect after impersonation
    'auth_guard' => 'web',      // Authentication guard to use
    'rate_limit' => [
        'max_attempts' => 5,    // Max token validation attempts
        'decay_minutes' => 1,   // Minutes until attempts reset
    ],
];
```

## Usage

### Landlord Controller
The Landlord controller creates the token and redirects to the tenant for automatic login.
```php
use VictorYoalli\MultitenancyImpersonate\Traits\CanImpersonate;

class ImpersonateController
{
    use CanImpersonate;

    public function store(Request $request)
    {
        $tenant = Tenant::find($request->get('tenant_id'));
        $redirect_url = "https://{$tenant->domain}/admin";

        // Create impersonation token in tenant's database
        $impersonate = $this->createToken($tenant, auth()->user(), $redirect_url);

        // Redirect to tenant's impersonation endpoint
        $tenant_url = "https://{$tenant->domain}/impersonate";

        return redirect("{$tenant_url}/{$impersonate->token}");
    }
}
```

### Tenant Controller
Validates the token and logs in the specified user. The user will be redirected to the `$redirect_url` provided when creating the token.
```php
use VictorYoalli\MultitenancyImpersonate\Traits\CanImpersonate;

class TenantImpersonateController
{
    use CanImpersonate;

    public function __invoke(Request $request, string $token)
    {
        $user = User::firstOrFail(); // Or find user by any criteria

        return $this->impersonate($token, $user);
    }
}
```

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email victoryoalli@gmail.com instead of using the issue tracker.

## Credits

- [Victor Yoalli](https://github.com/victoryoalli)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

# Laravel Multitenancy Impersonate

The intented functionality is to be able to impersonate a user of any tenant from a landlord instance.

This package is made to be used with [Spatie Laravel Multitenancy](https://github.com/spatie/laravel-multitenancy).

[![Latest Version on Packagist](https://img.shields.io/packagist/v/victoryoalli/multitenancy-impersonate.svg?style=flat-square)](https://packagist.org/packages/victoryoalli/multitenancy-impersonate)
[![Build Status](https://img.shields.io/travis/victoryoalli/multitenancy-impersonate/master.svg?style=flat-square)](https://travis-ci.org/victoryoalli/multitenancy-impersonate)
[![Quality Score](https://img.shields.io/scrutinizer/g/victoryoalli/multitenancy-impersonate.svg?style=flat-square)](https://scrutinizer-ci.com/g/victoryoalli/multitenancy-impersonate)
[![Total Downloads](https://img.shields.io/packagist/dt/victoryoalli/multitenancy-impersonate.svg?style=flat-square)](https://packagist.org/packages/victoryoalli/multitenancy-impersonate)

This is where your description should go. Try and limit it to a paragraph or two, and maybe throw in a mention of what PSRs you support to avoid any confusion with users and contributors.

## Installation

You can install the package via composer:

```bash
composer require victoryoalli/multitenancy-impersonate
```
## Publish Config and Migrations
```bash
php artisan vendor:publish
```

## Usage

### Landlord Controller
The Landlord controller creates the token and redirects to the tenant for automatic login.
``` php

use VictorYoalli\MultitenancyImpersonate\Traits\CanImpersonate;

class ImpersonateController
{
    use CanImpersonate;

    public function store(Request $request)
    {
        $tenant = Tenant::find($request->get('tenant_id'));
        $redirect_url = "https{$tenant->domain}/admin";
        $impersonate = $this->impersonate($tenant,auth()->user(),$redirect_url)

        $tenant_url = "https{$tenant->domain}/admin/impersonate";

        return redirect("{$tenant_url}/{$impersonate->token}");
    }

}
```

### Impersonate Tenant Controller
Impersonates to the user of your choice. Needs a valid token and the user to be impersonated.
Will be redirected to the provided `$redirect_url`.
```php
use CanImpersonate;

public function __invoke(Request $request, string $token)
    {
        $user = User::firstOrFail();

        return $this->impersonate($token, $user);
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

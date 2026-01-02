# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Laravel Multitenancy Impersonate is a package that enables user impersonation from a landlord application to tenant applications. It's designed to work with [Spatie Laravel Multitenancy](https://github.com/spatie/laravel-multitenancy).

## Commands

```bash
# Run tests
composer test

# Run tests with coverage
composer test-coverage

# Code formatting
composer format
```

Run a single test:
```bash
vendor/bin/phpunit tests/Models/ImpersonateTokenTest.php
vendor/bin/phpunit --filter testMethodName
```

## CI/CD

GitHub Actions runs tests on every push to `main` and on pull requests:
- PHP versions: 8.1, 8.2, 8.3
- Laravel versions: 10.x, 11.x (Laravel 11 requires PHP 8.2+)

Workflow file: `.github/workflows/tests.yml`

## Architecture

### Core Components

- **`CanImpersonate` trait** (`src/Traits/CanImpersonate.php`): Provides two methods:
  - `createToken()`: Called from landlord to create an impersonation token in the tenant database
  - `impersonate()`: Called from tenant to validate token and log in user

- **`ImpersonateToken` model** (`src/Models/ImpersonateToken.php`): Stores impersonation tokens in tenant database using `UsesTenantConnection`. Has a `live` scope for valid (non-expired, unused) tokens.

- **`TooManyAttemptsException`** (`src/Exceptions/TooManyAttemptsException.php`): Thrown when rate limit is exceeded.

- **Service Provider** (`src/MultitenancyImpersonateServiceProvider.php`): Publishes config and migrations.

### Impersonation Flow

1. Landlord controller uses `createToken($tenant, $user, $redirect_url)` to generate a UUID token in tenant's database
2. Landlord redirects to tenant URL with token
3. Tenant controller uses `impersonate($token, $user)` to validate token and authenticate user
4. Token is marked as used (via `markAsUsed()`) with timestamp, IP, and user_id

### Configuration (`config/config.php`)

- `ttl`: Token lifetime in seconds (default: 60)
- `redirect_path`: Default redirect path after impersonation (default: '/home')
- `auth_guard`: Authentication guard to use (default: 'web')
- `rate_limit.max_attempts`: Max token validation attempts (default: 5)
- `rate_limit.decay_minutes`: Minutes until attempts reset (default: 1)

## Testing

Tests use Orchestra Testbench with an in-memory SQLite database. The base `TestCase` class automatically runs migrations.

- `tests/Models/ImpersonateTokenTest.php`: Tests for the token model and scopes
- `tests/Traits/CanImpersonateTest.php`: Tests for impersonation logic and rate limiting
- `tests/Models/TestableImpersonateToken.php`: Test model without `UsesTenantConnection` trait

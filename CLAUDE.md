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

# Static analysis
composer psalm

# Code formatting
composer format
```

Run a single test:
```bash
vendor/bin/phpunit tests/Models/ImpersonateTokenTest.php
vendor/bin/phpunit --filter testMethodName
```

## Architecture

### Core Components

- **`CanImpersonate` trait** (`src/Traits/CanImpersonate.php`): Provides two methods:
  - `createToken()`: Called from landlord to create an impersonation token in the tenant database
  - `impersonate()`: Called from tenant to validate token and log in user

- **`ImpersonateToken` model** (`src/Models/ImpersonateToken.php`): Stores impersonation tokens in tenant database using `UsesTenantConnection`. Has a `live` scope for valid (non-expired, unused) tokens.

- **Service Provider** (`src/MultitenancyImpersonateServiceProvider.php`): Publishes config and migrations.

### Impersonation Flow

1. Landlord controller uses `createToken($tenant, $user, $redirect_url)` to generate a UUID token in tenant's database
2. Landlord redirects to tenant URL with token
3. Tenant controller uses `impersonate($token, $user)` to validate token and authenticate user
4. Token is marked as used (via `touch()`) with timestamp, IP, and user_id

### Configuration (`config/config.php`)

- `ttl`: Token lifetime in seconds (default: 1)
- `redirect_path`: Default redirect path after impersonation (default: '/home')
- `auth_guard`: Authentication guard to use (default: 'web')

## Testing

Tests use Orchestra Testbench with an in-memory SQLite database. The base `TestCase` class automatically runs migrations and loads factories.

# Changelog

All notable changes to `laravel-multitenancy-impersonate` will be documented in this file.

## 2.3.0 - 2026-05-17

### Added
- Laravel Boost skill (`resources/boost/skills/laravel-multitenancy-impersonate-development/SKILL.md`) for AI-assisted development — provides Claude and compatible editors with accurate guidance on `CanImpersonate`, `ImpersonateToken`, config keys, rate limiting, security considerations, and testing patterns

## 2.2.0 - 2026-03-13

### Added
- Support for `spatie/laravel-multitenancy` v2 (`^2.0`)
- Support for Laravel 8.x and 9.x
- Support for PHP 8.0 and 8.1
- Expanded CI matrix covering PHP 8.0–8.4 with Laravel 8–12

### Changed
- Lowered minimum PHP requirement from `^8.2` to `^8.0`
- Extended `illuminate/*` package constraints to include `^8.0|^9.0`

## 2.1.0 - 2026-01-02

### Added
- Laravel 12 support
- PHP 8.4 support in CI

### Changed
- Minimum PHP version updated to 8.2 (required by Laravel 12)
- Updated orchestra/testbench to ^10.0 for Laravel 12

## 2.0.0 - 2026-01-02

### Breaking Changes
- Minimum PHP version is now 8.1
- Minimum Laravel version is now 10.0
- Minimum Spatie Multitenancy version is now 3.0
- `touch()` method renamed to `markAsUsed()` for Laravel 10/11 compatibility

### Added
- Rate limiting to prevent brute force attacks (configurable max attempts and decay time)
- Logging for token creation and impersonation events
- `TooManyAttemptsException` for rate limit handling
- Proper date casting for `expired_at` and `impersonated_at`
- Unique index on `token` column
- Indexes on `impersonator_id` and `expired_at` for query optimization
- Comprehensive test coverage (12 tests)
- Configuration options for rate limiting

### Fixed
- Redirect code changed from 301 (permanent) to 302 (temporary)
- Token TTL increased from 1 second to 60 seconds
- Fixed incorrect comments in config file

### Changed
- Updated dependencies: PHP ^8.1, Laravel ^10.0|^11.0, Spatie Multitenancy ^3.0|^4.0
- Updated PHPUnit to v10/11 with modern `#[Test]` attributes
- Improved README documentation with correct usage examples

## 1.0.0 - 2020-05-01

- Initial release

# Changelog

All notable changes to `laravel-multitenancy-impersonate` will be documented in this file.

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

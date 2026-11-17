# Changelog

## [1.0.1] - 2026-11-17

### Fixed
- Removed the duplicate manual invocation of `defineDatabaseMigrations()` from the test case setup. Orchestra Testbench already invokes this hook, so the extra call attempted to recreate the `users` table and caused all feature tests to error.

## [1.0.0] - 2026-11-17

### Added
- Polymorphic referral-code ownership.
- Referral attribution to polymorphic referrers and referees.
- Unique code generation and active-code validation.
- Self-referral and duplicate referee protection.
- Pending, qualified, rewarded, and rejected lifecycle states.
- Pluggable reward issuer contract for integration with loyalty and gift-card systems.
- PHPUnit feature tests and GitHub Actions workflow.

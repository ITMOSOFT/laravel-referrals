# Release checklist

1. Review the source and run the full test suite.
2. Confirm the GitHub Actions matrix passes for supported PHP and Laravel versions.
3. Commit and push the package source to `main`.
4. Create and push the annotated `v1.0.0` tag.
5. Create the GitHub Release using the title and notes below.
6. Submit `itmosoft/laravel-referrals` to Packagist or confirm the Packagist GitHub hook is active.

## Git commands (run locally)

```bash
git add .
git status
git commit -m "feat: add Laravel referrals package"
git push origin main

git tag -a v1.0.0 -m "Release v1.0.0"
git push origin v1.0.0
```

## GitHub Release

Title: `v1.0.0 - Initial Release`

```markdown
## Features
- Polymorphic referral codes for users or other Eloquent models.
- Referral attribution with self-referral and duplicate-referee protection.
- Referral lifecycle: pending, qualified, rewarded, and rejected.
- Unique referral code generation and active-code validation.
- Pluggable reward issuer contract for integration with loyalty and gift-card systems.
- Configurable table prefix and model classes.

## Compatibility
- PHP 8.3+
- Laravel 12 and 13

## Installation
composer require itmosoft/laravel-referrals
```

## Packagist

Package name: `itmosoft/laravel-referrals`

After the GitHub tag is published, verify Packagist detects `v1.0.0`. If this is the first release, submit the GitHub repository URL at https://packagist.org/packages/submit.

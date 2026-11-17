# Release checklist

1. Run `composer test` and confirm all tests pass.
2. Confirm the GitHub Actions matrix passes for supported PHP and Laravel versions.
3. Commit and push the package source to `main`.
4. Create and push the annotated `v1.0.1` tag.
5. Create the GitHub Release using the title and notes below.
6. Confirm Packagist detects the new tag.

## Git commands (run locally)

```bash
git add .
git status
git commit -m "fix: avoid duplicate Testbench migration setup"
git push origin main

git tag -a v1.0.1 -m "Release v1.0.1"
git push origin v1.0.1
```

## GitHub Release

Title: `v1.0.1 - Fix Duplicate Testbench Migration Setup`

```markdown
## Bug Fixes
- Removed the duplicate manual call to `defineDatabaseMigrations()` from the package test case setup.
- Orchestra Testbench already invokes this migration hook; calling it again attempted to recreate the `users` table and caused all 10 feature tests to fail before exercising package behavior.

## Compatibility
- PHP 8.3+
- Laravel 12 and 13

## Upgrade
Update the package to `^1.0.1`:

composer update itmosoft/laravel-referrals
```

No application database migration changes are required for this test-only fix.

## Packagist

Verify Packagist detects `v1.0.1` after the GitHub tag is pushed.

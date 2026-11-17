# ITMOSOFT Laravel Referrals

Referral codes, attribution, qualification, and reward integration for Laravel applications.

## Requirements

- PHP 8.3+
- Laravel 12 or 13

## Installation

```bash
composer require itmosoft/laravel-referrals
php artisan vendor:publish --tag=itmosoft-referrals-config
php artisan vendor:publish --tag=itmosoft-referrals-migrations
php artisan migrate
```

Laravel package auto-discovery registers the service provider.

## Basic usage

```php
use ItmoSoft\Referrals\ReferralManager;

// Create a referral code for any Eloquent model.
$code = app(ReferralManager::class)->createCode($user);

// Attribute a new user to the referral code.
$referral = app(ReferralManager::class)->record($code->code, $newUser);

// Call this after your own business rules determine that the referral qualifies.
$referral = app(ReferralManager::class)->qualify($referral);
```

## Reward integration

This package deliberately does not own points, wallet balances, gift-card balances, or payment instruments. Implement `ReferralRewardIssuer` and connect it to your reward system.

```php
namespace App\Referrals;

use ItmoSoft\Referrals\Contracts\ReferralRewardIssuer;
use ItmoSoft\Referrals\Models\Referral;

class AppReferralRewardIssuer implements ReferralRewardIssuer
{
    public function issue(Referral $referral): void
    {
        // Integrate with laravel-loyalty, laravel-gift-cards,
        // or your application's own reward service.
        // Make this operation idempotent using the referral ID.
    }
}
```

Bind the implementation in a service provider:

```php
use App\Referrals\AppReferralRewardIssuer;
use ItmoSoft\Referrals\Contracts\ReferralRewardIssuer;

$this->app->bind(ReferralRewardIssuer::class, AppReferralRewardIssuer::class);
```

Then issue rewards after qualification:

```php
$referral = app(ReferralManager::class)->issueRewards(
    $referral,
    app(ReferralRewardIssuer::class)
);
```

`issueRewards()` marks a referral as rewarded only after the issuer returns successfully. The issuer should be idempotent because external side effects cannot be rolled back by a database transaction.

## Lifecycle

- `pending`: recorded and awaiting qualification.
- `qualified`: your application has confirmed the referral meets its rules.
- `rewarded`: the reward issuer completed successfully.
- `rejected`: the referral was rejected while pending.

Only pending referrals can be rejected. Only qualified referrals can be rewarded.

## Models and tables

- `ReferralCode`: polymorphic code owner, unique code, active flag, metadata.
- `Referral`: referral code, polymorphic referrer/referee, lifecycle timestamps, metadata.
- Default tables: `itmosoft_referral_codes` and `itmosoft_referrals`.

The table prefix is configurable through `itmosoft-referrals.table_prefix`.

## Important implementation notes

- Self-referrals are rejected.
- A referee can only be attributed once, enforced by application validation and a database unique index.
- Referral code matching is case-insensitive at the manager boundary by normalizing input to uppercase.
- Qualification rules, fraud detection, reward amounts, expiration, and campaign-specific policies belong to the host application.
- For high-volume concurrent referral attribution, handle unique-index violations as duplicate attribution at the application boundary.

## Tests

```bash
composer install
composer test
```

## Versioning

This package follows semantic versioning. See [CHANGELOG.md](CHANGELOG.md).

## License

MIT. See [LICENSE](LICENSE).

<?php

namespace ItmoSoft\Referrals\Tests\Feature;

use Illuminate\Database\Eloquent\Model;
use ItmoSoft\Referrals\Contracts\ReferralRewardIssuer;
use ItmoSoft\Referrals\Enums\ReferralStatus;
use ItmoSoft\Referrals\Exceptions\ReferralException;
use ItmoSoft\Referrals\Models\Referral;
use ItmoSoft\Referrals\ReferralManager;
use ItmoSoft\Referrals\Tests\TestCase;

class User extends Model
{
    protected $guarded = [];
}

class FakeRewardIssuer implements ReferralRewardIssuer
{
    public int $calls = 0;

    public function issue(Referral $referral): void
    {
        $this->calls++;
    }
}

class ReferralManagerTest extends TestCase
{
    private ReferralManager $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = app(ReferralManager::class);
    }

    private function user(string $name): User
    {
        return User::query()->create(['name' => $name]);
    }

    public function test_it_creates_a_referral_code_for_an_owner(): void
    {
        $owner = $this->user('Owner');

        $code = $this->manager->createCode($owner, 'OWNER2026', ['channel' => 'web']);

        $this->assertSame('OWNER2026', $code->code);
        $this->assertTrue($code->is_active);
        $this->assertSame('web', $code->metadata['channel']);
        $this->assertSame($owner->getKey(), $code->owner->getKey());
    }

    public function test_it_generates_a_unique_code_when_not_supplied(): void
    {
        $code = $this->manager->createCode($this->user('Owner'));

        $this->assertNotEmpty($code->code);
        $this->assertSame(10, strlen($code->code));
    }

    public function test_it_records_a_referral_and_attributes_the_owner(): void
    {
        $owner = $this->user('Owner');
        $referee = $this->user('New user');
        $this->manager->createCode($owner, 'INVITE123');

        $referral = $this->manager->record('invite123', $referee, ['campaign' => 'summer']);

        $this->assertSame(ReferralStatus::Pending, $referral->status);
        $this->assertSame($owner->getKey(), $referral->referrer->getKey());
        $this->assertSame($referee->getKey(), $referral->referee->getKey());
        $this->assertSame('summer', $referral->metadata['campaign']);
    }

    public function test_it_rejects_invalid_codes(): void
    {
        $this->expectException(ReferralException::class);
        $this->manager->record('UNKNOWN', $this->user('New user'));
    }

    public function test_it_prevents_self_referrals(): void
    {
        $owner = $this->user('Owner');
        $this->manager->createCode($owner, 'SELF123');

        $this->expectException(ReferralException::class);
        $this->manager->record('SELF123', $owner);
    }

    public function test_it_prevents_duplicate_referee_attribution(): void
    {
        $owner = $this->user('Owner');
        $referee = $this->user('New user');
        $this->manager->createCode($owner, 'CODE123');
        $this->manager->record('CODE123', $referee);

        $this->expectException(ReferralException::class);
        $this->manager->record('CODE123', $referee);
    }

    public function test_it_qualifies_a_pending_referral(): void
    {
        $owner = $this->user('Owner');
        $referee = $this->user('New user');
        $code = $this->manager->createCode($owner, 'QUALIFY');
        $referral = $this->manager->record($code->code, $referee);

        $qualified = $this->manager->qualify($referral);

        $this->assertSame(ReferralStatus::Qualified, $qualified->status);
        $this->assertNotNull($qualified->qualified_at);
    }

    public function test_it_issues_rewards_once_for_a_qualified_referral(): void
    {
        $owner = $this->user('Owner');
        $referee = $this->user('New user');
        $code = $this->manager->createCode($owner, 'REWARD');
        $referral = $this->manager->qualify(
            $this->manager->record($code->code, $referee)
        );
        $issuer = new FakeRewardIssuer();

        $rewarded = $this->manager->issueRewards($referral, $issuer);
        $again = $this->manager->issueRewards($rewarded, $issuer);

        $this->assertSame(ReferralStatus::Rewarded, $again->status);
        $this->assertNotNull($again->rewarded_at);
        $this->assertSame(1, $issuer->calls);
    }

    public function test_it_rejects_reward_issuance_before_qualification(): void
    {
        $owner = $this->user('Owner');
        $referee = $this->user('New user');
        $code = $this->manager->createCode($owner, 'PENDING');
        $referral = $this->manager->record($code->code, $referee);

        $this->expectException(ReferralException::class);
        $this->manager->issueRewards($referral, new FakeRewardIssuer());
    }

    public function test_it_rejects_a_qualified_referral_from_being_rejected(): void
    {
        $owner = $this->user('Owner');
        $referee = $this->user('New user');
        $code = $this->manager->createCode($owner, 'NOPE');
        $referral = $this->manager->qualify(
            $this->manager->record($code->code, $referee)
        );

        $this->expectException(ReferralException::class);
        $this->manager->reject($referral);
    }
}

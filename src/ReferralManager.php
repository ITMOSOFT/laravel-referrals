<?php

namespace ItmoSoft\Referrals;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use ItmoSoft\Referrals\Contracts\ReferralRewardIssuer;
use ItmoSoft\Referrals\Enums\ReferralStatus;
use ItmoSoft\Referrals\Exceptions\ReferralException;
use ItmoSoft\Referrals\Models\Referral;
use ItmoSoft\Referrals\Models\ReferralCode;

class ReferralManager
{
    public function createCode(Model $owner, ?string $code = null, array $metadata = []): ReferralCode
    {
        $model = config('itmosoft-referrals.models.code', ReferralCode::class);
        $code ??= $this->generateCode();

        return $model::query()->create([
            'owner_type' => $owner->getMorphClass(),
            'owner_id' => $owner->getKey(),
            'code' => strtoupper(trim($code)),
            'is_active' => true,
            'metadata' => $metadata,
        ]);
    }

    public function record(string $code, Model $referee, array $metadata = []): Referral
    {
        return DB::transaction(function () use ($code, $referee, $metadata): Referral {
            $codeModel = config('itmosoft-referrals.models.code', ReferralCode::class);
            $referralModel = config('itmosoft-referrals.models.referral', Referral::class);

            /** @var ReferralCode|null $referralCode */
            $referralCode = $codeModel::query()
                ->where('code', strtoupper(trim($code)))
                ->where('is_active', true)
                ->lockForUpdate()
                ->first();

            if (! $referralCode) {
                throw new ReferralException('The referral code is invalid or inactive.');
            }

            if ($referralCode->owner_type === $referee->getMorphClass()
                && (string) $referralCode->owner_id === (string) $referee->getKey()) {
                throw new ReferralException('Self-referrals are not allowed.');
            }

            $existing = $referralModel::query()
                ->where('referee_type', $referee->getMorphClass())
                ->where('referee_id', $referee->getKey())
                ->exists();

            if ($existing) {
                throw new ReferralException('This referee has already been attributed.');
            }

            return $referralModel::query()->create([
                'referral_code_id' => $referralCode->getKey(),
                'referrer_type' => $referralCode->owner_type,
                'referrer_id' => $referralCode->owner_id,
                'referee_type' => $referee->getMorphClass(),
                'referee_id' => $referee->getKey(),
                'status' => ReferralStatus::Pending,
                'metadata' => $metadata,
            ]);
        });
    }

    public function qualify(Referral $referral): Referral
    {
        return DB::transaction(function () use ($referral): Referral {
            $locked = $referral->newQuery()
                ->whereKey($referral->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status === ReferralStatus::Rejected) {
                throw new ReferralException('A rejected referral cannot be qualified.');
            }

            if ($locked->status === ReferralStatus::Pending) {
                $locked->forceFill([
                    'status' => ReferralStatus::Qualified,
                    'qualified_at' => now(),
                ])->save();
            }

            return $locked->refresh();
        });
    }

    public function reject(Referral $referral): Referral
    {
        return DB::transaction(function () use ($referral): Referral {
            $locked = $referral->newQuery()
                ->whereKey($referral->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status !== ReferralStatus::Pending) {
                throw new ReferralException('Only pending referrals can be rejected.');
            }

            $locked->forceFill(['status' => ReferralStatus::Rejected])->save();

            return $locked->refresh();
        });
    }

    public function issueRewards(Referral $referral, ReferralRewardIssuer $issuer): Referral
    {
        return DB::transaction(function () use ($referral, $issuer): Referral {
            $locked = $referral->newQuery()
                ->whereKey($referral->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status === ReferralStatus::Rewarded) {
                return $locked;
            }

            if ($locked->status !== ReferralStatus::Qualified) {
                throw new ReferralException('Only qualified referrals can receive rewards.');
            }

            $issuer->issue($locked);

            $locked->forceFill([
                'status' => ReferralStatus::Rewarded,
                'rewarded_at' => now(),
            ])->save();

            return $locked->refresh();
        });
    }

    private function generateCode(): string
    {
        $length = (int) config('itmosoft-referrals.code.length', 10);
        $characters = (string) config(
            'itmosoft-referrals.code.characters',
            'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'
        );

        do {
            $code = '';
            for ($i = 0; $i < $length; $i++) {
                $code .= $characters[random_int(0, strlen($characters) - 1)];
            }
            $exists = config('itmosoft-referrals.models.code', ReferralCode::class)
                ::query()->where('code', $code)->exists();
        } while ($exists);

        return $code;
    }
}

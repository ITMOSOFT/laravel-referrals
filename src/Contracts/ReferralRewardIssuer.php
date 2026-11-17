<?php

namespace ItmoSoft\Referrals\Contracts;

use ItmoSoft\Referrals\Models\Referral;

interface ReferralRewardIssuer
{
    /**
     * Issue the configured referral rewards.
     *
     * Implementations should be idempotent for a given referral ID because
     * a failed request may be retried after an external side effect.
     */
    public function issue(Referral $referral): void;
}

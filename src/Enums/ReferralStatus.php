<?php

namespace ItmoSoft\Referrals\Enums;

enum ReferralStatus: string
{
    case Pending = 'pending';
    case Qualified = 'qualified';
    case Rewarded = 'rewarded';
    case Rejected = 'rejected';
}

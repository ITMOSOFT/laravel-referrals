<?php

return [
    'table_prefix' => 'itmosoft_',

    'models' => [
        'code' => \ItmoSoft\Referrals\Models\ReferralCode::class,
        'referral' => \ItmoSoft\Referrals\Models\Referral::class,
    ],

    'code' => [
        'length' => 10,
        'characters' => 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789',
    ],
];

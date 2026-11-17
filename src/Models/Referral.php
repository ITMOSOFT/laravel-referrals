<?php

namespace ItmoSoft\Referrals\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use ItmoSoft\Referrals\Enums\ReferralStatus;

class Referral extends Model
{
    protected $guarded = [];

    public function getTable(): string
    {
        return config('itmosoft-referrals.table_prefix', 'itmosoft_').'referrals';
    }

    protected function casts(): array
    {
        return [
            'status' => ReferralStatus::class,
            'metadata' => 'array',
            'qualified_at' => 'datetime',
            'rewarded_at' => 'datetime',
        ];
    }

    public function referralCode(): BelongsTo
    {
        return $this->belongsTo(
            config('itmosoft-referrals.models.code'),
            'referral_code_id'
        );
    }

    public function referrer(): MorphTo
    {
        return $this->morphTo();
    }

    public function referee(): MorphTo
    {
        return $this->morphTo();
    }
}

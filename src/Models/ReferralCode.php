<?php

namespace ItmoSoft\Referrals\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReferralCode extends Model
{
    protected $guarded = [];

    public function getTable(): string
    {
        return config('itmosoft-referrals.table_prefix', 'itmosoft_').'referral_codes';
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(
            config('itmosoft-referrals.models.referral'),
            'referral_code_id'
        );
    }
}

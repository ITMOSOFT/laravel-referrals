<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $prefix = config('itmosoft-referrals.table_prefix', 'itmosoft_');
        $codes = $prefix.'referral_codes';
        $referrals = $prefix.'referrals';

        Schema::create($referrals, function (Blueprint $blueprint) use ($codes): void {
            $blueprint->id();
            $blueprint->foreignId('referral_code_id')->constrained($codes)->cascadeOnDelete();
            $blueprint->morphs('referrer');
            $blueprint->morphs('referee');
            $blueprint->string('status', 24)->default('pending')->index();
            $blueprint->timestamp('qualified_at')->nullable();
            $blueprint->timestamp('rewarded_at')->nullable();
            $blueprint->json('metadata')->nullable();
            $blueprint->timestamps();
            $blueprint->unique(['referee_type', 'referee_id'], 'itmosoft_referrals_referee_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            config('itmosoft-referrals.table_prefix', 'itmosoft_').'referrals'
        );
    }
};

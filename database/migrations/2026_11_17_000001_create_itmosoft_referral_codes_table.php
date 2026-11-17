<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $table = config('itmosoft-referrals.table_prefix', 'itmosoft_').'referral_codes';

        Schema::create($table, function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->morphs('owner');
            $blueprint->string('code', 64)->unique();
            $blueprint->boolean('is_active')->default(true)->index();
            $blueprint->json('metadata')->nullable();
            $blueprint->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            config('itmosoft-referrals.table_prefix', 'itmosoft_').'referral_codes'
        );
    }
};

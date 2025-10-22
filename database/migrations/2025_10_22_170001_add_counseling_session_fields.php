<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('counseling_sessions', function (Blueprint $table) {
            $table->json('referral_academic')->nullable();
            $table->string('referral_academic_other')->nullable();
            $table->json('referral_social')->nullable();
            $table->string('referral_social_other')->nullable();
            $table->text('incident_description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('counseling_sessions', function (Blueprint $table) {
            $table->dropColumn([
                'referral_academic',
                'referral_academic_other',
                'referral_social',
                'referral_social_other',
                'incident_description',
            ]);
        });
    }
};

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
        Schema::table('faculty_assignments', function (Blueprint $table) {
            // Add strand and track fields for Senior High School assignments
            $table->string('strand')->nullable()->after('section')->comment('For SHS: STEM, ABM, HUMSS, TVL');
            $table->string('track')->nullable()->after('strand')->comment('For TVL strand: ICT, H.E., etc.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('faculty_assignments', function (Blueprint $table) {
            $table->dropColumn(['strand', 'track']);
        });
    }
};

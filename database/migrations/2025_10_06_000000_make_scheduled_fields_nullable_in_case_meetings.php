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
        Schema::table('case_meetings', function (Blueprint $table) {
            $table->date('scheduled_date')->nullable()->change();
            $table->time('scheduled_time')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('case_meetings', function (Blueprint $table) {
            $table->date('scheduled_date')->nullable(false)->change();
            $table->time('scheduled_time')->nullable(false)->change();
        });
    }
};

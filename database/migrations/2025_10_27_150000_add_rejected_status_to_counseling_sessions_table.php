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
            $table->enum('status', ['scheduled', 'completed', 'cancelled', 'rescheduled', 'recommended', 'rejected'])->default('scheduled')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('counseling_sessions', function (Blueprint $table) {
            $table->enum('status', ['scheduled', 'completed', 'cancelled', 'rescheduled', 'recommended'])->default('scheduled')->change();
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('case_meetings', function (Blueprint $table) {
            // Change ENUM to include all required statuses
            $table->enum('status', ['scheduled', 'in_progress', 'pre_completed', 'submitted', 'completed', 'cancelled', 'forwarded'])
                ->default('scheduled')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('case_meetings', function (Blueprint $table) {
            // Revert ENUM to previous values (update as needed)
            $table->enum('status', ['scheduled', 'pre_completed', 'completed', 'cancelled'])
                ->default('scheduled')
                ->change();
        });
    }
};

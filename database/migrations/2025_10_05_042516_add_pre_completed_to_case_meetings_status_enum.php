<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE case_meetings MODIFY COLUMN status ENUM('scheduled', 'in_progress', 'pre_completed', 'completed', 'cancelled', 'forwarded') DEFAULT 'scheduled'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE case_meetings MODIFY COLUMN status ENUM('scheduled', 'in_progress', 'completed', 'cancelled', 'forwarded') DEFAULT 'scheduled'");
    }
};

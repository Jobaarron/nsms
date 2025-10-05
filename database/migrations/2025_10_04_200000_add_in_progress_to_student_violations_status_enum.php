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
        DB::statement("ALTER TABLE student_violations MODIFY COLUMN status ENUM('pending', 'investigating', 'in_progress', 'resolved', 'dismissed', 'forwarded') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE student_violations MODIFY COLUMN status ENUM('pending', 'investigating', 'resolved', 'dismissed', 'forwarded') NOT NULL DEFAULT 'pending'");
    }
};

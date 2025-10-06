<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE student_violations MODIFY COLUMN status ENUM('pending', 'investigating', 'resolved', 'dismissed', 'forwarded') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE student_violations MODIFY COLUMN status ENUM('pending', 'investigating', 'resolved', 'dismissed') NOT NULL DEFAULT 'pending'");
    }
};

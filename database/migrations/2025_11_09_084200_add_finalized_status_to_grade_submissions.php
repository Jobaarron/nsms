<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add 'finalized' to the status ENUM
        DB::statement("ALTER TABLE grade_submissions MODIFY COLUMN status ENUM('draft', 'submitted', 'approved', 'rejected', 'revision_requested', 'finalized') NOT NULL DEFAULT 'draft'");
    }

    public function down(): void
    {
        // Remove 'finalized' from the status ENUM
        DB::statement("ALTER TABLE grade_submissions MODIFY COLUMN status ENUM('draft', 'submitted', 'approved', 'rejected', 'revision_requested') NOT NULL DEFAULT 'draft'");
    }
};

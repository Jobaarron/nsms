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
        DB::statement("ALTER TABLE enrollees MODIFY COLUMN enrollment_status ENUM('pending', 'approved', 'rejected', 'enrolled', 'cancelled', 'rejected_appeal') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First, update any rejected_appeal records back to rejected before removing the enum value
        DB::statement("UPDATE enrollees SET enrollment_status = 'rejected' WHERE enrollment_status = 'rejected_appeal'");
        DB::statement("ALTER TABLE enrollees MODIFY COLUMN enrollment_status ENUM('pending', 'approved', 'rejected', 'enrolled', 'cancelled') DEFAULT 'pending'");
    }
};

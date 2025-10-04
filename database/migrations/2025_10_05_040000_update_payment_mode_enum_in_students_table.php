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
        // Update the payment_mode enum to use the new values
        DB::statement("ALTER TABLE students MODIFY COLUMN payment_mode ENUM('full', 'quarterly', 'monthly') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to the original enum values
        DB::statement("ALTER TABLE students MODIFY COLUMN payment_mode ENUM('cash', 'online payment', 'installment') NULL");
    }
};

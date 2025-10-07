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
        // Update payment_method enum to include cash, online, bank_transfer
        DB::statement("ALTER TABLE payments MODIFY COLUMN payment_method ENUM('cash', 'online', 'bank_transfer', 'scholarship', 'voucher')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum values
        DB::statement("ALTER TABLE payments MODIFY COLUMN payment_method ENUM('cash', 'online_payment', 'scholarship', 'voucher')");
    }
};

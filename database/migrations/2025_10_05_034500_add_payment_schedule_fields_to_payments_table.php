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
        Schema::table('payments', function (Blueprint $table) {
            $table->date('scheduled_date')->nullable()->after('paid_at');
            $table->string('period_name')->nullable()->after('scheduled_date');
            // payment_mode removed - using payment_method for schedule type
            $table->decimal('amount_received', 10, 2)->nullable()->after('period_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['scheduled_date', 'period_name', 'amount_received']);
        });
    }
};

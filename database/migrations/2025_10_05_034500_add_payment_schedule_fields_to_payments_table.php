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
            $table->string('payment_mode')->nullable()->after('period_name');
            $table->decimal('amount_received', 10, 2)->nullable()->after('payment_mode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['scheduled_date', 'period_name', 'payment_mode', 'amount_received']);
        });
    }
};

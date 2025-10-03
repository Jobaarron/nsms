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
            $table->foreignId('processed_by')->nullable()->constrained('cashiers')->onDelete('set null');
            $table->timestamp('confirmed_at')->nullable();
            $table->text('cashier_notes')->nullable();
            $table->enum('confirmation_status', ['pending', 'confirmed', 'rejected'])->default('pending');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['processed_by']);
            $table->dropColumn(['processed_by', 'confirmed_at', 'cashier_notes', 'confirmation_status']);
        });
    }
};

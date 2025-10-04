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
            // Drop the existing foreign key constraint
            $table->dropForeign(['fee_id']);
            
            // Modify the fee_id column to be nullable
            $table->foreignId('fee_id')->nullable()->change()->constrained('fees')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Drop the nullable foreign key
            $table->dropForeign(['fee_id']);
            
            // Restore the original non-nullable foreign key
            $table->foreignId('fee_id')->change()->constrained('fees')->onDelete('cascade');
        });
    }
};

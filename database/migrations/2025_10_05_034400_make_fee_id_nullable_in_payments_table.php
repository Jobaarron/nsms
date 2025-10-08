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
        Schema::table('payments', function (Blueprint $table) {
            // Drop the existing foreign key constraint if it exists
            try {
                $table->dropForeign(['fee_id']);
            } catch (Exception $e) {
                // Foreign key might not exist, continue
            }
            
            // Modify the fee_id column to be nullable
            $table->unsignedBigInteger('fee_id')->nullable()->change();
            
            // Add the foreign key constraint
            $table->foreign('fee_id')->references('id')->on('fees')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            // First, handle any NULL values by either removing records or setting a default
            // Option 1: Delete records with NULL fee_id (if they're invalid)
            DB::table('payments')->whereNull('fee_id')->delete();
            
            // Option 2: If you want to keep records, set a default fee_id
            // Ensure we have a default fee to reference
            $defaultFee = DB::table('fees')->first();
            if ($defaultFee) {
                DB::table('payments')->whereNull('fee_id')->update(['fee_id' => $defaultFee->id]);
            }
            
            Schema::table('payments', function (Blueprint $table) {
                // Drop the nullable foreign key if it exists
                try {
                    $table->dropForeign(['fee_id']);
                } catch (Exception $e) {
                    // Foreign key might not exist, continue
                }
                
                // First change the column to NOT NULL
                $table->unsignedBigInteger('fee_id')->change();
                
                // Then add the foreign key constraint
                $table->foreign('fee_id')->references('id')->on('fees')->onDelete('cascade');
            });
        } catch (Exception $e) {
            // If rollback fails, log the error but don't stop the process
            \Log::error('Migration rollback failed: ' . $e->getMessage());
        }
    }
};

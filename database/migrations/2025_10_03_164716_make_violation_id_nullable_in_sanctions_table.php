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
        Schema::table('sanctions', function (Blueprint $table) {
            // Drop the existing foreign key if it exists
            try {
                $table->dropForeign(['violation_id']);
            } catch (Exception $e) {
                // Foreign key might not exist, continue
            }
            
            // Make violation_id nullable
            $table->unsignedBigInteger('violation_id')->nullable()->change();
            
            // Add the foreign key constraint
            $table->foreign('violation_id')->references('id')->on('student_violations')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            // First, handle any NULL values by removing records with NULL violation_id
            // These records are likely invalid since violations should have a violation_id
            DB::table('sanctions')->whereNull('violation_id')->delete();
            
            Schema::table('sanctions', function (Blueprint $table) {
                // Drop the nullable foreign key if it exists
                try {
                    $table->dropForeign(['violation_id']);
                } catch (Exception $e) {
                    // Foreign key might not exist, continue
                }
                
                // First change the column to NOT NULL
                $table->unsignedBigInteger('violation_id')->change();
                
                // Then add the foreign key constraint
                $table->foreign('violation_id')->references('id')->on('student_violations')->onDelete('cascade');
            });
        } catch (Exception $e) {
            // If rollback fails, log the error but don't stop the process
            \Log::error('Migration rollback failed: ' . $e->getMessage());
        }
    }
};

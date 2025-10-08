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
        Schema::table('sanctions', function (Blueprint $table) {
            $table->dropForeign(['violation_id']);
            $table->foreign('violation_id')->references('id')->on('student_violations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First, safely drop any existing foreign key constraints
        try {
            Schema::table('sanctions', function (Blueprint $table) {
                $table->dropForeign(['violation_id']);
            });
        } catch (Exception $e) {
            // Foreign key might not exist, continue
        }
        
        // Then add the foreign key to the appropriate table
        Schema::table('sanctions', function (Blueprint $table) {
            // Check if 'violations' table exists, otherwise use 'student_violations'
            $tableName = Schema::hasTable('violations') ? 'violations' : 'student_violations';
            $table->foreign('violation_id')->references('id')->on($tableName)->onDelete('cascade');
        });
    }
};

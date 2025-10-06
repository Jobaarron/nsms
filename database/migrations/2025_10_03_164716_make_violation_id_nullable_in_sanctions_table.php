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
            // Drop the existing foreign key
            $table->dropForeign(['violation_id']);
            // Make violation_id nullable
            $table->foreignId('violation_id')->nullable()->change()->constrained('student_violations')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sanctions', function (Blueprint $table) {
            // Drop the nullable foreign key
            $table->dropForeign(['violation_id']);
            // Make violation_id not nullable again
            $table->foreignId('violation_id')->change()->constrained('student_violations')->onDelete('cascade');
        });
    }
};

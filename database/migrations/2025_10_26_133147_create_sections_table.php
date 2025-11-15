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
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->string('section_name'); // e.g., 'A', 'B', 'C', 'Diamond', 'Ruby'
            $table->string('grade_level'); // e.g., 'Grade 1', 'Grade 7', 'Kinder'
            $table->string('academic_year'); // e.g., '2025-2026'
            $table->integer('max_students')->default(40); // Maximum students per section
            $table->integer('current_students')->default(0); // Current enrolled students
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable(); // Optional description
            $table->timestamps();
            
            // Ensure unique section per grade level per academic year
            $table->unique(['section_name', 'grade_level', 'academic_year']);
        });

        // Add section_id to students table
        Schema::table('students', function (Blueprint $table) {
            $table->foreignId('section_id')->nullable()->constrained('sections')->onDelete('set null');
            $table->index('section_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove section_id from students table first
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['section_id']);
            $table->dropColumn('section_id');
        });
        
        Schema::dropIfExists('sections');
    }
};

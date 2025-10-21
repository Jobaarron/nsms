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
        // Revamped faculty_assignments table using Laravel Schema Builder
        // This table enables all faculty head features from the checklist:
        // - Assign teacher per subject/section
        // - Assign adviser per class  
        // - View submitted grades from teachers
        // - Approve/reject submitted grades
        // - Activate grade submission
        
        // Use the same approach as grade_submissions migration
        Schema::create('faculty_assignments', function (Blueprint $table) {
            $table->id();
            
            // Core assignment relationships - following the same pattern as grade_submissions
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('assigned_by')->constrained('users')->onDelete('cascade');
            
            // Class identification
            $table->string('grade_level')->comment('Grade level (e.g., Grade 7, Grade 8)');
            $table->string('section')->comment('Section name (e.g., A, B, C)');
            $table->string('academic_year')->default('2025-2026')->comment('Academic year');
            
            // Assignment type and status
            $table->enum('assignment_type', ['subject_teacher', 'class_adviser'])
                  ->default('subject_teacher')
                  ->comment('Type of assignment');
            $table->enum('status', ['active', 'inactive', 'pending'])
                  ->default('active')
                  ->comment('Assignment status');
            
            // Assignment dates
            $table->date('assigned_date')->nullable()->comment('Date when assignment was made');
            $table->date('effective_date')->nullable()->comment('Date when assignment becomes effective');
            $table->date('end_date')->nullable()->comment('Date when assignment ends');
            
            // Additional information
            $table->text('notes')->nullable()->comment('Additional notes about the assignment');
            $table->integer('student_count')->default(0)->comment('Number of students in the class');
            $table->decimal('weekly_hours', 5, 2)->default(0)->comment('Weekly teaching hours');
            
            $table->timestamps();
            
            // Indexes for better performance - following grade_submissions pattern
            $table->index(['teacher_id', 'academic_year', 'status'], 'fa_teacher_year_status_idx');
            $table->index(['subject_id', 'grade_level', 'section'], 'fa_subject_class_idx');
            $table->index(['assignment_type', 'status'], 'fa_assignment_status_idx');
            $table->index(['grade_level', 'section', 'academic_year'], 'fa_class_lookup_idx');
            
            // Unique constraint to prevent duplicate assignments - same pattern as grade_submissions
            $table->unique(['teacher_id', 'subject_id', 'grade_level', 'section', 'academic_year'], 'unique_faculty_assignment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Use the same approach as grade_submissions migration
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        Schema::dropIfExists('faculty_assignments');
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
};

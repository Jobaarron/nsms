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
        Schema::create('grade_submissions', function (Blueprint $table) {
            $table->id();
            
            // Core relationships - interconnected with existing grades system
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade'); // Same as grades.teacher_id
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            
            // Submission details
            $table->string('grade_level'); // Links to students.grade_level
            $table->string('section'); // Links to students.section
            $table->string('academic_year')->default(date('Y') . '-' . (date('Y') + 1));
            $table->enum('quarter', ['1st', '2nd', '3rd', '4th']);
            
            // Submission workflow
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected', 'revision_requested'])->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null'); // Faculty head
            
            // Grade data - JSON to store all student grades at once
            $table->json('grades_data'); // Array of {student_id, grade, remarks}
            $table->integer('total_students');
            $table->integer('grades_entered');
            
            // Workflow notes
            $table->text('submission_notes')->nullable();
            $table->text('review_notes')->nullable();
            
            // Integration with existing grades table
            $table->boolean('grades_finalized')->default(false); // Whether grades are copied to grades table
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['teacher_id', 'academic_year', 'status'], 'gs_teacher_year_status_idx');
            $table->index(['grade_level', 'section', 'quarter', 'academic_year'], 'gs_grade_section_quarter_year_idx');
            $table->index(['subject_id', 'quarter', 'academic_year'], 'gs_subject_quarter_year_idx');
            $table->index(['reviewed_by', 'status'], 'gs_reviewer_status_idx');
            
            // Prevent duplicate submissions
            $table->unique(['teacher_id', 'subject_id', 'grade_level', 'section', 'quarter', 'academic_year'], 'unique_grade_submission');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        Schema::dropIfExists('grade_submissions');
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
};

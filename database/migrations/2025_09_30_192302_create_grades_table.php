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
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            
            // Core relationships
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            
            // Academic period
            $table->string('academic_year', 10)->default(date('Y') . '-' . (date('Y') + 1));
            $table->enum('quarter', ['1st', '2nd', '3rd', '4th']);
            
            // Simple grade storage - just the final grade per quarter
            $table->decimal('grade', 5, 2)->nullable(); // Final grade for the quarter (0-100)
            
            // Optional remarks
            $table->text('remarks')->nullable();
            
            // Simple tracking
            $table->timestamp('submitted_at')->nullable();
            $table->boolean('is_final')->default(false);
            
            $table->timestamps();
            
            // Constraints and indexes
            $table->unique(['student_id', 'subject_id', 'quarter', 'academic_year'], 'unique_grade_record');
            $table->index(['student_id', 'academic_year']);
            $table->index(['teacher_id', 'quarter']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};

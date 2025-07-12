<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->string('subject');
            $table->string('quarter');
            $table->decimal('grade', 5, 2);
            $table->text('remarks')->nullable();
            $table->string('academic_year');
            $table->timestamps();
            
            // Prevent duplicate grades for same student/subject/quarter
            $table->unique(['student_id', 'subject', 'quarter', 'academic_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};

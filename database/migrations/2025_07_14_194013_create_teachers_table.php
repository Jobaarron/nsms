<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('employee_id')->unique();
            $table->string('department');
            $table->string('subject_specialization');
            $table->enum('employment_type', ['full_time', 'part_time', 'substitute'])->default('full_time');
            $table->enum('teacher_level', ['junior', 'senior', 'head_teacher', 'department_head'])->default('junior');
            $table->date('hire_date');
            $table->string('qualification')->nullable();
            $table->integer('years_experience')->default(0);
            $table->json('subjects_taught')->nullable(); // Array of subjects
            $table->json('class_assignments')->nullable(); // Array of assigned classes
            $table->json('permissions')->nullable(); // Additional teacher-specific permissions
            $table->timestamp('last_login_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Indexes
            $table->index('user_id');
            $table->index('department');
            $table->index('teacher_level');
            $table->index('is_active');
            $table->index('employment_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
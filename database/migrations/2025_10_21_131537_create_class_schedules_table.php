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
        Schema::create('class_schedules', function (Blueprint $table) {
            $table->id();
            
            // Core relationships - interconnected with existing system
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade'); // Links to teachers table
            
            // Class details
            $table->string('grade_level'); // Links to students.grade_level
            $table->string('section'); // Links to students.section
            $table->string('academic_year')->default(date('Y') . '-' . (date('Y') + 1));
            
            // Schedule details
            $table->enum('day_of_week', ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']);
            $table->time('start_time');
            $table->time('end_time');
            $table->string('room')->nullable();
            
            // Status
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['grade_level', 'section', 'academic_year']);
            $table->index(['teacher_id', 'day_of_week']);
            $table->index(['subject_id', 'academic_year']);
            
            // Prevent duplicate schedules
            $table->unique(['subject_id', 'grade_level', 'section', 'day_of_week', 'start_time', 'academic_year'], 'unique_class_schedule');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        Schema::dropIfExists('class_schedules');
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
};

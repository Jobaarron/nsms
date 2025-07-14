<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discipline_officers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('employee_id')->unique();
            $table->enum('officer_level', ['officer', 'senior_officer', 'head_discipline'])->default('officer');
            $table->json('areas_of_responsibility')->nullable(); // e.g., ['attendance', 'behavior', 'uniform', 'safety']
            $table->json('grade_levels_assigned')->nullable(); // Grade levels they handle
            $table->string('office_location')->nullable();
            $table->json('patrol_schedule')->nullable(); // Patrol times and areas
            $table->boolean('can_suspend')->default(false);
            $table->boolean('can_expel')->default(false);
            $table->date('hire_date');
            $table->string('qualification');
            $table->json('training_certifications')->nullable(); // Safety, conflict resolution, etc.
            $table->json('permissions')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Indexes
            $table->index('user_id');
            $table->index('officer_level');
            $table->index('is_active');
            $table->index('can_suspend');
            $table->index('can_expel');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discipline_officers');
    }
};

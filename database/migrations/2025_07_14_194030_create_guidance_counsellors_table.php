<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guidance_counsellors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('employee_id')->unique();
            $table->string('license_number')->nullable();
            $table->enum('counsellor_level', ['junior', 'senior', 'head_counsellor'])->default('junior');
            $table->json('specializations')->nullable(); // e.g., ['academic', 'career', 'personal', 'crisis']
            $table->json('grade_levels_assigned')->nullable(); // Grade levels they handle
            $table->string('office_location')->nullable();
            $table->json('available_hours')->nullable(); // Working hours
            $table->integer('max_students_per_day')->default(20);
            $table->date('hire_date');
            $table->string('qualification');
            $table->json('certifications')->nullable(); // Additional certifications
            $table->json('permissions')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Indexes
            $table->index('user_id');
            $table->index('counsellor_level');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guidance_counsellors');
    }
};

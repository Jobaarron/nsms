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
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('employee_id')->unique()->comment('Unique identifier for the teacher (e.g., TCH001)');
            $table->string('department')->nullable()->comment('Department the teacher belongs to');
            $table->string('position')->nullable()->comment('Position or job title');
            $table->string('specialization')->nullable()->comment('Teacher specialization or subject area');
            $table->enum('employment_status', ['full_time', 'part_time', 'contractual', 'substitute'])->default('full_time');
            $table->json('subjects')->nullable()->comment('Subjects taught by the teacher');
            $table->date('hire_date')->nullable()->comment('Date when the teacher was hired');
            $table->string('phone_number')->nullable()->comment('Contact number');
            $table->text('address')->nullable()->comment('Residential address');
            $table->text('qualifications')->nullable()->comment('Educational background and certifications');
            $table->boolean('is_active')->default(true)->comment('Whether the teacher is currently active');
            $table->timestamps();
            
            // Indexes
            $table->index('employee_id');
            $table->index('department');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};

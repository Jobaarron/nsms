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
        Schema::create('faculty_heads', function (Blueprint $table) {
            $table->id();
            
            // Link to users table for authentication
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Faculty Head identification
            $table->string('employee_id')->unique()->comment('Unique identifier for the faculty head');
            
            // Personal Information
            $table->string('department')->nullable()->comment('Department the faculty head manages');
            $table->string('position')->default('Faculty Head')->comment('Position title');
            
            // Employment details
            $table->date('appointed_date')->nullable()->comment('Date when appointed as faculty head');
            $table->enum('employment_status', ['active', 'inactive', 'on_leave'])->default('active');
            
            // Contact Information
            $table->string('phone_number')->nullable()->comment('Contact number');
            $table->text('address')->nullable()->comment('Residential address');
            
            // Administrative details
            $table->text('qualifications')->nullable()->comment('Educational background and certifications');
            $table->json('permissions')->nullable()->comment('Specific permissions for this faculty head');
            
            // Status
            $table->boolean('is_active')->default(true)->comment('Whether the faculty head is currently active');
            $table->text('notes')->nullable()->comment('Additional notes or remarks');
            
            $table->timestamps();
            
            // Indexes
            $table->index('employee_id');
            $table->index('department');
            $table->index(['is_active', 'employment_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faculty_heads');
    }
};

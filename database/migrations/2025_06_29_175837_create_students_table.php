<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->longText('id_photo')->nullable(); // Store image as base64 binary data
            $table->string('id_photo_mime_type')->nullable(); // Store MIME type (image/jpeg, image/png)
            
            // Link to users table for authentication
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            
            // Student identification
            $table->string('student_id')->unique()->nullable(); // e.g., STU-2024-001
            $table->string('lrn')->unique()->nullable(); // Learner Reference Number
            
            // ENROLLMENT STATUS AND ACADEMIC INFO
            $table->enum('enrollment_status', ['pending', 'approved', 'rejected', 'enrolled', 'dropped', 'graduated'])->default('pending');
            $table->string('academic_year')->default('2024-2025');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('enrolled_at')->nullable();
            $table->string('password');
            
            // FILE PATHS
            $table->json('documents')->nullable(); // Birth certificate, report cards, etc.
            
            // PERSONAL INFORMATION
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('suffix')->nullable(); // Jr., Sr., III, etc.
            $table->date('date_of_birth');
            $table->string('place_of_birth')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            // $table->enum('civil_status', ['single', 'married', 'widowed', 'separated'])->default('single');
            $table->string('nationality')->default('Filipino');
            $table->string('religion')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('email')->unique()->nullable(); // Make nullable since user_id handles auth
            $table->text('address');
            // $table->string('barangay')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('zip_code')->nullable();
            
            // ACADEMIC INFORMATION
            $table->string('grade_level'); // Changed from grade_applied
            $table->string('strand')->nullable(); // For SHS students
            $table->string('track')->nullable(); // Academic, TVL, Sports, Arts & Design
            $table->string('section')->nullable();
            $table->enum('student_type', ['new', 'transferee', 'returnee', 'continuing'])->default('new');
            
            // GUARDIAN/PARENT INFORMATION
            $table->string('father_name')->nullable();
            $table->string('father_occupation')->nullable();
            $table->string('father_contact')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('mother_occupation')->nullable();
            $table->string('mother_contact')->nullable();
            $table->string('guardian_name'); // Primary guardian
            // $table->string('guardian_relationship')->nullable();
            $table->string('guardian_contact');
            // $table->string('guardian_email')->nullable();
            // $table->text('guardian_address')->nullable();
            
            // PREVIOUS SCHOOL INFORMATION
            $table->enum('last_school_type', ['public', 'private'])->nullable();
            $table->string('last_school_name')->nullable();
            // $table->string('last_school_address')->nullable();
            // $table->string('last_grade_completed')->nullable();
            // $table->year('year_graduated')->nullable();
            // $table->decimal('general_average', 5, 2)->nullable();
            
            // MEDICAL AND HEALTH INFORMATION
            $table->text('medical_history')->nullable();
            
            
            // FINANCIAL INFORMATION
            // $table->enum('payment_mode', ['cash', 'installment', 'scholarship', 'voucher'])->default('cash');
            $table->enum('payment_mode', ['cash', 'online payment', 'scholarship', 'voucher'])->default('cash');
            $table->boolean('is_paid')->default(false);
            // $table->boolean('is_scholar')->default(false);
            // $table->string('scholarship_type')->nullable();
            // $table->decimal('scholarship_amount', 10, 2)->nullable();
            // $table->boolean('is_pwd')->default(false); // Person with Disability
            // $table->boolean('is_indigenous')->default(false); // Indigenous People
            
            // ENROLLMENT SCHEDULING
            $table->date('preferred_schedule')->nullable();
            $table->timestamp('enrollment_date')->nullable();
            
            // STATUS TRACKING
            $table->boolean('is_active')->default(true);
            // $table->text('remarks')->nullable();
            
            // TIMESTAMPS
            $table->timestamps();
            
            // INDEXES FOR BETTER PERFORMANCE
            $table->index(['enrollment_status', 'academic_year']);
            $table->index(['grade_level', 'section']);
            $table->index(['student_type', 'is_active']);
            $table->index('last_name');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};

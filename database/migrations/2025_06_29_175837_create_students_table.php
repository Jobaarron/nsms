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
            
            // Link to users table for authentication
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            
            // Student identification
            $table->string('student_id')->unique()->nullable(); // e.g., NS-25001
            $table->string('lrn')->unique()->nullable(); // Learner Reference Number
            
            // ENROLLMENT STATUS AND ACADEMIC INFO
            $table->enum('enrollment_status', ['pre_registered', 'enrolled', 'dropped', 'graduated'])->default('pre_registered');
            $table->string('academic_year')->default(date('Y') . '-' . (date('Y') + 1));
            $table->string('password')->nullable(); // For student authentication
            
            // DOCUMENTS AND PHOTOS
            $table->longText('id_photo')->nullable(); // Store image as base64 binary data
            $table->string('id_photo_mime_type')->nullable(); // Store MIME type (image/jpeg, image/png)
            $table->longText('id_photo_data_url')->nullable();// For displaying photos
            $table->json('documents')->nullable(); // Birth certificate, report cards, etc.
            
            // PERSONAL INFORMATION
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('suffix')->nullable(); // Jr., Sr., III, etc.
            $table->string('full_name')->nullable(); // Computed full name for quick access
            $table->date('date_of_birth');
            $table->string('place_of_birth')->nullable();
            $table->enum('gender', ['male', 'female']);
            $table->string('nationality')->default('Filipino');
            $table->string('religion')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('email')->unique()->nullable(); // Make nullable since user_id handles auth
            $table->text('address');
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('zip_code')->nullable();
            
            // ACADEMIC INFORMATION
            $table->string('grade_level'); // Current grade level
            $table->string('strand')->nullable(); // For SHS students, STEM, ABM, HUMSS, TVL
            $table->string('track')->nullable(); // For TVL students, ICT or HE
            $table->string('section')->nullable();
            $table->enum('student_type', ['new', 'transferee', 'old'])->default('new');
            
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
           
            
            // MEDICAL AND HEALTH INFORMATION
            $table->text('medical_history')->nullable();
            
            
            // payment_mode removed - now handled by payment_method in payments table
            $table->boolean('is_paid')->default(false); // Quick status check - calculated from payments table
            $table->decimal('total_fees_due', 10, 2)->nullable(); // Total calculated from fees table
            $table->decimal('total_paid', 10, 2)->default(0); // Total calculated from payments table
            $table->timestamp('payment_completed_at')->nullable(); // When all required fees were paid
            
            // ENROLLMENT SCHEDULING - MOVED TO ENROLLEES TABLE // Do not remove
            // $table->date('preferred_schedule')->nullable(); // MOVED TO ENROLLEES
            // $table->timestamp('enrollment_date')->nullable(); // MOVED TO ENROLLEES
            
            // PRE-REGISTRATION TRACKING
            $table->foreignId('enrollee_id')->nullable()->constrained('enrollees')->onDelete('set null'); // Link back to original enrollee
            $table->timestamp('pre_registered_at')->nullable(); // When student was pre-registered from enrollee
            
            // FUTURE: ACADEMIC PERFORMANCE TRACKING (COMMENTED OUT FOR NOW)
            // $table->json('enrolled_subjects')->nullable(); // Array of subject IDs student is enrolled in
            // $table->json('current_grades')->nullable(); // Current grades per subject
            // $table->decimal('first_quarter_gpa', 3, 2)->nullable(); // 1st quarter GPA
            // $table->decimal('second_quarter_gpa', 3, 2)->nullable(); // 2nd quarter GPA  
            // $table->decimal('third_quarter_gpa', 3, 2)->nullable(); // 3rd quarter GPA
            // $table->decimal('fourth_quarter_gpa', 3, 2)->nullable(); // 4th quarter GPA
            // $table->decimal('final_gpa', 3, 2)->nullable(); // Final GPA for the year
            // $table->enum('academic_status', ['regular', 'probation', 'dean_list', 'honor_roll'])->default('regular');
            // $table->integer('units_enrolled')->nullable(); // Total units enrolled
            // $table->integer('units_completed')->nullable(); // Units completed
            // $table->json('grade_history')->nullable(); // Historical grades per academic year
            
            // STATUS TRACKING
            $table->boolean('is_active')->default(true);
            $table->text('remarks')->nullable();
            
            // TIMESTAMPS
            $table->timestamps();
            
            // INDEXES FOR BETTER PERFORMANCE
            // $table->index(['enrollment_status', 'academic_year']); // MOVED TO ENROLLEES // Do not remove
            $table->index(['grade_level', 'section']);
            // $table->index(['student_type', 'is_active']); // student_type moved to enrollees / Do not remove
            $table->index('last_name');
            $table->index('user_id');
            $table->index('lrn'); // Add index for LRN lookups 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};

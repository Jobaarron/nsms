<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollees', function (Blueprint $table) {
            $table->id();
            
            // Link to users table for admin who processes the enrollment
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Enrollee identification
            $table->string('application_id')->unique(); // e.g., 25-001 / Password: 25-001
            $table->string('lrn')->unique()->nullable(); // Learner Reference Number
            
            // ENROLLMENT STATUS AND WORKFLOW
            $table->enum('enrollment_status', ['pending', 'approved', 'rejected', 'enrolled', 'cancelled'])->default('pending');
            $table->string('academic_year')->default(date('Y') . '-' . (date('Y') + 1));
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('enrolled_at')->nullable();
            $table->text('status_reason')->nullable(); // Reason for approval/rejection
            
            // EVALUATION TRACKING
            $table->timestamp('first_viewed_at')->nullable();
            $table->unsignedBigInteger('first_viewed_by')->nullable();
            $table->timestamp('evaluation_started_at')->nullable();
            $table->unsignedBigInteger('evaluation_started_by')->nullable();
            $table->timestamp('evaluation_completed_at')->nullable();
            $table->unsignedBigInteger('evaluation_completed_by')->nullable();
            $table->integer('documents_reviewed_count')->default(0);
            $table->integer('documents_total_count')->default(0);
            
            // ENROLLMENT DOCUMENTS
            $table->longText('id_photo')->nullable(); // Store image as base64 binary data
            $table->string('id_photo_mime_type')->nullable(); // Store MIME type (image/jpeg, image/png)
            $table->longText('id_photo_data_url')->nullable(); // For displaying photos
            $table->json('documents')->nullable(); // Birth certificate, report cards, etc.
            
            // PERSONAL INFORMATION
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('suffix')->nullable(); // Jr., Sr., III, etc.
            $table->date('date_of_birth');
            $table->string('place_of_birth')->nullable();
            $table->enum('gender', ['male', 'female']);
            $table->string('nationality')->default('Filipino');
            $table->string('religion')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('email')->unique(); // Required for enrollment application
            $table->string('password')->nullable(); // For enrollee authentication
            $table->string('remember_token')->nullable(); // For remember me functionality
            $table->text('address');
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('zip_code')->nullable();
            
            // ACADEMIC INFORMATION APPLIED FOR
            $table->string('grade_level_applied'); // Grade level they're applying for
            $table->string('strand_applied')->nullable(); // For SHS students, STEM, ABM, HUMSS, TVL
            $table->string('track_applied')->nullable(); // For TVL students, ICT or HE
            $table->enum('student_type', ['new', 'transferee', 'old'])->default('new');
            
            // GUARDIAN/PARENT INFORMATION
            $table->string('father_name')->nullable();
            $table->string('father_occupation')->nullable();
            $table->string('father_contact')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('mother_occupation')->nullable();
            $table->string('mother_contact')->nullable();
            $table->string('guardian_name'); 
            $table->string('guardian_contact');
            
            // PREVIOUS SCHOOL INFORMATION
            $table->enum('last_school_type', ['public', 'private'])->nullable();
            $table->string('last_school_name')->nullable();
            
            // MEDICAL AND HEALTH INFORMATION
            $table->text('medical_history')->nullable();
            
            // ENROLLMENT FINANCIAL INFORMATION - Works with fees and payments tables
            // Note that detailed payment records are stored in payments table via polymorphic relationship
            // payment_mode removed - now handled by payment_method in payments table
            $table->boolean('is_paid')->default(false); // Quick status check - calculated from payments table
            $table->decimal('total_fees_due', 10, 2)->nullable(); // Total calculated from fees table
            $table->decimal('total_paid', 10, 2)->default(0); // Total calculated from payments table
            $table->timestamp('payment_completed_at')->nullable(); // When all required fees were paid
            
            // ENROLLMENT SCHEDULING
            $table->timestamp('enrollment_date')->nullable();
            $table->timestamp('application_date')->default(now());
            
            // PRE-REGISTRATION TRACKING
            $table->string('student_id')->nullable(); // Generated when pre-registered as student (e.g., NS-25001)
            $table->timestamp('pre_registered_at')->nullable(); // When enrollee pre-registered as student
            
            // STATUS TRACKING
            $table->boolean('is_active')->default(true);
            $table->text('remarks')->nullable();
            
            // TIMESTAMPS
            $table->timestamps();
            
            // INDEXES FOR BETTER PERFORMANCE
            $table->index(['enrollment_status', 'academic_year']);
            $table->index(['grade_level_applied', 'student_type']);
            $table->index(['application_date', 'enrollment_status']);
            $table->index('last_name');
            $table->index('lrn');
            $table->index('email');
            $table->index('application_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollees');
    }
};

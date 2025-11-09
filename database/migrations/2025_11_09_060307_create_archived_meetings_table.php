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
        Schema::create('archived_meetings', function (Blueprint $table) {
            $table->id();
            
            // Original case meeting ID for reference
            $table->unsignedBigInteger('original_case_meeting_id');
            
            // Basic meeting information
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('violation_id')->nullable();
            $table->unsignedBigInteger('counselor_id')->nullable();
            $table->unsignedBigInteger('adviser_id')->nullable();
            $table->string('meeting_type')->nullable();
            $table->date('scheduled_date')->nullable();
            $table->time('scheduled_time')->nullable();
            $table->string('location')->nullable();
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->text('teacher_statement')->nullable();
            $table->text('action_plan')->nullable();
            $table->string('status')->default('pending');
            $table->text('summary')->nullable();
            $table->text('recommendations')->nullable();
            $table->boolean('follow_up_required')->default(false);
            $table->date('follow_up_date')->nullable();
            $table->text('sanction_recommendation')->nullable();
            $table->enum('urgency_level', ['low', 'medium', 'high'])->default('medium');
            $table->text('president_notes')->nullable();
            $table->boolean('forwarded_to_president')->default(false);
            $table->timestamp('forwarded_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            
            // Agreed Actions/Interventions fields
            $table->boolean('written_reflection')->default(false);
            $table->date('written_reflection_due')->nullable();
            $table->string('mentor_name')->nullable();
            $table->boolean('mentorship_counseling')->default(false);
            $table->boolean('parent_teacher_communication')->default(false);
            $table->date('parent_teacher_date')->nullable();
            $table->boolean('restorative_justice_activity')->default(false);
            $table->date('restorative_justice_date')->nullable();
            $table->boolean('follow_up_meeting')->default(false);
            $table->date('follow_up_meeting_date')->nullable();
            $table->boolean('community_service')->default(false);
            $table->date('community_service_date')->nullable();
            $table->string('community_service_area')->nullable();
            $table->boolean('suspension')->default(false);
            $table->boolean('suspension_3days')->default(false);
            $table->boolean('suspension_5days')->default(false);
            $table->integer('suspension_other_days')->nullable();
            $table->date('suspension_start')->nullable();
            $table->date('suspension_end')->nullable();
            $table->date('suspension_return')->nullable();
            $table->boolean('expulsion')->default(false);
            $table->date('expulsion_date')->nullable();
            
            // Student reply fields
            $table->text('student_statement')->nullable();
            $table->text('incident_feelings')->nullable();
            $table->text('student_reply_incident_date')->nullable();
            $table->text('student_reply_location')->nullable();
            $table->text('student_reply_people_involved')->nullable();
            $table->text('student_reply_what_happened')->nullable();
            $table->text('student_reply_feelings')->nullable();
            $table->text('student_reply_why_happened')->nullable();
            $table->text('student_reply_what_learned')->nullable();
            $table->text('student_reply_prevent_future')->nullable();
            $table->text('student_reply_additional_comments')->nullable();
            
            // Archive metadata
            $table->timestamp('archived_at')->nullable();
            $table->string('archived_by')->nullable(); // User who archived it
            $table->string('archive_reason')->default('case_closed');
            
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('violation_id')->references('id')->on('student_violations')->onDelete('set null');
            
            // Indexes for better performance
            $table->index('student_id');
            $table->index('archived_at');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archived_meetings');
    }
};

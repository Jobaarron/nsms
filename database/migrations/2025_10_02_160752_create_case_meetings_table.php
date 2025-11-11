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
        Schema::create('case_meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->unsignedBigInteger('violation_id')->nullable();
            $table->foreign('violation_id')->references('id')->on('student_violations')->onDelete('set null');
            $table->foreignId('counselor_id')->constrained('guidances')->onDelete('cascade');
            $table->unsignedBigInteger('adviser_id')->nullable();
            $table->foreign('adviser_id')->references('id')->on('users')->onDelete('set null');
            $table->enum('meeting_type', ['case_meeting', 'house_visit'])->default('case_meeting');
            $table->date('scheduled_date')->nullable();
            $table->time('scheduled_time')->nullable();
            $table->string('location')->nullable();
            $table->text('reason');
            $table->text('notes')->nullable();
            $table->text('teacher_statement')->nullable();
            $table->text('action_plan')->nullable();
            $table->enum('status', ['scheduled', 'in_progress', 'pre_completed', 'submitted', 'case_closed', 'cancelled', 'forwarded'])->default('scheduled');
            $table->text('summary')->nullable();
            $table->text('recommendations')->nullable();
            $table->boolean('follow_up_required')->default(false);
            $table->date('follow_up_date')->nullable();
            $table->text('sanction_recommendation')->nullable();
            $table->enum('urgency_level', ['low', 'medium', 'high', 'urgent'])->nullable();
            $table->text('president_notes')->nullable();
            $table->boolean('forwarded_to_president')->default(false);
            $table->timestamp('forwarded_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            // Agreed actions fields
            $table->boolean('written_reflection')->default(false);
            $table->date('written_reflection_due')->nullable();
            $table->boolean('mentorship_counseling')->default(false);
            $table->string('mentor_name')->nullable();
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
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('case_meetings');
    }
};

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
        Schema::create('student_violations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('case_meeting_id')->nullable();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('reported_by')->constrained('disciplines')->onDelete('cascade');
            $table->string('violation_type')->nullable(); // e.g., 'late', 'uniform', 'misconduct', 'academic'
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('major_category', ['minor', 'major'])->nullable();
            $table->enum('urgency_level', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('severity', ['minor', 'major', 'severe'])->default('minor');
            $table->date('violation_date');
            $table->time('violation_time')->nullable();
            $table->string('location')->nullable();
            $table->json('witnesses')->nullable(); // Array of witness names
            $table->text('evidence')->nullable(); // Description of evidence
            $table->json('attachments')->nullable(); // File paths for photos/documents
            $table->enum('status', [
                'pending',
                'investigating',
                'in_progress',
                'resolved',
                'dismissed',
                'forwarded',
                'scheduled',
                'pre_completed',
                'case_closed',
                'submitted'
            ])->default('pending');
            $table->text('sanction')->nullable();
            $table->text('resolution')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('disciplines')->onDelete('set null');
            $table->date('resolved_at')->nullable();
            $table->text('student_statement')->nullable();
            $table->text('incident_feelings')->nullable();
            $table->text('action_plan')->nullable();
            $table->text('disciplinary_action')->nullable();
            $table->boolean('parent_notified')->default(false);
            $table->date('parent_notification_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['student_id', 'violation_date']);
            $table->index(['status', 'severity']);
            $table->index('violation_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_violations');
    }
};

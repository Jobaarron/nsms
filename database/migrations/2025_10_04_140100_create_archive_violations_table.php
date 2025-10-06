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
        Schema::create('archive_violations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('reported_by')->constrained('disciplines')->onDelete('cascade');
            $table->string('violation_type')->nullable(); // e.g., 'late', 'uniform', 'misconduct', 'academic'
            $table->string('title');
            $table->text('description');
            $table->enum('severity', ['minor', 'major', 'severe'])->default('minor');
            $table->string('major_category')->nullable();
            $table->text('sanction')->nullable();
            $table->date('violation_date');
            $table->time('violation_time')->nullable();
            $table->string('location')->nullable();
            $table->json('witnesses')->nullable(); // Array of witness names
            $table->text('evidence')->nullable(); // Description of evidence
            $table->json('attachments')->nullable(); // File paths for photos/documents
            $table->enum('status', ['pending', 'investigating', 'resolved', 'dismissed', 'forwarded'])->default('pending');
            $table->text('resolution')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('disciplines')->onDelete('set null');
            $table->date('resolved_at')->nullable();
            $table->text('student_statement')->nullable();
            $table->text('disciplinary_action')->nullable();
            $table->boolean('parent_notified')->default(false);
            $table->date('parent_notification_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('archived_at')->nullable(); // When this violation was archived
            $table->string('archive_reason')->nullable(); // Reason for archiving (e.g., 'escalation_to_major')
            $table->timestamps();
            
            // Indexes
            $table->index(['student_id', 'violation_date']);
            $table->index(['status', 'severity']);
            $table->index('violation_type');
            $table->index('archived_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archive_violations');
    }
};

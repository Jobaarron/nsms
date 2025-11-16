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
        Schema::create('archived_counseling_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('original_session_id')->nullable();
            $table->text('counseling_summary_report')->nullable();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('counselor_id');
            $table->unsignedBigInteger('recommended_by')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('frequency')->nullable();
            $table->integer('time_limit')->nullable();
            $table->time('time')->nullable();
            $table->integer('session_no')->nullable();
            $table->string('status')->default('completed');
            $table->json('referral_academic')->nullable();
            $table->text('referral_academic_other')->nullable();
            $table->json('referral_social')->nullable();
            $table->text('referral_social_other')->nullable();
            $table->text('incident_description')->nullable();
            
            // Archive-specific fields
            $table->timestamp('archived_at');
            $table->string('archive_reason')->default('completed');
            $table->text('archive_notes')->nullable();
            $table->unsignedBigInteger('archived_by')->nullable();
            
            // Snapshot fields for historical data
            $table->string('student_name')->nullable();
            $table->string('student_id_number')->nullable();
            $table->string('counselor_name')->nullable();
            $table->string('recommended_by_name')->nullable();
            $table->timestamp('original_created_at')->nullable();
            $table->timestamp('original_updated_at')->nullable();
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['student_id', 'archived_at']);
            $table->index(['counselor_id', 'archived_at']);
            $table->index(['status', 'archived_at']);
            $table->index('archive_reason');
            $table->index('original_session_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archived_counseling_sessions');
    }
};

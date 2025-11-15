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
        Schema::create('counseling_sessions', function (Blueprint $table) {
            $table->id();
            $table->text('counseling_summary_report')->nullable();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('counselor_id')->nullable()->constrained('guidances')->onDelete('set null');
            $table->foreignId('recommended_by')->nullable()->constrained('users')->onDelete('set null');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('frequency')->nullable();
            $table->integer('time_limit')->nullable();
            $table->time('time')->nullable();
            $table->integer('session_no')->nullable();
            $table->enum('status', ['scheduled', 'completed', 'cancelled', 'rescheduled', 'recommended', 'rejected'])->default('scheduled');
            $table->json('referral_academic')->nullable();
            $table->string('referral_academic_other')->nullable();
            $table->json('referral_social')->nullable();
            $table->string('referral_social_other')->nullable();
            $table->text('incident_description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('counseling_sessions');
    }
};

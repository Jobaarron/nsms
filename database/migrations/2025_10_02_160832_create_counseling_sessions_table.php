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
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('counselor_id')->nullable()->constrained('guidances')->onDelete('set null');
            $table->foreignId('recommended_by')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('session_type', ['individual', 'group', 'family', 'career'])->default('individual');
            $table->date('scheduled_date')->nullable();
            $table->time('scheduled_time')->nullable();
            $table->integer('duration')->default(60); // Duration in minutes
            $table->string('location')->nullable();
            $table->text('reason');
            $table->text('notes')->nullable();
            $table->enum('status', ['scheduled', 'completed', 'cancelled', 'rescheduled', 'recommended'])->default('scheduled');
            $table->text('session_summary')->nullable();
            $table->text('student_progress')->nullable();
            $table->text('goals_achieved')->nullable();
            $table->text('next_steps')->nullable();
            $table->boolean('follow_up_required')->default(false);
            $table->date('follow_up_date')->nullable();
            $table->timestamp('completed_at')->nullable();
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

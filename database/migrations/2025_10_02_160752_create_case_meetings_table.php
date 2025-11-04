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
            $table->foreignId('counselor_id')->constrained('guidances')->onDelete('cascade');
            $table->enum('meeting_type', ['case_meeting', 'house_visit'])->default('case_meeting');
            $table->date('scheduled_date')->nullable();
            $table->time('scheduled_time')->nullable();
            $table->string('location')->nullable();
            $table->text('reason');
            $table->text('notes')->nullable();
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

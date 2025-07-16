<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->string('academic_year');
            $table->string('grade_level');
            $table->string('strand')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('processed_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            
            // One enrollment per student per academic year
            $table->unique(['student_id', 'academic_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};

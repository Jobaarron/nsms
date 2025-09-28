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
        Schema::table('students', function (Blueprint $table) {
            // Add fields needed for pre-registration system
            $table->string('track')->nullable()->after('strand'); // Academic, TVL, Sports, Arts & Design
            $table->enum('student_type', ['new', 'transferee', 'returnee', 'continuing'])->default('new')->after('section');
            $table->enum('enrollment_status', ['pre_registered', 'enrolled', 'dropped', 'graduated'])->default('pre_registered')->after('student_type');
            $table->string('academic_year')->default('2024-2025')->after('enrollment_status');
            $table->json('documents')->nullable()->after('academic_year'); // Store enrollment documents reference
            $table->string('full_name')->nullable()->after('suffix'); // Computed full name
            $table->text('id_photo_data_url')->nullable()->after('id_photo_mime_type'); // For displaying photos
            $table->foreignId('enrollee_id')->nullable()->constrained('enrollees')->onDelete('set null')->after('user_id'); // Link to original enrollee
            $table->timestamp('pre_registered_at')->nullable()->after('updated_at'); // When pre-registration was completed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'track',
                'student_type', 
                'enrollment_status',
                'academic_year',
                'documents',
                'full_name',
                'id_photo_data_url',
                'pre_registered_at'
            ]);
            $table->dropForeign(['enrollee_id']);
            $table->dropColumn('enrollee_id');
        });
    }
};

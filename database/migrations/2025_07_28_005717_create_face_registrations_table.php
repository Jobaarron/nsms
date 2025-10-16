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
        Schema::create('face_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->nullable()->constrained('students')->onDelete('cascade');
            $table->longText('face_encoding')->nullable(); // Store the face encoding/embedding as JSON
            $table->longText('face_image_data')->nullable(); // Store face image as base64 (processed from ID photo)
            $table->string('face_image_mime_type')->nullable(); // MIME type of processed face image
            $table->float('confidence_score')->nullable()->default(0.0); // Confidence score of the face encoding
            $table->json('face_landmarks')->nullable(); // Store face landmarks as JSON
            $table->string('source', 100)->nullable()->default('id_photo'); // Source of face data
            $table->timestamp('registered_at')->nullable()->useCurrent();
            $table->foreignId('registered_by')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('is_active')->nullable()->default(true);
            $table->string('device_id')->nullable(); // Device used for registration
            // $table->json('metadata')->nullable(); // Additional metadata
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['student_id', 'is_active']);
            $table->index('registered_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('face_registrations');
    }
};

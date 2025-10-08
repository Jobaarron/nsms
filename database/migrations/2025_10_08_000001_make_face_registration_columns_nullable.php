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
        Schema::table('face_registrations', function (Blueprint $table) {
            $table->foreignId('student_id')->nullable()->change();
            $table->longText('face_encoding')->nullable()->change();
            $table->longText('face_image_data')->nullable()->change();
            $table->string('face_image_mime_type')->nullable()->change();
            $table->float('confidence_score')->nullable()->change();
            $table->json('face_landmarks')->nullable()->change();
            $table->string('source', 100)->nullable()->change();
            $table->timestamp('registered_at')->nullable()->change();
            $table->foreignId('registered_by')->nullable()->change();
            $table->boolean('is_active')->nullable()->change();
            $table->string('device_id')->nullable()->change();
            // Removed $table->timestamps(0); to avoid duplicate columns
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('face_registrations', function (Blueprint $table) {
            $table->foreignId('student_id')->nullable(false)->change();
            $table->longText('face_encoding')->nullable(false)->change();
            $table->longText('face_image_data')->nullable()->change();
            $table->string('face_image_mime_type')->nullable()->change();
            $table->float('confidence_score')->nullable(false)->change();
            $table->json('face_landmarks')->nullable()->change();
            $table->string('source', 100)->nullable(false)->change();
            $table->timestamp('registered_at')->nullable(false)->change();
            $table->foreignId('registered_by')->nullable()->change();
            $table->boolean('is_active')->nullable(false)->change();
            $table->string('device_id')->nullable()->change();
            // Removed $table->timestamps(0); to avoid duplicate columns during rollback
        });
    }
};

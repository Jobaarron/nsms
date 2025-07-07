<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            // file paths
            $table->string('id_photo');
            $table->json('documents');
            // personal info
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->date('dob');
            $table->string('religion')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->text('address');
            // grade & strand
            $table->string('grade_applied');
            $table->string('strand')->nullable();
            // guardian
            $table->string('guardian_name');
            $table->string('guardian_contact');
            // last school
            $table->enum('last_school_type', ['Public','Private'])->nullable();
            $table->string('last_school_name')->nullable();
            // medical
            $table->text('medical_history')->nullable();
            // payment & scheduling
            $table->string('payment_mode');
            $table->boolean('is_paid')->default(false);
            $table->date('preferred_schedule')->nullable();
            // timestamps
            $table->timestamps();
            
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
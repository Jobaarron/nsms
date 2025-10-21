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
        Schema::create('guidances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('employee_id')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone_number')->nullable();
            $table->text('address')->nullable();
            $table->string('position')->nullable(); // Guidance Counselor, Head Counselor
            $table->date('hire_date')->nullable();
            $table->text('qualifications')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->enum('emergency_contact_relationship', ['spouse', 'parent', 'sibling', 'child', 'friend', 'other'])->nullable();
            $table->text('notes')->nullable();
            $table->enum('specialization', ['guidance_counselor', 'head_counselor', 'career_counselor'])->default('guidance_counselor');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guidances');
    }
};

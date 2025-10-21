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
        Schema::create('guidance_discipline', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('employee_id')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone_number')->nullable();
            $table->text('address')->nullable();
            $table->string('position')->nullable();
            $table->string('specialization')->nullable()->comment('Staff specialization or area of expertise');
            $table->enum('type', ['guidance', 'discipline'])->default('guidance')->comment('Type of staff - guidance or discipline');
            $table->date('hire_date')->nullable();
            $table->text('qualifications')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->enum('emergency_contact_relationship', ['spouse', 'parent', 'sibling', 'child', 'friend', 'other'])->nullable();
            $table->text('notes')->nullable();
            $table->enum('department', ['guidance', 'discipline', 'security'])->default('guidance');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guidance_discipline');
    }
};

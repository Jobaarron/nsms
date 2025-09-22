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
        Schema::create('fees', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., 'Entrance Fee', 'Tuition Fee', 'Miscellaneous Fee'
            $table->text('description')->nullable();
            $table->decimal('amount', 10, 2); // Amount in PHP
            $table->string('academic_year'); // e.g., '2024-2025'
            
            // Grade level and educational level categorization
            $table->json('applicable_grades')->nullable(); // JSON array of applicable grades
            $table->enum('educational_level', ['preschool', 'elementary', 'junior_high', 'senior_high'])->nullable();
            $table->enum('fee_category', ['entrance', 'tuition', 'miscellaneous', 'laboratory', 'library', 'other'])->default('other');
            
            // Payment scheduling options
            $table->enum('payment_schedule', ['full_payment', 'pay_separate', 'pay_before_exam', 'monthly', 'quarterly'])->default('full_payment');
            $table->boolean('is_required')->default(true); // Whether this fee is mandatory
            $table->integer('payment_order')->default(1); // Order of payment priority
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['educational_level', 'fee_category', 'academic_year']);
            $table->index(['is_active', 'is_required']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fees');
    }
};

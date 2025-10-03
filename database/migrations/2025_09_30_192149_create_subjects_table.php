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
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            
            // Basic subject information
            $table->string('subject_code', 20)->unique(); // e.g., ENG7, MATH8, SCI11-STEM
            $table->string('subject_name', 100); // e.g., English 7, Mathematics 8, Earth Science
            
            // Grade level (get from student/enrollee record)
            $table->string('grade_level', 20); // Kinder, Grade 1-12
            
            // Senior High School specific (null for other levels)
            $table->string('strand', 20)->nullable(); // STEM, ABM, HUMSS, TVL
            $table->string('track', 20)->nullable(); // ICT, HE (for TVL only)
            
            // Status
            $table->boolean('is_active')->default(true);
            $table->string('academic_year', 10)->default(date('Y') . '-' . (date('Y') + 1));
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['grade_level', 'strand', 'track']);
            $table->index(['academic_year', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};

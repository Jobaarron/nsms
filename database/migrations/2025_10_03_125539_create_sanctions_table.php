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
        Schema::create('sanctions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('violation_id')->constrained('violations')->onDelete('cascade');
            $table->string('severity'); // minor, major
            $table->string('category')->nullable(); // for major offenses: Category 1, 2, 3
            $table->text('sanction'); // The sanction text
            $table->string('deportment_grade_action'); // e.g., "No change", "Lowered by one step", "Needs Improvement (NI)"
            $table->string('suspension'); // e.g., "None", "3-5 days", "Dismissal/Expulsion"
            $table->text('notes')->nullable(); // Additional notes about the sanction
            $table->boolean('is_automatic')->default(true); // Whether this was automatically calculated
            $table->boolean('is_approved')->default(false); // Whether the sanction has been approved
            $table->foreignId('approved_by')->nullable()->constrained('guidance_discipline')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['violation_id', 'is_approved']);
            $table->index(['severity', 'category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sanctions');
    }
};

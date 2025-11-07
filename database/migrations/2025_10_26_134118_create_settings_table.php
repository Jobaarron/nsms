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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // Setting key (e.g., 'grade_submission_active')
            $table->text('value')->nullable(); // Setting value (JSON or string)
            $table->string('type')->default('string'); // Data type: string, boolean, json, integer
            $table->text('description')->nullable(); // Human-readable description
            $table->string('group')->default('general'); // Setting group for organization
            $table->timestamps();
            
            $table->index(['key', 'group']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};

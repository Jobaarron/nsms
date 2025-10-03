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
        Schema::create('violation_lists', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('severity', ['minor', 'major', 'severe']);
            $table->string('category')->nullable(); // For major offenses: 1, 2, 3
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('violation_lists');
    }
};

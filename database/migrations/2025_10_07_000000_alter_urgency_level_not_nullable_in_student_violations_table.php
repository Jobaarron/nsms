<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing null or invalid values to 'medium'
        DB::table('student_violations')->whereNull('urgency_level')->orWhere('urgency_level', '')->update(['urgency_level' => 'medium']);

        Schema::table('student_violations', function (Blueprint $table) {
            $table->enum('urgency_level', ['low', 'medium', 'high', 'urgent'])->default('medium')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_violations', function (Blueprint $table) {
            $table->enum('urgency_level', ['low', 'medium', 'high', 'urgent'])->nullable()->change();
        });
    }
};

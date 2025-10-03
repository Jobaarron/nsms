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
        // Drop existing foreign keys if they exist
        DB::statement('ALTER TABLE student_violations DROP FOREIGN KEY IF EXISTS violations_reported_by_foreign');
        DB::statement('ALTER TABLE student_violations DROP FOREIGN KEY IF EXISTS violations_resolved_by_foreign');

        Schema::table('student_violations', function (Blueprint $table) {
            $table->foreign('reported_by')->references('id')->on('disciplines')->onDelete('cascade');
            $table->foreign('resolved_by')->references('id')->on('disciplines')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop existing foreign keys if they exist
        DB::statement('ALTER TABLE student_violations DROP FOREIGN KEY IF EXISTS violations_reported_by_foreign');
        DB::statement('ALTER TABLE student_violations DROP FOREIGN KEY IF EXISTS violations_resolved_by_foreign');

        Schema::table('student_violations', function (Blueprint $table) {
            $table->foreign('reported_by')->references('id')->on('guidance_discipline')->onDelete('cascade');
            $table->foreign('resolved_by')->references('id')->on('guidance_discipline')->onDelete('set null');
        });
    }
};

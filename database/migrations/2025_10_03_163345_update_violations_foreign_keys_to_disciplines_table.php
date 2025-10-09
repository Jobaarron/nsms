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
        // Drop old foreign key and add new one to disciplines
        Schema::table('student_violations', function (Blueprint $table) {
            try {
                $table->dropForeign('violations_reported_by_foreign');
            } catch (\Exception $e) {}
            $table->foreign('reported_by')->references('id')->on('disciplines')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to guidance_discipline
        Schema::table('student_violations', function (Blueprint $table) {
            try {
                $table->dropForeign('violations_reported_by_foreign');
            } catch (\Exception $e) {}
            $table->foreign('reported_by')->references('id')->on('guidance_discipline')->onDelete('cascade');
        });
    }
};

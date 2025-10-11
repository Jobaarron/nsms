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
        Schema::table('student_violations', function (Blueprint $table) {
            $table->unsignedBigInteger('case_meeting_id')->nullable()->after('id');
            // Uncomment the next line if you want a foreign key constraint:
            // $table->foreign('case_meeting_id')->references('id')->on('case_meetings')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_violations', function (Blueprint $table) {
            $table->dropColumn('case_meeting_id');
        });
    }
};

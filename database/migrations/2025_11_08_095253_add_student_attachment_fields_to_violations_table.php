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
            $table->string('student_attachment_path')->nullable()->after('action_plan');
            $table->text('student_attachment_description')->nullable()->after('student_attachment_path');
            $table->timestamp('student_attachment_uploaded_at')->nullable()->after('student_attachment_description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_violations', function (Blueprint $table) {
            $table->dropColumn(['student_attachment_path', 'student_attachment_description', 'student_attachment_uploaded_at']);
        });
    }
};

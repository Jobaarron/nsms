<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('case_meetings', function (Blueprint $table) {
            $table->text('teacher_statement')->nullable()->after('notes');
            $table->text('action_plan')->nullable()->after('teacher_statement');
        });
    }

    public function down(): void
    {
        Schema::table('case_meetings', function (Blueprint $table) {
            $table->dropColumn('teacher_statement');
            $table->dropColumn('action_plan');
        });
    }
};

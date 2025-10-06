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
            if (!Schema::hasColumn('student_violations', 'major_category')) {
                $table->string('major_category')->nullable()->after('severity');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_violations', function (Blueprint $table) {
            if (Schema::hasColumn('student_violations', 'major_category')) {
                $table->dropColumn('major_category');
            }
        });
    }
};

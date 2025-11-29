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
            $table->boolean('is_escalated')->default(false)->after('status');
            $table->text('escalation_reason')->nullable()->after('is_escalated');
            $table->integer('occurrence_count')->default(1)->after('escalation_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_violations', function (Blueprint $table) {
            $table->dropColumn(['is_escalated', 'escalation_reason', 'occurrence_count']);
        });
    }
};

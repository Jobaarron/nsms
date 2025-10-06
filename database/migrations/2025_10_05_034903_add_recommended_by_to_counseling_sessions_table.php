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
        Schema::table('counseling_sessions', function (Blueprint $table) {
            if (!Schema::hasColumn('counseling_sessions', 'recommended_by')) {
                // Drop existing foreign key
                $table->dropForeign(['counselor_id']);
                // Make counselor_id nullable
                $table->unsignedBigInteger('counselor_id')->nullable()->change();
                // Add back the foreign key
                $table->foreign('counselor_id')->references('id')->on('guidances')->onDelete('set null');
                // Add recommended_by field
                $table->foreignId('recommended_by')->nullable()->constrained('users')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('counseling_sessions', function (Blueprint $table) {
            $table->dropForeign(['recommended_by']);
            $table->dropColumn('recommended_by');
            // Drop foreign key and make not nullable
            $table->dropForeign(['counselor_id']);
            $table->unsignedBigInteger('counselor_id')->nullable(false)->change();
            $table->foreign('counselor_id')->references('id')->on('guidances')->onDelete('cascade');
        });
    }
};

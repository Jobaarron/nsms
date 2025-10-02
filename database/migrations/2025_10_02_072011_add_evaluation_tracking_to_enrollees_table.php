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
        Schema::table('enrollees', function (Blueprint $table) {
            // Track when registrar actually views and evaluates the application
            $table->timestamp('first_viewed_at')->nullable()->after('approved_at');
            $table->foreignId('first_viewed_by')->nullable()->constrained('registrars')->onDelete('set null')->after('first_viewed_at');
            $table->timestamp('evaluation_started_at')->nullable()->after('first_viewed_by');
            $table->foreignId('evaluation_started_by')->nullable()->constrained('registrars')->onDelete('set null')->after('evaluation_started_at');
            $table->timestamp('evaluation_completed_at')->nullable()->after('evaluation_started_by');
            $table->foreignId('evaluation_completed_by')->nullable()->constrained('registrars')->onDelete('set null')->after('evaluation_completed_at');
            
            // Track document review progress
            $table->integer('documents_reviewed_count')->default(0)->after('evaluation_completed_by');
            $table->integer('documents_total_count')->default(0)->after('documents_reviewed_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollees', function (Blueprint $table) {
            $table->dropForeign(['first_viewed_by']);
            $table->dropForeign(['evaluation_started_by']);
            $table->dropForeign(['evaluation_completed_by']);
            $table->dropColumn([
                'first_viewed_at',
                'first_viewed_by',
                'evaluation_started_at',
                'evaluation_started_by',
                'evaluation_completed_at',
                'evaluation_completed_by',
                'documents_reviewed_count',
                'documents_total_count'
            ]);
        });
    }
};

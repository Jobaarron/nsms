<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // These fields have been moved to the enrollees table
        // This migration is no longer needed as enrollment status tracking
        // is now handled in the enrollees table, not the students table
        
        // Schema::table('students', function (Blueprint $table) {
        //     // Add columns if they don't exist
        //     if (!Schema::hasColumn('students', 'rejected_by')) {
        //         $table->unsignedBigInteger('rejected_by')->nullable()->after('approved_by');
        //     }
        //     if (!Schema::hasColumn('students', 'rejected_at')) {
        //         $table->timestamp('rejected_at')->nullable()->after('approved_at');
        //     }
        //     if (!Schema::hasColumn('students', 'status_updated_at')) {
        //         $table->timestamp('status_updated_at')->nullable()->after('rejected_at');
        //     }
        //     if (!Schema::hasColumn('students', 'status_updated_by')) {
        //         $table->unsignedBigInteger('status_updated_by')->nullable()->after('status_updated_at');
        //     }
        //     if (!Schema::hasColumn('students', 'status_reason')) {
        //         $table->text('status_reason')->nullable()->after('status_updated_by');
        //     }
        //     
        //     // Add foreign key constraints
        //     $table->foreign('rejected_by')->references('id')->on('users')->onDelete('set null');
        //     $table->foreign('status_updated_by')->references('id')->on('users')->onDelete('set null');
        // });
    }

    public function down()
    {
        // No rollback needed since no changes were made in up()
        // Schema::table('students', function (Blueprint $table) {
        //     $table->dropForeign(['rejected_by']);
        //     $table->dropForeign(['status_updated_by']);
        //     $table->dropColumn([
        //         'rejected_by',
        //         'rejected_at', 
        //         'status_updated_at',
        //         'status_updated_by',
        //         'status_reason'
        //     ]);
        // });
    }
};

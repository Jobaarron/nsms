<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the old foreign key constraint
        Schema::table('enrollees', function (Blueprint $table) {
            $table->dropForeign(['processed_by']);
        });

        // Add the new foreign key constraint pointing to registrars table
        Schema::table('enrollees', function (Blueprint $table) {
            $table->foreignId('processed_by')->nullable()->change()->constrained('registrars')->onDelete('set null');
        });
    }

    public function down(): void
    {
        // Revert to the old foreign key constraint
        Schema::table('enrollees', function (Blueprint $table) {
            $table->dropForeign(['processed_by']);
        });

        Schema::table('enrollees', function (Blueprint $table) {
            $table->foreignId('processed_by')->nullable()->change()->constrained('users')->onDelete('set null');
        });
    }
};

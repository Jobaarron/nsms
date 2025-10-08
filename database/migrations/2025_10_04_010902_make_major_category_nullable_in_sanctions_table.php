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
        Schema::table('sanctions', function (Blueprint $table) {
            $table->string('major_category')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First, update any NULL values to a default value before making the column NOT NULL
        DB::table('sanctions')->whereNull('major_category')->update(['major_category' => '']);
        
        Schema::table('sanctions', function (Blueprint $table) {
            $table->string('major_category')->nullable(false)->change();
        });
    }
};

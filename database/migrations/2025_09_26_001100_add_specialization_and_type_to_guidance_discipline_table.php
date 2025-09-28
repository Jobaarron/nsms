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
        Schema::table('guidance_discipline', function (Blueprint $table) {
            $table->string('specialization')->nullable()->after('position')->comment('Staff specialization or area of expertise');
            $table->enum('type', ['guidance', 'discipline'])->default('guidance')->after('specialization')->comment('Type of staff - guidance or discipline');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guidance_discipline', function (Blueprint $table) {
            $table->dropColumn(['specialization', 'type']);
        });
    }
};

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
        Schema::table('payments', function (Blueprint $table) {
            // Add fee breakdown columns
            $table->decimal('entrance_fee', 10, 2)->nullable()->after('amount');
            $table->decimal('miscellaneous_fee', 10, 2)->nullable()->after('entrance_fee');
            $table->decimal('tuition_fee', 10, 2)->nullable()->after('miscellaneous_fee');
            $table->decimal('others_fee', 10, 2)->nullable()->after('tuition_fee');
            $table->decimal('total_fee', 10, 2)->nullable()->after('others_fee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['entrance_fee', 'miscellaneous_fee', 'tuition_fee', 'others_fee', 'total_fee']);
        });
    }
};

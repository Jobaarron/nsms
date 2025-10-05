<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::table('sanctions', function (Blueprint $table) {
        $table->foreignId('case_meeting_id')->nullable()->constrained('case_meetings')->onDelete('cascade')->after('id');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down()
{
    Schema::table('sanctions', function (Blueprint $table) {
        $table->dropConstrainedForeignId('case_meeting_id');
    });
}
};

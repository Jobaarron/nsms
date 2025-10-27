<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('case_meetings', function (Blueprint $table) {
            $table->unsignedBigInteger('violation_id')->nullable()->after('student_id');
            $table->foreign('violation_id')->references('id')->on('student_violations')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('case_meetings', function (Blueprint $table) {
            $table->dropForeign(['violation_id']);
            $table->dropColumn('violation_id');
        });
    }
};

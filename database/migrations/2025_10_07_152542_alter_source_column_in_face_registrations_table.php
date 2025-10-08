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
    Schema::table('face_registrations', function (Blueprint $table) {
        $table->string('source', 100)->change();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down()
{
    Schema::table('face_registrations', function (Blueprint $table) {
        $table->string('source', 20)->change(); // revert if needed
    });
}
};

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
    Schema::table('results', function (Blueprint $table) {
        $table->string('grade')->nullable()->change();
    });
}

public function down()
{
    Schema::table('results', function (Blueprint $table) {
        $table->string('grade')->nullable(false)->change();
    });
}

};

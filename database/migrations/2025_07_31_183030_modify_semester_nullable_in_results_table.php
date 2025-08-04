<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifySemesterNullableInResultsTable extends Migration
{
    public function up()
    {
        Schema::table('results', function (Blueprint $table) {
            $table->string('semester')->nullable()->change(); // allow nulls
        });
    }

    public function down()
    {
        Schema::table('results', function (Blueprint $table) {
            $table->string('semester')->nullable(false)->change(); // rollback
        });
    }
}

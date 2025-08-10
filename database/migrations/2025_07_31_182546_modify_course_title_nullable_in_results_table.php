<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyCourseTitleNullableInResultsTable extends Migration
{
    public function up()
    {
        Schema::table('results', function (Blueprint $table) {
            $table->string('course_title')->nullable();
        });
    }

    public function down()
    {
        Schema::table('results', function (Blueprint $table) {
            $table->string('course_title')->nullable(false);
        });
    }
}

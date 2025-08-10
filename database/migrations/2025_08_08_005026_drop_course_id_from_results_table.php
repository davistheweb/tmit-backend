<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
     public function up()
    {
        Schema::table('results', function (Blueprint $table) {
            // Drop foreign key constraint by its name
            $table->dropForeign('results_course_id_foreign'); // usually `{table}_{column}_foreign`

            // Now drop the column
            $table->dropColumn('course_id');
        });
    }

    public function down()
    {
        Schema::table('results', function (Blueprint $table) {
            $table->unsignedBigInteger('course_id')->nullable();

            // If needed, recreate foreign key in down method
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
        });
    }
};

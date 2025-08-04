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
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->string('reg_number')->after('student_id'); 
            $table->string('email')->after('reg_number');
        });
    }

    public function down()
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('reg_number')->after('student_id'); 
            $table->string('email')->after('reg_number');
        });
    }
};

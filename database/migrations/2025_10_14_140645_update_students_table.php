<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'department_id')) {
                $table->foreignId('department_id')->nullable()->constrained()->onDelete('cascade');
            }
            if (!Schema::hasColumn('students', 'current_level')) {
                $table->integer('current_level')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn(['department_id', 'current_level']);
        });
    }
};

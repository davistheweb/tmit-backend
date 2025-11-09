<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'acceptance_fee_paid')) {
                $table->boolean('acceptance_fee_paid')->default(false)->after('current_level');
            }
            if (!Schema::hasColumn('students', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('acceptance_fee_paid');
            }
        });
    }

    public function down()
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['acceptance_fee_paid', 'is_active']);
        });
    }
};

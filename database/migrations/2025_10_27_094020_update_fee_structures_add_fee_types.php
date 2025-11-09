<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('fee_structures', function (Blueprint $table) {
            if (!Schema::hasColumn('fee_structures', 'fee_type')) {
                $table->enum('fee_type', ['school', 'acceptance', 'hostel'])
                    ->default('school')
                    ->after('session_id');
            }
            if (!Schema::hasColumn('fee_structures', 'is_mandatory')) {
                $table->boolean('is_mandatory')->default(true)->after('allow_installment');
            }
        });
    }

    public function down()
    {
        Schema::table('fee_structures', function (Blueprint $table) {
            $table->dropColumn(['fee_type', 'is_mandatory']);
        });
    }
};

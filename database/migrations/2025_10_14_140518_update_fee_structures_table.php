<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('fee_structures', function (Blueprint $table) {
            // Add new columns if they don't exist
            if (!Schema::hasColumn('fee_structures', 'session_id')) {
                $table->foreignId('session_id')
                      ->nullable()
                      ->constrained('school_sessions') // ✅ fixed table name
                      ->onDelete('cascade');
            }

            if (!Schema::hasColumn('fee_structures', 'department_id')) {
                $table->foreignId('department_id')
                      ->nullable()
                      ->constrained('departments') // ✅ ensure this matches your actual table name
                      ->onDelete('cascade');
            }

            if (!Schema::hasColumn('fee_structures', 'level')) {
                $table->integer('level')->nullable(); // 100, 200, 300, etc.
            }

            if (!Schema::hasColumn('fee_structures', 'installment_first')) {
                $table->decimal('installment_first', 10, 2)->nullable();
            }

            if (!Schema::hasColumn('fee_structures', 'installment_second')) {
                $table->decimal('installment_second', 10, 2)->nullable();
            }

            if (!Schema::hasColumn('fee_structures', 'allow_installment')) {
                $table->boolean('allow_installment')->default(true);
            }
        });
    }

    public function down()
    {
        Schema::table('fee_structures', function (Blueprint $table) {
            // Drop FKs safely
            if (Schema::hasColumn('fee_structures', 'session_id')) {
                $table->dropForeign(['session_id']);
                $table->dropColumn('session_id');
            }

            if (Schema::hasColumn('fee_structures', 'department_id')) {
                $table->dropForeign(['department_id']);
                $table->dropColumn('department_id');
            }

            $table->dropColumn(['level', 'installment_first', 'installment_second', 'allow_installment']);
        });
    }
};

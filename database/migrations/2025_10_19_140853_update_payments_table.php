<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'session_id')) {
                $table->foreignId('session_id')
                      ->nullable()
                      ->constrained('school_sessions')
                      ->onDelete('cascade');
            }

            if (!Schema::hasColumn('payments', 'payment_type')) {
                $table->enum('payment_type', ['full', 'installment_first', 'installment_second'])->nullable();
            }

            // ✅ Only rename if 'payment_method' doesn't exist yet
            if (Schema::hasColumn('payments', 'channel') && !Schema::hasColumn('payments', 'payment_method')) {
                $table->renameColumn('channel', 'payment_method');
            } 
            // ✅ Only add if neither 'channel' nor 'payment_method' exist
            elseif (!Schema::hasColumn('payments', 'channel') && !Schema::hasColumn('payments', 'payment_method')) {
                $table->string('payment_method')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'session_id')) {
                $table->dropForeign(['session_id']);
                $table->dropColumn('session_id');
            }

            if (Schema::hasColumn('payments', 'payment_type')) {
                $table->dropColumn('payment_type');
            }

            if (Schema::hasColumn('payments', 'payment_method')) {
                $table->dropColumn('payment_method');
            }
        });
    }
};

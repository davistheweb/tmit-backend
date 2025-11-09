<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTable extends Migration
{
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->string('reference')->unique(); // internal invoice ref
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->decimal('amount', 12, 2);
            $table->enum('status', ['pending','partial','paid','cancelled'])->default('pending');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('student_id');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('invoices');
    }
}

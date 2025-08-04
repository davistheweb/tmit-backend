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
    Schema::create('courses', function (Blueprint $table) {
        $table->id();
        $table->string('code')->unique(); // e.g. MTH101
        $table->string('title');          // e.g. General Mathematics
        $table->integer('unit');          // e.g. 3
        $table->string('level');          // e.g. 100, 200
        $table->string('semester');       // e.g. First, Second
        $table->foreignId('department_id')->constrained()->onDelete('cascade');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};

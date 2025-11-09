<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
      Schema::create('academic_sessions', function (Blueprint $table) {
    $table->id();
    $table->string('year'); 
    $table->enum('semester', ['First Semester', 'Second Semester']);
    $table->boolean('is_active')->default(false);
    $table->date('start_date')->nullable();
    $table->date('end_date')->nullable();
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_session');
    }
};

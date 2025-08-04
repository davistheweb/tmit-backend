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
        Schema::create('student_profiles', function (Blueprint $table) {
        $table->id();
        $table->foreignId('student_id')->constrained()->onDelete('cascade');

        $table->string('surname');
        $table->string('middle_name')->nullable();
        $table->string('last_name');
        $table->string('gender');
        $table->date('dob');
        $table->string('country');
        $table->string('state');
        $table->string('lga');
        $table->string('home_town');
        $table->string('phone');
        $table->string('nin');
        $table->string('contact_address');
        $table->string('blood_group');
        $table->string('genotype');
        $table->string('religion');
        $table->string('image_path')->nullable();
        $table->string('certifications_path')->nullable();
        $table->string('department');
        $table->string('year');

        $table->timestamps();
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_profiles');
    }
};

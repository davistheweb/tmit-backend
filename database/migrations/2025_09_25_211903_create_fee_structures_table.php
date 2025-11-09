<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('fee_structures', function (Blueprint $table) {
            $table->id();
            $table->string('year_of_study');   // e.g. "Year 1"
            $table->string('session');         // e.g. "2025/2026"
            $table->decimal('amount', 12, 2);  // fees in Naira
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('fee_structures');
    }
};

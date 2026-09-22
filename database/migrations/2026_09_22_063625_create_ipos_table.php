<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipos', function (Blueprint $table) {
            $table->id();
            $table->string('code'); // Contoh: GSM
            $table->string('company_name'); // Contoh: PT Global Sukses Makmur Tbk
            $table->string('status')->default('UPCOMING IPO'); // UPCOMING IPO, BOOKBUILDING, LISTED
            $table->string('offering_date')->nullable(); // Contoh: 12 - 15 Maret 2026
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipos');
    }
};

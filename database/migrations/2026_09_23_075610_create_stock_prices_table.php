<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stock_prices', function (Blueprint $table) {
            $table->id();
            $table->string('stock_code', 10)->index(); // Selaras dengan watchlists
            $table->date('trade_date')->index();
            $table->decimal('open', 12, 2);
            $table->decimal('high', 12, 2);
            $table->decimal('low', 12, 2);
            $table->decimal('close', 12, 2);
            $table->bigInteger('volume');
            $table->timestamps();

            // Mencegah duplikasi data untuk saham dan tanggal yang sama
            $table->unique(['stock_code', 'trade_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_prices');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Menghapus tabel jika sebelumnya dibuat paksa oleh Python
        Schema::dropIfExists('broker_summaries');

        Schema::create('broker_summaries', function (Blueprint $table) {
            $table->id();
            $table->string('stock_code', 20);
            $table->date('trade_date');
            
            // Diperbesar menjadi 150 karakter agar nama sekuritas panjang tidak error
            $table->string('broker_code', 150); 
            
            $table->bigInteger('volume');
            $table->decimal('value', 20, 2);
            $table->bigInteger('frequency');
            $table->timestamps();

            // Mencegah data ganda untuk broker yang sama di saham & tanggal yang sama
            $table->unique(['stock_code', 'trade_date', 'broker_code'], 'broker_summaries_unique');
            
            // Index untuk mempercepat query Top 5 Broker di Chart
            $table->index(['stock_code', 'trade_date'], 'idx_broker_summaries_code_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('broker_summaries');
    }
};
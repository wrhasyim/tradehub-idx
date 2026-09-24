<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('broker_summaries');

        Schema::create('broker_summaries', function (Blueprint $table) {
            $table->id();
            // stock_code maksimal 20, broker_code maksimal 150 agar aman dari data panjang
            $table->string('stock_code', 20);
            $table->date('trade_date');
            $table->string('broker_code', 150); 
            
            // Kolom Asli untuk Kalkulasi Net & Gross
            $table->bigInteger('buy_volume')->default(0);
            $table->bigInteger('sell_volume')->default(0);
            $table->bigInteger('net_volume')->default(0);
            
            $table->decimal('buy_value', 20, 2)->default(0);
            $table->decimal('sell_value', 20, 2)->default(0);
            $table->decimal('net_value', 20, 2)->default(0);
            
            $table->bigInteger('frequency')->default(0);
            $table->timestamps();

            $table->unique(['stock_code', 'trade_date', 'broker_code'], 'broker_summaries_unique');
            $table->index(['stock_code', 'trade_date'], 'idx_broker_summaries_code_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('broker_summaries');
    }
};
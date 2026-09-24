<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_prices', function (Blueprint $table) {
            // Menambahkan kolom JSON untuk menyimpan ringkasan Top Buyer & Seller broker
            $table->json('broksum_data')->nullable()->after('foreign_sell');
        });
    }

    public function down(): void
    {
        Schema::table('stock_prices', function (Blueprint $table) {
            $table->dropColumn('broksum_data');
        });
    }
};
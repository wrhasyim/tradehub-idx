<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('stock_prices', function (Blueprint $table) {
            $table->decimal('change', 8, 2)->nullable();
            $table->decimal('value', 20, 2)->nullable();
            $table->bigInteger('frequency')->nullable();
            $table->decimal('foreign_buy', 20, 2)->nullable();
            $table->decimal('foreign_sell', 20, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('stock_prices', function (Blueprint $table) {
            $table->dropColumn(['change', 'value', 'frequency', 'foreign_buy', 'foreign_sell']);
        });
    }
};

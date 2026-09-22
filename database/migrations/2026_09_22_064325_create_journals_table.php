<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('stock_code');
            $table->enum('position', ['Long', 'Short'])->default('Long');
            $table->decimal('buy_price', 15, 2);
            $table->decimal('sell_price', 15, 2)->nullable();
            $table->integer('lots');
            $table->decimal('pnl_amount', 15, 2)->nullable(); // Nominal untung/rugi
            $table->decimal('pnl_percentage', 5, 2)->nullable(); // Persentase untung/rugi
            $table->text('notes')->nullable();
            $table->date('trade_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journals');
    }
};
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
    Schema::create('watchlists', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->string('stock_code', 10);
        $table->enum('status', ['watching', 'triggered', 'closed'])->default('watching');
        $table->decimal('entry_price', 10, 2)->nullable();
        $table->decimal('target_price', 10, 2)->nullable();
        $table->decimal('stop_loss', 10, 2)->nullable();
        $table->text('ai_analysis_notes')->nullable();
        $table->timestamps();
        
        $table->index(['user_id', 'stock_code']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('watchlists');
    }
};

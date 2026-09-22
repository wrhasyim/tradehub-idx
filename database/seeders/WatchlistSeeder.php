<?php

namespace Database\Seeders;

use App\Models\Watchlist;
use Illuminate\Database\Seeder;

class WatchlistSeeder extends Seeder
{
    public function run(): void
    {
        Watchlist::create([
            'user_id' => 1,
            'stock_code' => 'BRPT',
            'status' => 'watching', // Ubah ke huruf kecil
            'entry_price' => 1050,
            'target_price' => 1200,
            'stop_loss' => 1000,
            'ai_analysis_notes' => 'Volume akumulasi meningkat dalam 3 hari terakhir.'
        ]);

        Watchlist::create([
            'user_id' => 1,
            'stock_code' => 'AMMN',
            'status' => 'triggered', // Ubah ke huruf kecil
            'entry_price' => 8800,
            'target_price' => 9200,
            'stop_loss' => 8500,
            'ai_analysis_notes' => 'MACD Golden Cross, sentimen harga tembaga global positif.'
        ]);
    }
}
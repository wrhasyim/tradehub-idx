<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Journal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'stock_code', 'position', 'buy_price', 
        'sell_price', 'lots', 'pnl_amount', 'pnl_percentage', 
        'notes', 'trade_date'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
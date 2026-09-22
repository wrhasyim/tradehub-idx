<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Watchlist extends Model
{
    protected $fillable = [
        'user_id',
        'stock_code',
        'status',
        'entry_price',
        'target_price',
        'stop_loss',
        'ai_analysis_notes',
    ];

    // Relasi balikan: Watchlist ini milik siapa?
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
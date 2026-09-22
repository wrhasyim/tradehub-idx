<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    protected $fillable = [
        'user_id',
        'plan_name',
        'amount',
        'payment_status',
        'payment_method',
        'started_at',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    // Relasi balikan: Subscription ini milik siapa?
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
<?php

namespace App\Models;

use App\Enums\ConferenceDirection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Conference extends Model
{
    protected $fillable = [
        'name',
        'status',
        'user_id',
        'to',
        'from',
    ];

    protected $casts = [
        'direction' => ConferenceDirection::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

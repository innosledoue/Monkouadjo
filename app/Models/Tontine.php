<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tontine extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'members_count', 'contribution', 'frequency',
        'current_turn', 'start_date', 'status', 'note',
    ];

    protected $casts = [
        'start_date' => 'date',
        'contribution' => 'decimal:2',
        'members_count' => 'integer',
        'current_turn' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * SubSession represents a single guest within a GroupSession.
 * Room and therapist assignments live at the SessionItem level.
 */
class SubSession extends Model
{
    use HasFactory;

    protected $table = 'sub_sessions';

    protected $fillable = [
        'group_session_id',
        'guest_name',
        'phone',
        'status',
        'start_time',
        'end_time',
        'is_checked_out',
        'share_group_bill',
        'notes',
        'order_id', // FK to orders – set after split-bill checkout for this sub-session
    ];

    protected $casts = [
        'id'               => 'integer',
        'group_session_id' => 'integer',
        'order_id'         => 'integer',
        'is_checked_out'   => 'boolean',
        'share_group_bill' => 'boolean',
        'start_time'       => 'datetime',
        'end_time'         => 'datetime',
        'status'           => 'string',
    ];

    // ── Relationships ─────────────────────────────────────────────────────
    public function groupSession(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(GroupSession::class, 'group_session_id');
    }

    public function order(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function sessionItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SessionItem::class, 'sub_session_id');
    }

    // ── Helpers ───────────────────────────────────────────────────────────
    /**
     * Sum of all session item final_prices.
     */
    public function getSubtotalAttribute(): float
    {
        return (float) $this->sessionItems()->sum('final_price');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * SessionItem represents a single service consumed during a SubSession.
 * Room and therapist are assigned here (not on the SubSession).
 */
class SessionItem extends Model
{
    use HasFactory;

    protected $table = 'session_items';

    protected $fillable = [
        'sub_session_id',
        'item_id',
        'quantity',
        'room_id',
        'bed_id',
        'therapist_id',
        'started_at',
        'ended_at',
        'start_time',
        'end_time',
        'started_time',
        'ended_time',
        'duration',
        'price',
        'discount',
        'final_price',
        'status',
        'notes',
    ];

    protected $casts = [
        'id'               => 'integer',
        'branch_id'        => 'integer',
        'session_id'       => 'integer',
        'item_id'          => 'integer',
        'therapist_id'     => 'integer',
        'quantity'         => 'integer',
        'duration_minutes' => 'integer',
        'unit_price'       => 'decimal:2',
        'total_price'      => 'decimal:2',
        'type'             => 'string',
        'started_at'       => 'datetime',
        'ended_at'         => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();
    }

    public function session(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(SubSession::class, 'session_id');
    } 
    // ── Relationships ─────────────────────────────────────────────────────
    public function subSession(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(SubSession::class, 'sub_session_id');
    }

    public function item(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id')->withTrashed();
    }

    public function room(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function bed(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Bed::class, 'bed_id');
    }

    public function therapist(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'therapist_id');
    }
}

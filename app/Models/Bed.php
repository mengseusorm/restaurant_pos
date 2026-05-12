<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\BranchScope;

class Bed extends Model
{
    use HasFactory;

    protected $table = 'beds';

    protected $fillable = [
        'branch_id',
        'room_id',
        'name',
        'status',
    ];

    protected $casts = [
        'id'        => 'integer',
        'branch_id' => 'integer',
        'room_id'   => 'integer',
        'name'      => 'string',
        'status'    => 'string',
    ];

    public function room(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function subSessions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SubSession::class, 'bed_id');
    }

    /**
     * Returns the single active (non-checked-out) sub-session for this bed, if any.
     */
    public function activeSession(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(SubSession::class, 'bed_id')
            ->whereNotIn('status', ['checked_out'])
            ->latest();
    }

    protected static function boot(): void
    {
        parent::boot();
        static::addGlobalScope(new BranchScope());
    }
}

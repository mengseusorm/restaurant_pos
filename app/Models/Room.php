<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Enums\BedStatus;
use App\Models\Scopes\BranchScope;

class Room extends Model
{
    use HasFactory;

    protected $table = 'rooms';

    protected $fillable = [
        'name',
        'branch_id',
        'status',
        'qr_code_token',
    ];

    protected $casts = [
        'id'        => 'integer',
        'branch_id' => 'integer',
        'name'      => 'string',
        'status'    => 'string',
    ];

    public function subSessions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SubSession::class, 'room_id');
    }

    public function sessionItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SessionItem::class, 'room_id');
    }

    public function beds(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Bed::class, 'room_id');
    }

    /**
     * Derive room status from its beds.
     * - All available  → available
     * - All occupied   → occupied
     * - Any cleaning   → cleaning
     * - Mixed          → partially_occupied
     * Falls back to the stored status when no beds exist.
     */
    public function getDerivedStatusAttribute(): string
    {
        $beds = $this->beds;
        if ($beds->isEmpty()) {
            return $this->status ?? 'available';
        }

        $statuses = $beds->pluck('status');

        if ($statuses->contains('cleaning')) {
            return \App\Enums\BedStatus::CLEANING;
        }
        if ($statuses->every(fn($s) => $s === 'available')) {
            return \App\Enums\BedStatus::AVAILABLE;
        }
        if ($statuses->every(fn($s) => $s === 'occupied')) {
            return \App\Enums\RoomStatus::OCCUPIED;
        }

        return 'partially_occupied';
    }

    public function branch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public static function generateQrToken(): string
    {
        do {
            $token = Str::random(32);
        } while (self::where('qr_code_token', $token)->exists());

        return $token;
    }

    protected static function boot(): void
    {
        parent::boot();
        static::addGlobalScope(new BranchScope());
    }
}

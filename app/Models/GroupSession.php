<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\Order;
use App\Enums\GroupSessionStatus;
use App\Enums\PaymentStatus;

/**
 * GroupSession represents one visit.
 * A visit can have 1..n guests (SubSessions).
 * is_group_checkout = true  → single bill for all guests.
 * is_group_checkout = false → split bill per guest.
 */
class GroupSession extends Model
{
    use HasFactory;

    protected $table = 'group_sessions';

    protected $fillable = [
        'code',
        'branch_id',
        'status',
        'arrival_time',
        'end_time',
        'total_guests',
        'is_group_checkout',
        'notes',
    ];

    protected $casts = [
        'id'               => 'integer',
        'branch_id'        => 'integer',
        'total_guests'     => 'integer',
        'is_group_checkout'=> 'boolean',
        'arrival_time'     => 'datetime',
        'end_time'         => 'datetime',
    ];

    // ── Boot ─────────────────────────────────────────────────────────────
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (GroupSession $gs) {
            if (empty($gs->code)) {
                $gs->code = static::generateCode();
            }
        });
    }

    // ── Helpers ───────────────────────────────────────────────────────────
    public static function generateCode(): string
    {
        $prefix = 'GS-' . now()->format('Ymd') . '-';
        $last   = static::where('code', 'like', $prefix . '%')
                        ->orderByDesc('id')
                        ->value('code');

        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Sync total_guests from the actual count of sub-sessions.
     */
    public function syncGuestCount(): void
    {
        $this->updateQuietly([
            'total_guests' => $this->subSessions()->count(),
        ]);
    }

    public function syncStatusFromOrders(): void
    {
        $subSessions = $this->subSessions()->get(['id', 'group_session_id', 'is_checked_out', 'order_id']);
        if ($subSessions->isEmpty() || $subSessions->contains(fn($subSession) => !$subSession->is_checked_out)) {
            return;
        }

        $subSessionOrderIds = $subSessions->pluck('order_id')->filter()->unique()->values();
        $orders = Order::withoutGlobalScopes()
            ->where(function ($query) use ($subSessionOrderIds) {
                $query->where('group_session_id', $this->id);
                if ($subSessionOrderIds->isNotEmpty()) {
                    $query->orWhereIn('id', $subSessionOrderIds);
                }
            })
            ->get(['id', 'payment_status']);

        if ($orders->isEmpty()) {
            return;
        }

        $hasUnpaidOrder = $orders->contains(fn($order) => (int) $order->payment_status !== PaymentStatus::PAID);

        $this->updateQuietly([
            'status'   => $hasUnpaidOrder ? GroupSessionStatus::OPEN : GroupSessionStatus::COMPLETED,
            'end_time' => $hasUnpaidOrder ? $this->end_time : ($this->end_time ?? now()),
        ]);
    }

    // ── Relationships ─────────────────────────────────────────────────────
    public function subSessions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SubSession::class, 'group_session_id');
    }

    public function orders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Order::class, 'group_session_id');
    }
}

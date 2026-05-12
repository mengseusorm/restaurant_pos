<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_code',
        'customer_name',
        'customer_phone',
        'customer_email',
        'reservation_date',
        'reservation_time',
        'number_of_people',
        'table_id',
        'status',
        'special_request',
        'created_by',
        'branch_id',
        'deposit_amount',
        'payment_status',
        'check_in_time',
        'check_out_time',
        'cancel_reason',
        'reminder_sent',
        'duration_minutes',
    ];

    protected $casts = [
        'reservation_date' => 'date',
        'reservation_time' => 'datetime:H:i',
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
        'status' => 'integer',
        'payment_status' => 'integer',
        'reminder_sent' => 'boolean',
        'deposit_amount' => 'decimal:2',
        'number_of_people' => 'integer',
        'duration_minutes' => 'integer',
    ];

    public function table(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(DiningTable::class, 'table_id');
    }

    public function branch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function createdBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Generate unique reservation code
     */
    public static function generateReservationCode($branchId = null)
    {
        $date = date('Ymd');
        $prefix = $branchId ? 'RSV-B' . $branchId . '-' : 'RSV-';
        $lastReservation = self::where('reservation_code', 'like', $prefix . $date . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastReservation) {
            $lastNumber = intval(substr($lastReservation->reservation_code, -3));
            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }

        return $prefix . $date . '-' . $newNumber;
    }
}

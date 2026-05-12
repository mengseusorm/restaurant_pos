<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SessionQueue extends Model
{
    use HasFactory;

    protected $table = 'session_queues';

    protected $fillable = [
        'branch_id',
        'room_id',
        'service_id',
        'therapist_id',
        'customer_name',
        'customer_phone',
        'notes',
        'position',
        'status',
    ];

    protected $casts = [
        'id'           => 'integer',
        'branch_id'    => 'integer',
        'room_id'      => 'integer',
        'service_id'   => 'integer',
        'therapist_id' => 'integer',
        'position'     => 'integer',
        'status'       => 'string',
    ];

    public function room(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function service(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Item::class, 'service_id');
    }

    public function therapist(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'therapist_id');
    }
}

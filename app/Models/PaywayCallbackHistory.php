<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaywayCallbackHistory extends Model
{
    use HasFactory;

    protected $table = 'payway_callback_histories';

    protected $fillable = [
        'tran_id',
        'apv',
        'status',
        'merchant_ref_no',
        'raw_payload',
        'ip_address',
        'user_agent',
        'is_processed',
        'processed_at',
        'processing_notes',
    ];

    protected $casts = [
        'apv'          => 'integer',
        'raw_payload'  => 'array',
        'is_processed' => 'boolean',
        'processed_at' => 'datetime',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];

    /**
     * Get the related PayWay transaction.
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(PaywayTransaction::class, 'tran_id', 'tran_id');
    }

    /**
     * Scope: only processed callbacks.
     */
    public function scopeProcessed($query)
    {
        return $query->where('is_processed', true);
    }

    /**
     * Scope: only unprocessed callbacks.
     */
    public function scopeUnprocessed($query)
    {
        return $query->where('is_processed', false);
    }
}

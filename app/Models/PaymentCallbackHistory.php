<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentCallbackHistory extends Model
{
    use HasFactory;

    protected $table = 'payment_callback_histories';

    protected $fillable = [
        'payment_gateway',
        'out_trade_no',
        'transaction_id',
        'status',
        'merchant_id',
        'app_id',
        'callback_url',
        'request_headers',
        'request_data',
        'response_data',
        'response_status',
        'ip_address',
        'user_agent',
        'is_valid',
        'validation_errors',
        'is_processed',
        'processing_errors',
        'callback_received_at',
    ];

    protected $casts = [
        'request_headers' => 'array',
        'request_data' => 'array',
        'response_data' => 'array',
        'response_status' => 'integer',
        'is_valid' => 'boolean',
        'is_processed' => 'boolean',
        'callback_received_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the related order based on out_trade_no
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'out_trade_no', 'order_serial_no');
    }

    /**
     * Get the related payment order
     */
    public function paymentOrder(): BelongsTo
    {
        return $this->belongsTo(PaymentOrder::class, 'out_trade_no', 'transaction_no');
    }

    /**
     * Scope for filtering by payment gateway
     */
    public function scopeByGateway($query, $gateway)
    {
        return $query->where('payment_gateway', $gateway);
    }

    /**
     * Scope for filtering by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for successful callbacks
     */
    public function scopeSuccessful($query)
    {
        return $query->where('is_valid', true)->where('is_processed', true);
    }

    /**
     * Scope for failed callbacks
     */
    public function scopeFailed($query)
    {
        return $query->where(function ($q) {
            $q->where('is_valid', false)->orWhere('is_processed', false);
        });
    }
}

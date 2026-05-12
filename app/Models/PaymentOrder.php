<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentOrder extends Model
{
    use HasFactory;

    protected $table = "payment_orders"; 
    protected $fillable = [
        'order_id',
        'transaction_no',
        'amount',
        'currency',
        'payment_gateway',
        'status',
        'last_placed_at',
        'paid_at',
        'expires_at',
        'payment_requests',  // each request push data in this
        'qr_code_url',
        'gateway_response',
        'refund_requests',   // array of refund request data
        'refund_status',     // current refund status
        'refund_amount',     // total refunded amount
    ];

    protected $casts = [
        'order_id'           => 'integer',
        'transaction_no'     => 'string',
        'amount'             => 'decimal:2',
        'currency'           => 'string',
        'payment_gateway'    => 'string',
        'status'             => 'integer',
        'last_placed_at'     => 'datetime',
        'paid_at'            => 'datetime',
        'expires_at'         => 'datetime',
        'payment_requests'   => 'array',
        'qr_code_url'        => 'string',
        'gateway_response'   => 'array',
        'refund_requests'    => 'array',
        'refund_status'      => 'string',
        'refund_amount'      => 'decimal:2',
    ];
}

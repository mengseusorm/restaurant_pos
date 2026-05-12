<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDeleted extends Model
{
    use HasFactory;

    protected $table = "order_deleteds";
    protected $fillable = [
        'order_serial_no',
        'invoice_number',
        'token',
        'user_id',
        'branch_id',
        'subtotal',
        'discount',
        'delivery_charge',
        'total_tax',
        'total',
        'order_type',
        'order_datetime',
        'delivery_time',
        'preparation_time',
        'is_advance_order',
        'address',
        'payment_method',
        'payment_status',
        'status',
        'dining_table',
        'source',
        'pos_payment_method',
        'pos_payment_note',
        'waiting_number',
        'business_date'
    ];

    protected $casts = [
        'id'               => 'integer',
        'order_serial_no'  => 'string',
        'invoice_number'   => 'string',
        'token'            => 'string',
        'user_id'          => 'integer',
        'branch_id'        => 'integer',
        'subtotal'         => 'decimal:6',
        'discount'         => 'decimal:6',
        'delivery_charge'  => 'decimal:6',
        'total_tax'        => 'decimal:6',
        'total'            => 'decimal:6',
        'order_type'       => 'integer',
        'order_datetime'   => 'datetime',
        'delivery_time'    => 'string',
        'preparation_time' => 'integer',
        'is_advance_order' => 'integer',
        'payment_method'   => 'integer',
        'payment_status'   => 'integer',
        'status'           => 'integer',
        'dining_table'     => 'array',
        'source'           => 'integer',
        'pos_payment_method' => 'integer',
        'pos_payment_note' => 'string',
        'waiting_number'   => 'integer',
        'business_date'    => 'datetime'
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItemDeleted::class, 'order_serial_no', 'order_serial_no');
    }

    public function paymentMethod() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method');
    }
}

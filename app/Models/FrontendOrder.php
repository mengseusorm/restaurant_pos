<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FrontendOrder extends Model
{
    use HasFactory;

    protected $table = "orders";
    protected $fillable = [
        'order_serial_no',
        'token',
        'user_id',
        'branch_id',
        'subtotal',
        'discount',
        'delivery_charge',
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
        'dining_table_id',
        'source',
        'order_note',
        'customer_name',
        'customer_phone_number',
        'customer_address',
        // Telegram Mini App fields
        'telegram_user_id',
        'telegram_chat_id',
        'telegram_username',
        // PayWay transaction fields
        'payment_transaction_id',
        'payment_transaction_data',
        'currency',
        'currency_id',
        'receive_payment_currency',
        'receive_payment_currency_id',
    ];

    protected $casts = [
        'id'               => 'integer',
        'order_serial_no'  => 'string',
        'token'            => 'string',
        'user_id'          => 'integer',
        'branch_id'        => 'integer',
        'subtotal'         => 'decimal:6',
        'discount'         => 'decimal:6',
        'delivery_charge'  => 'decimal:6',
        'total'            => 'decimal:6',
        'order_type'       => 'integer',
        'order_datetime'   => 'datetime',
        'delivery_time'    => 'string',
        'preparation_time' => 'integer',
        'is_advance_order' => 'integer',
        'payment_method'   => 'integer',
        'payment_status'   => 'integer',
        'status'           => 'integer',
        'dining_table_id'  => 'integer',
        'source'           => 'string',
        'customer_name'    => 'string',
        'customer_phone_number' => 'string',
        'customer_address' => 'string',
    ];

    public function orderItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'id');
    }

    public function items(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'order_items');
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function address(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(OrderAddress::class, 'order_id', 'id');
    }

    public function branch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', OrderStatus::PENDING);
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', OrderStatus::PROCESSING);
    }

    public function scopeOutForDelivery($query)
    {
        return $query->where('status', OrderStatus::OUT_FOR_DELIVERY);
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', OrderStatus::DELIVERED);
    }

    public function scopeCanceled($query)
    {
        return $query->where('status', OrderStatus::CANCELED);
    }

    public function scopeReturned($query)
    {
        return $query->where('status', OrderStatus::RETURNED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', OrderStatus::REJECTED);
    }

    public function transaction(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Transaction::class, 'order_id', 'id');
    }

    public function transactions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Transaction::class, 'order_id', 'id');
    }

    public function diningTable(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(FrontendDiningTable::class);
    }

    // Although there is no foreign key constraint in the database, this defines the relation for Eloquent
    public function paymentMethod(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        // 'payment_method' in orders table refers to 'id' in payment_methods table
        return $this->belongsTo(PaymentMethod::class, 'payment_method', 'id');
    }

    public function posPaymentMethod(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        // 'payment_method' in orders table refers to 'id' in payment_methods table
        return $this->belongsTo(PaymentMethod::class, 'pos_payment_method', 'id');
    }
}

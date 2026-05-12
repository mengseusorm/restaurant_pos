<?php

namespace App\Models;

use App\Enums\OrderStatus as OrderStatusEnum;
use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $table = "orders";
    protected $fillable = [
        'order_serial_no',
        'invoice_number',
        'token',
        'user_id',  //this is customer id
        'member_id', // member integration for loyalty points
        'number_of_people', // this is the number of people for dining table orders

        'order_user_id', // this is the user who created the order
        'branch_id',
        'subtotal',
        'discount', // discount = subtotal * discount_percentage / 100
        'discount_percentage',
        'delivery_charge',
        'total_tax',  // total_tax = total_tax * discount_percentage / 100
        'total',     // total = subtotal - discount + delivery_charge
        'paid_amount', // total amount paid in order currency
        'balance_due', // remaining amount to be paid in order currency (total - paid_amount)

        'points_earned', // points earned from this order
        'points_redeemed', // points redeemed/used in this order

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
        'pos_payment_method',
        'pos_payment_note',
        'pos_received_amount',

        'payment_transaction_id',
        'payment_transaction_data',

        'waiting_number',
        'business_date',
        'order_note', // Added order_note field
        // 'payment_method_id', // Assuming this is a foreign key to PaymentMethod
        'currency',
        'currency_id',
        'receive_payment_currency', // Currency customer pays in, defaults to same as order currency
        'receive_payment_currency_id', // This receive payment currency is used to store the currency type that received, default is the same currency_id
        'customer_name',
        'customer_phone_number',
        'customer_address',
        'rejection_reason', // Reason for order rejection
        'rejected_at', // Timestamp when order was rejected
        'telegram_user_id', // Telegram user ID
        'telegram_chat_id', // Telegram chat ID
        'telegram_username', // Telegram username
        'check_in_time', // Time when customer checks in or order is created
        'check_out_time', // Time when customer checks out or order is paid
        'checkout',
        'change_amount', // Change currency must be the same as order price currency.
        'group_session_id', // FK to group_sessions – set when order is created via massage checkout
    ];

    protected $casts = [
        'id'                 => 'integer',
        'order_serial_no'    => 'string',
        'invoice_number'     => 'string',
        'token'              => 'string',
        'user_id'            => 'integer',
        'member_id'          => 'integer',
        'branch_id'          => 'integer',
        'subtotal'           => 'decimal:6',
        'discount'           => 'decimal:6',
        'delivery_charge'    => 'decimal:6',
        'total_tax'          => 'decimal:6',
        'total'              => 'decimal:6',
        'paid_amount'        => 'decimal:6',
        'balance_due'        => 'decimal:6',
        'points_earned'      => 'integer',
        'points_redeemed'    => 'integer',
        'order_type'         => 'integer',
        'order_datetime'     => 'datetime',
        'delivery_time'      => 'string',
        'preparation_time'   => 'integer',
        'is_advance_order'   => 'integer',
        'payment_method'     => 'integer',
        'payment_status'     => 'integer',
        'status'             => 'integer',
        'dining_table_id'    => 'integer',
        'source'             => 'integer',
        'pos_payment_method' => 'integer',
        'pos_payment_note'   => 'string',
        'payment_transaction_id' => 'string',
        'payment_transaction_data' => 'array',
        'waiting_number'     => 'integer',
        'business_date'      => 'datetime',
        'order_note'         => 'string', // Cast for order_note field
        'customer_name'      => 'string',
        'customer_phone_number' => 'string',
        'customer_address'   => 'string',
        // 'payment_method_id'  => 'integer', // Assuming this is a foreign key to PaymentMethod
        'currency'           => 'string',
        'currency_id'        => 'integer',
        'receive_payment_currency' => 'string',
        'receive_payment_currency_id' => 'integer',
        'rejection_reason'   => 'string',
        'rejected_at'        => 'datetime',
        'check_in_time'      => 'datetime',
        'check_out_time'     => 'datetime',
        'telegram_user_id'   => 'string',
        'telegram_chat_id'   => 'string',
        'telegram_username'  => 'string',
        'checkout'           => 'datetime',
        'change_amount'      => 'json',
        'group_session_id'   => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::addGlobalScope(new BranchScope());

        static::saved(function (Order $order) {
            if (!$order->wasChanged('payment_status') && !$order->wasChanged('group_session_id')) {
                return;
            }

            $groupSession = null;
            if ($order->group_session_id) {
                $groupSession = GroupSession::find($order->group_session_id);
            }
            if (!$groupSession) {
                $groupSessionId = SubSession::where('order_id', $order->id)->value('group_session_id');
                $groupSession = $groupSessionId ? GroupSession::find($groupSessionId) : null;
            }

            $groupSession?->syncStatusFromOrders();
        });
    }

    public function orderItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function items(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'order_items')->withTrashed();
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function member(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function orderUser(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'order_user_id')->withTrashed();
    }

    public function address(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(OrderAddress::class);
    }

    public function branch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function groupSession(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(GroupSession::class, 'group_session_id');
    }

    public function currencyRelation(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function receivePaymentCurrencyRelation(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Currency::class, 'receive_payment_currency_id');
    }

    public function orderStatus(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(OrderStatus::class, 'status', 'status_code');
    }

    public function orderType(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(OrderType::class, 'order_type', 'type_code');
    }

    public function scopePending($query)
    {
        return $query->where('status', OrderStatusEnum::PENDING);
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', OrderStatusEnum::PROCESSING);
    }

    public function scopeOutForDelivery($query)
    {
        return $query->where('status', OrderStatusEnum::OUT_FOR_DELIVERY);
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', OrderStatusEnum::DELIVERED);
    }

    public function scopeCanceled($query)
    {
        return $query->where('status', OrderStatusEnum::CANCELED);
    }

    public function scopeReturned($query)
    {
        return $query->where('status', OrderStatusEnum::RETURNED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', OrderStatusEnum::REJECTED);
    }

    public function transaction(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Transaction::class);
    }

    public function transactions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function diningTable(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(DiningTable::class);
    }

    public function orderDinings(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        // return $this->hasMany(OrderDining::class, 'order_dinings');
        return $this->hasMany(OrderDining::class, 'order_id', 'id');
    }

    public function diningTables(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(DiningTable::class, 'order_dinings');
    }

    public function diningTableOrder(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DiningTable::class);
    }
    // public function paymentMethod(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    // {
    //     return $this->belongsTo(PaymentMethod::class, 'payment_method_id', 'id');
    // }

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

    // Point-related scopes and methods
    public function scopeWithMember($query)
    {
        return $query->whereNotNull('member_id');
    }

    public function scopeWithPointsEarned($query)
    {
        return $query->where('points_earned', '>', 0);
    }

    public function scopeWithPointsRedeemed($query)
    {
        return $query->where('points_redeemed', '>', 0);
    }

    /**
     * Get the net point change for this order (earned - redeemed)
     */
    public function getNetPointChangeAttribute(): int
    {
        return $this->points_earned - $this->points_redeemed;
    }

    /**
     * Check if this order has any point activity
     */
    public function hasPointActivity(): bool
    {
        return $this->points_earned > 0 || $this->points_redeemed > 0;
    }

    /**
     * Get formatted points earned
     */
    public function getFormattedPointsEarnedAttribute(): string
    {
        return $this->points_earned > 0 ? "+{$this->points_earned} points" : "0 points";
    }

    /**
     * Get formatted points redeemed
     */
    public function getFormattedPointsRedeemedAttribute(): string
    {
        return $this->points_redeemed > 0 ? "-{$this->points_redeemed} points" : "0 points";
    }

    /**
     * Get the member's name or guest indicator
     */
    public function getCustomerDisplayNameAttribute(): string
    {
        if ($this->member) {
            return $this->member->name;
        } elseif ($this->user) {
            return $this->user->name;
        }
        return 'Guest Customer';
    }

    /**
     * Calculate effective total after point redemption
     */
    public function getEffectiveTotalAttribute(): float
    {
        // This would be the actual amount paid after applying point redemption discount
        // Implementation depends on how points are converted to currency value
        return $this->total; // Base implementation - should be customized based on point conversion rules
    }

    public function stockRecord():\Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(StockRecord::class, 'order_id', 'id');
    } 
}

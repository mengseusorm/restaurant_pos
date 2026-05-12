<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = "transactions";
    protected $fillable = [
        'order_id', 
        'user_id', 
        'transaction_no', 
        'amount', 
        'currency',
        'currency_id',
        'amount_base_currency',
        'base_currency',
        'base_currency_id',
        'transaction_amount',
        'transaction_currency',
        'transaction_currency_id',
        'change_amount',
        'change_currency',
        'change_currency_id',
        'exchange_rate',
        'exchange_rate_base',
        'exchange_rate_target',
        'payment_method', 
        'pos_payment_method', 
        'type', 
        'sign', 
        'reference_transaction', 
        'gateway_response',
        'note'
    ];
    protected $casts = [
        'id'                     => 'integer',
        'order_id'               => 'integer',
        'user_id'                => 'integer',
        'transaction_no'         => 'string',
        'amount'                 => 'decimal:6',
        'currency'               => 'string',
        'currency_id'            => 'integer',
        'amount_base_currency'   => 'decimal:6',
        'base_currency'          => 'string',
        'base_currency_id'       => 'integer',
        'transaction_amount'     => 'decimal:6',
        'transaction_currency'   => 'string',
        'transaction_currency_id' => 'integer',
        'change_amount'          => 'decimal:6',
        'change_currency'        => 'string',
        'change_currency_id'     => 'integer',
        'exchange_rate'          => 'decimal:8',
        'exchange_rate_base'     => 'string',
        'exchange_rate_target'   => 'string',
        'payment_method'         => 'string',
        'pos_payment_method'     => 'integer',
        'type'                   => 'string',
        'sign'                   => 'string',
        'note'                   => 'string',
    ];

    public function order() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    public function posPaymentMethod() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'pos_payment_method', 'id');
    }

    public function user() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function currencyRelation() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function baseCurrencyRelation() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Currency::class, 'base_currency_id');
    }

    public function transactionCurrencyRelation() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Currency::class, 'transaction_currency_id');
    }

    public function changeCurrencyRelation() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Currency::class, 'change_currency_id');
    }
}

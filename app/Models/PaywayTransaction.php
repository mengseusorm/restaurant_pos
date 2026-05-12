<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaywayTransaction extends Model
{
    protected $table = "payway_transactions";
    
    protected $fillable = [
        'tran_id',
        'order_id',
        'branch_id',
        'payment_method_id',
        'amount',
        'currency',
        'qr_string',
        'qr_image',
        'abapay_deeplink',
        'payment_status_code',
        'payment_status',
        'payment_amount',
        'payment_currency',
        'apv',
        'transaction_date',
        'response_data',
    ];
    
    protected $casts = [
        'id'                   => 'integer',
        'order_id'             => 'integer',
        'branch_id'            => 'integer',
        'payment_method_id'    => 'integer',
        'amount'               => 'decimal:2',
        'payment_amount'       => 'decimal:2',
        'payment_status_code'  => 'integer',
        'transaction_date'     => 'datetime',
        'response_data'        => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id', 'id');
    }

    /**
     * Get the related transaction from transactions table
     * The tran_id in payway_transactions matches transaction_no in transactions
     */
    public function transaction()
    {
        return $this->hasOne(Transaction::class, 'transaction_no', 'tran_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockRecord extends Model
{
    use HasFactory;

    protected $table = "stock_records";
    protected $fillable = [
        'item_id',
        'stock_id',
        'user_id' ,
        'order_id',  // When have record, to_warehouse_id and from_warehouse_id is null
        'quantity',
        'record_type',
        'from_warehouse_id',  // When have record, to_warehouse_id is null, order_id is null
        'to_warehouse_id',   // When have record, from_warehouse_id is null, order_id is null
        'created_at',	
    ];
    protected $casts = [
        'id'                => 'integer',
        'item_id'           => 'integer',
        'stock_id'          => 'integer',
        'user_id'           => 'integer',
        'order_id'          => 'integer',
        'quantity'          => 'integer',
        'record_type'       => 'string',
        'to_warehouse_id'   => 'integer',
        'from_warehouse_id' => 'integer',
        'created_at'        => 'datetime',
    ];

    public function fromwarehouse(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ItemStock::class, 'from_warehouse_id');
    }

    public function towarehouse(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ItemStock::class, 'to_warehouse_id');
    }

    public function stock() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ItemStock::class);
    }

    public function item(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(user::class);
    }

    public function order(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
    
    public function getTransferTypeAttribute()
    {
        if ($this->from_warehouse_id && $this->from_warehouse_id != $this->stock_id) {
            return 'TRANSFER IN';
        }
        if ($this->to_warehouse_id && $this->to_warehouse_id != $this->stock_id) {
            return 'TRANSFER OUT';
        }
        return null;
    }
}

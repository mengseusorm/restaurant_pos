<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $table = "order_items";
    protected $fillable = [
        'order_id',
        'branch_id',
        'item_id',
        'order_item_custom_name',
        'quantity',
        'discount',
        'discount_percentage',
        'tax_name',
        'tax_rate',
        'tax_type',
        'tax_amount',  // Total tax amount that has already x quantity
        'price',   // This is just save the unit price of the item
        'item_variations',
        'item_extras',
        'item_variation_total',
        'item_extra_total',
        'total_price',  // price x quantity + item_variation_total + item_extra_total
        'instruction',
        'move_from_order', // Previous order_serial_no from which this item was moved
        'move_by', // User ID who performed the move operation
        'order_times',
        'order_item_status',
        'reasons',
        'creator_type',
        'creator_id',
        'editor_type',
        'editor_id',
        'created_at',
        'updated_at'
    ];
    protected $casts = [
        'id'                   => 'integer',
        'order_id'             => 'integer',
        'branch_id'            => 'integer',
        'item_id'              => 'integer',
        'order_item_custom_name' => 'string',
        'quantity'             => 'integer',
        'discount'             => 'decimal:6',
        'discount_percentage'  => 'decimal:2',
        'tax_name'             => 'string',
        'tax_rate'             => 'string',
        'tax_type'             => 'integer',
        'tax_amount'           => 'decimal:6',
        'price'                => 'decimal:6',
        'item_variations'      => 'string',
        'item_extras'          => 'string',
        'item_variation_total' => 'decimal:6',
        'item_extra_total'     => 'decimal:6',
        'total_price'          => 'decimal:6',
        'instruction'          => 'string',
        'move_from_order'      => 'string',
        'move_by'              => 'integer',
        'order_times'          => 'string',
        'order_item_status'    => 'integer',
        'reasons'              => 'integer',       
        'creator_type'         => 'string',
        'creator_id'           => 'integer',
        'editor_type'          => 'string',
        'editor_id'            => 'integer',
        'created_at'           => 'datetime',
        'updated_at'           => 'datetime'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }
    
    // public function item()
    // {
    //     return $this->belongsTo(Item::class, 'item_id', 'id')->withTrashed();
    // }
    
    public function orderItem()
    {
        return $this->belongsTo(Item::class, 'item_id', 'id')->withTrashed();
    }
    
    public function branch(){
        return $this->belongsTo(Branch::class);
    }
    public function movedBy()
    {
        return $this->belongsTo(User::class, 'move_by', 'id')->withTrashed();
    }
}

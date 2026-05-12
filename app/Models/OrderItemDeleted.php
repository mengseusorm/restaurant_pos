<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItemDeleted extends Model
{
    use HasFactory;

    protected $table = "order_item_deleteds";
    protected $fillable = [
        'order_id',
        'order_serial_no',
        'delete_reason',
        'branch_id',
        'item_id',
        'quantity',
        'discount',
        'discount_percentage',
        'tax_name',
        'tax_rate',
        'tax_type',
        'tax_amount',
        'price',
        'item_variations',
        'item_extras',
        'item_variation_total',
        'item_extra_total',
        'total_price',
        'instruction',
        'order_times',
        'order_item_status',
        'reasons',
        'creator_type',
        'creator_id',
        'editor_type',
        'editor_id',
        'order_created_at',
        'order_updated_at',
        'created_at',
        'updated_at'
    ];
    protected $casts = [
        'id'                   => 'integer',
        'order_id'             => 'integer',
        'order_serial_no'      => 'string',
        'delete_reason'        => 'string',
        'branch_id'            => 'integer',
        'item_id'              => 'integer',
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

    public function item(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id', 'id');
    }
}

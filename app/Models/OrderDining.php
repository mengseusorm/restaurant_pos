<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDining extends Model
{
    use HasFactory;
    protected $table = "order_dinings";
    protected $fillable = [
        'order_id',
        'dining_table_id',
        'branch_id'

    ];
    protected $casts = [
        'id'                   => 'integer',
        'order_id'             => 'integer',
        'dining_table_id'      => 'integer',
        'branch_id'            => 'integer'
    ];

    public function order() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
    public function diningTable()
    {
        return $this->belongsTo(DiningTable::class, 'dining_table_id', 'id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}

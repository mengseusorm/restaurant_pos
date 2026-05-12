<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Model;

class OrderStatus extends Model
{
    protected $table = "order_statuses";
    protected $fillable = ['branch_id', 'status_code', 'name', 'name_kh', 'name_cn', 'name_en', 'status_order', 'status'];

    protected $casts = [
        'id'           => 'integer',
        'branch_id'    => 'integer',
        'status_code'  => 'integer',
        'name'         => 'string',
        'name_kh'      => 'string',
        'name_cn'      => 'string',
        'name_en'      => 'string',
        'status_order' => 'integer',
        'status'       => 'integer',
    ];

    public function order(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Order::class, 'status_code');
    }

    protected static function boot(): void
    {
        parent::boot();
        static::addGlobalScope(new BranchScope());
    }
}

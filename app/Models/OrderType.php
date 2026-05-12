<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Model;

class OrderType extends Model
{
    protected $table = "order_types";
    protected $fillable = ['branch_id', 'type_code', 'name', 'name_kh', 'name_cn', 'name_en', 'type_order', 'status'];

    protected $casts = [
        'id'         => 'integer',
        'branch_id'  => 'integer',
        'type_code'  => 'integer',
        'name'       => 'string',
        'name_kh'    => 'string',
        'name_cn'    => 'string',
        'name_en'    => 'string',
        'type_order' => 'integer',
        'status'     => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::addGlobalScope(new BranchScope());
    }
}

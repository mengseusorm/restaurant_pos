<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sort',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
        'status' => 'integer',
        'sort'   => 'integer',
    ];

    public function branches(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Branch::class);
    }
}

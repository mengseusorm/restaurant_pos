<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemAttribute extends Model
{
    use HasFactory;

    protected $table = "item_attributes";
    protected $fillable = ['name', 'status', 'require_input_price'];
    protected $casts = [
        'id'                  => 'integer',
        'name'                => 'string',
        'status'              => 'integer',
        'require_input_price' => 'integer',
    ];

    public function variations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ItemAttributeVariation::class);
    }
}

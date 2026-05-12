<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $table = "currencies";
    protected $fillable = ['name', 'name_kh', 'name_cn', 'symbol', 'code', 'decimal_places', 'is_cryptocurrency', 'exchange_rate','second_currency','second_currency_exchange_rate','second_decimal','show_exchange_rate_on_invoice'];

    protected $casts = [
        'id'                            => 'integer',
        'name'                          => 'string',
        'name_kh'                       => 'string',
        'name_cn'                       => 'string',
        'symbol'                        => 'string',
        'code'                          => 'string',
        'decimal_places'                => 'integer',
        'is_cryptocurrency'             => 'integer',
        'exchange_rate'                 => 'decimal:10',
        'second_currency'               => 'string',
        'second_currency_exchange_rate' => 'string',
        'second_decimal'                => 'string',
        'show_exchange_rate_on_invoice' => 'integer',
    ];
    /**
     * branch
     */
    public function branch() : \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Branch::class);
    }
}

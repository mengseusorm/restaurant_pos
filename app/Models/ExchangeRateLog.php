<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangeRateLog extends Model
{
    protected $table = "exchange_rate_logs";
    
    const UPDATED_AT = null; // This table only has created_at
    
    protected $fillable = [
        'base_currency',
        'target_currency',
        'rate',
        'source',
        'created_by',
    ];

    protected $casts = [
        'id' => 'integer',
        'base_currency' => 'string',
        'target_currency' => 'string',
        'rate' => 'decimal:10',
        'source' => 'string',
        'created_by' => 'integer',
    ];

    /**
     * Get the base currency model
     */
    public function baseCurrencyModel()
    {
        return $this->belongsTo(Currency::class, 'base_currency', 'code');
    }

    /**
     * Get the target currency model
     */
    public function targetCurrencyModel()
    {
        return $this->belongsTo(Currency::class, 'target_currency', 'code');
    }

    /**
     * Get the user who created this log
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Log a new exchange rate change
     */
    public static function logRate($baseCurrency, $targetCurrency, $rate, $source = 'manual', $createdBy = null)
    {
        return self::create([
            'base_currency' => $baseCurrency,
            'target_currency' => $targetCurrency,
            'rate' => $rate,
            'source' => $source,
            'created_by' => $createdBy ?? auth()->id(),
        ]);
    }
}

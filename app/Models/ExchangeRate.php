<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    protected $table = "exchange_rates";
    
    protected $fillable = [
        'base_currency',
        'target_currency',
        'rate',
        'effective_at',
    ];

    protected $casts = [
        'id' => 'integer',
        'base_currency' => 'string',
        'target_currency' => 'string',
        'rate' => 'decimal:10',
        'effective_at' => 'datetime',
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
     * Get exchange rate logs for this pair
     */
    public function logs()
    {
        return $this->hasMany(ExchangeRateLog::class, 'base_currency', 'base_currency')
            ->where('target_currency', $this->target_currency)
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get the latest exchange rate for a currency pair
     */
    public static function getRate($baseCurrency, $targetCurrency)
    {
        // Same currency, rate is 1
        if ($baseCurrency === $targetCurrency) {
            return 1.0;
        }

        $rate = self::where('base_currency', $baseCurrency)
            ->where('target_currency', $targetCurrency)
            ->first();

        return $rate ? (float)$rate->rate : null;
    }

    /**
     * Convert amount from base currency to target currency
     */
    public static function convert($amount, $baseCurrency, $targetCurrency)
    {
        if ($baseCurrency === $targetCurrency) {
            return $amount;
        }

        $rate = self::getRate($baseCurrency, $targetCurrency);
        
        if ($rate === null) {
            // Try inverse rate
            $inverseRate = self::getRate($targetCurrency, $baseCurrency);
            if ($inverseRate !== null && $inverseRate > 0) {
                $rate = 1 / $inverseRate;
            }
        }

        return $rate ? $amount * $rate : null;
    }
}

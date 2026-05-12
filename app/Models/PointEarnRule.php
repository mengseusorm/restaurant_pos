<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointEarnRule extends Model
{
    use HasFactory;

    protected $table = "point_earn_rules";

    protected $fillable = [
        'branch_id',
        'name', // Added field
        'currency_amount',
        'point',
        'is_active',
    ];

    protected $casts = [
        'id' => 'integer',
        'branch_id' => 'integer',
        'name' => 'string', // Added cast
        'currency_amount' => 'decimal:2',
        'point' => 'integer',
        'is_active' => 'boolean',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::addGlobalScope(new BranchScope());
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Calculate points earned for a given currency amount
     */
    public function calculatePointsForAmount(float $amount): int
    {
        if ($this->currency_amount <= 0) {
            return 0;
        }
        
        return (int) floor(($amount / $this->currency_amount) * $this->point);
    }

    /**
     * Get the currency value equivalent to a point
     */
    public function getCurrencyPerPointAttribute(): float
    {
        if ($this->point <= 0) {
            return 0;
        }
        
        return $this->currency_amount / $this->point;
    }

    /**
     * Get formatted currency amount
     */
    public function getFormattedCurrencyAmountAttribute(): string
    {
        return number_format($this->currency_amount, 2);
    }
}


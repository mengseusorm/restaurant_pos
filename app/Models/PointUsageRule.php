<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointUsageRule extends Model
{
    use HasFactory;

    protected $table = "point_usage_rules";

    protected $fillable = [
        'branch_id',
        'name',
        'usage_type',
        'point_to_currency',
        'min_point_usage',
        'max_point_usage',
        'is_active',
    ];

    protected $casts = [
        'id' => 'integer',
        'branch_id' => 'integer',
        'name' => 'string',
        'usage_type' => 'string',
        'point_to_currency' => 'decimal:2',
        'min_point_usage' => 'integer',
        'max_point_usage' => 'integer',
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

    public function scopeForUsageType($query, string $usageType)
    {
        return $query->where('usage_type', $usageType);
    }

    public function scopeDeductOrder($query)
    {
        return $query->where('usage_type', 'deduct_order');
    }

    public function scopeExchangeGift($query)
    {
        return $query->where('usage_type', 'exchange_gift');
    }

    /**
     * Calculate currency value for given points
     */
    public function calculateCurrencyForPoints(int $points): float
    {
        if ($points < $this->min_point_usage) {
            return 0;
        }

        if ($this->max_point_usage && $points > $this->max_point_usage) {
            $points = $this->max_point_usage;
        }

        return $points * $this->point_to_currency;
    }

    /**
     * Check if points amount is valid for usage
     */
    public function isValidPointAmount(int $points): bool
    {
        if ($points < $this->min_point_usage) {
            return false;
        }

        if ($this->max_point_usage && $points > $this->max_point_usage) {
            return false;
        }

        return true;
    }

    /**
     * Get formatted point to currency rate
     */
    public function getFormattedPointToCurrencyAttribute(): string
    {
        return number_format($this->point_to_currency, 2);
    }

    /**
     * Get usage type display name
     */
    public function getUsageTypeDisplayAttribute(): string
    {
        return match($this->usage_type) {
            'deduct_order' => 'Order Deduction',
            'exchange_gift' => 'Gift Exchange',
            default => ucfirst(str_replace('_', ' ', $this->usage_type))
        };
    }
}

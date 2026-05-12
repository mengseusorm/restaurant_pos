<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointGift extends Model
{
    use HasFactory;

    protected $table = "point_gifts";

    protected $fillable = [
        'branch_id',
        'item_id',
        'required_points',
        'stock_limit',
        'redeemed_count',
        'is_active',
    ];

    protected $casts = [
        'id' => 'integer',
        'branch_id' => 'integer',
        'item_id' => 'integer',
        'required_points' => 'integer',
        'stock_limit' => 'integer',
        'redeemed_count' => 'integer',
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'remaining_stock',
        'is_unlimited_stock',
        'is_in_stock',
        'item_name',
        'item_price',
        'formatted_required_points',
        'points_saved',
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

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class)->withTrashed();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeWithItem($query)
    {
        return $query->with('item');
    }

    public function scopeForItem($query, int $itemId)
    {
        return $query->where('item_id', $itemId);
    }

    public function scopeAffordableBy($query, int $memberPoints)
    {
        return $query->where('required_points', '<=', $memberPoints);
    }

    public function scopeByPointsRequired($query, string $direction = 'asc')
    {
        return $query->orderBy('required_points', $direction);
    }

    public function scopeInStock($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('stock_limit')
              ->orWhereRaw('redeemed_count < stock_limit');
        });
    }

    public function scopeOutOfStock($query)
    {
        return $query->whereNotNull('stock_limit')
                     ->whereRaw('redeemed_count >= stock_limit');
    }

    public function scopeWithStock($query)
    {
        return $query->selectRaw('*, CASE 
            WHEN stock_limit IS NULL THEN -1 
            ELSE (stock_limit - redeemed_count) 
            END as remaining_stock');
    }

    /**
     * Check if a member can afford this gift
     */
    public function canBeAffordedBy(Member $member): bool
    {
        return $member->point_balance >= $this->required_points;
    }

    /**
     * Check if the gift can be redeemed (in stock and available)
     */
    public function canBeRedeemed(Member $member): bool
    {
        if (!$this->isAvailable()) {
            return false;
        }

        if (!$this->is_in_stock) {
            return false;
        }

        if ($member && !$this->canBeAffordedBy($member)) {
            return false;
        }

        return true;
    }

    /**
     * Increment the redeemed count
     */
    public function incrementRedeemedCount(int $count = 1): bool
    {
        if (!$this->is_in_stock) {
            throw new \Exception('Cannot redeem: Gift is out of stock');
        }

        if (!is_null($this->stock_limit) && ($this->redeemed_count + $count) > $this->stock_limit) {
            throw new \Exception('Cannot redeem: Not enough stock available');
        }

        return $this->increment('redeemed_count', $count);
    }

    /**
     * Check if gift is available (active and item exists)
     */
    public function isAvailable(): bool
    {
        return $this->is_active && $this->item && !$this->item->trashed();
    }

    /**
     * Get the item name safely
     */
    public function getItemNameAttribute(): string
    {
        return $this->item ? $this->item->name : 'Item not found';
    }

    /**
     * Get remaining stock attribute
     */
    public function getRemainingStockAttribute(): int
    {
        if (is_null($this->stock_limit)) {
            return -1; // Unlimited stock
        }
        
        return max(0, $this->stock_limit - $this->redeemed_count);
    }

    /**
     * Check if stock is unlimited
     */
    public function getIsUnlimitedStockAttribute(): bool
    {
        return is_null($this->stock_limit);
    }

    /**
     * Check if gift is in stock
     */
    public function getIsInStockAttribute(): bool
    {
        return $this->is_unlimited_stock || $this->remaining_stock > 0;
    }

    /**
     * Get the item price safely
     */
    public function getItemPriceAttribute(): float
    {
        return $this->item ? $this->item->price : 0;
    }

    /**
     * Get formatted required points
     */
    public function getFormattedRequiredPointsAttribute(): string
    {
        return number_format($this->required_points) . ' points';
    }

    /**
     * Calculate points saved compared to item price
     */
    public function getPointsSavedAttribute(): float
    {
        if (!$this->item || !$this->item->price) {
            return 0;
        }

        // Assuming 1 point = 1 currency unit for calculation
        // You might want to use PointUsageRule to get actual conversion rate
        return max(0, $this->item->price - $this->required_points);
    }
}

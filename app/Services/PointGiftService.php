<?php

namespace App\Services;

use App\Models\PointGift;
use App\Models\Member;
use App\Models\MemberPointTransaction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PointGiftService
{
    /**
     * Get paginated point gifts
     */
    public function index(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = PointGift::with(['branch', 'item']);

        // Apply filters
        if (!empty($filters['is_active'])) {
            $query->where('is_active', $filters['is_active'] === 'true');
        }

        if (!empty($filters['in_stock_only'])) {
            $query->inStock();
        }

        if (!empty($filters['item_id'])) {
            $query->where('item_id', $filters['item_id']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('required_points', 'like', '%' . $filters['search'] . '%')
                  ->orWhereHas('item', function ($itemQuery) use ($filters) {
                      $itemQuery->where('name', 'like', '%' . $filters['search'] . '%');
                  });
            });
        }

        return $query->orderBy('required_points', 'asc')->paginate($perPage);
    }

    /**
     * Get all active point gifts
     */
    public function getActive(): Collection
    {
        return PointGift::active()
            ->inStock()
            ->with(['item'])
            ->byPointsRequired('asc')
            ->get();
    }

    /**
     * Get gifts affordable by member
     */
    public function getAffordableByMember(Member $member): Collection
    {
        return PointGift::active()
            ->inStock()
            ->affordableBy($member->point_balance)
            ->with(['item'])
            ->byPointsRequired('asc')
            ->get();
    }

    /**
     * Store a new point gift
     */
    public function store(array $data): PointGift
    {
        $data['branch_id'] = auth()->user()->branch_id;
        
        return PointGift::create($data);
    }

    /**
     * Update point gift
     */
    public function update(PointGift $pointGift, array $data): bool
    {
        return $pointGift->update($data);
    }

    /**
     * Delete point gift
     */
    public function destroy(PointGift $pointGift): bool
    {
        return $pointGift->delete();
    }

    /**
     * Toggle active status
     */
    public function toggleStatus(PointGift $pointGift): bool
    {
        return $pointGift->update(['is_active' => !$pointGift->is_active]);
    }

    /**
     * Redeem gift for member
     */
    public function redeemGift(PointGift $gift, Member $member, int $quantity = 1): array
    {
        $result = [
            'success' => false,
            'message' => '',
            'transaction' => null,
        ];

        DB::beginTransaction();
        
        try {
            // Validate redemption
            $validation = $this->validateRedemption($gift, $member, $quantity);
            if (!$validation['can_redeem']) {
                $result['message'] = $validation['message'];
                return $result;
            }

            $totalPointsRequired = $gift->required_points * $quantity;

            // Create point transaction
            $transaction = MemberPointTransaction::create([
                'member_id' => $member->id,
                'branch_id' => $member->branch_id,
                'transaction_type' => 'deduct',
                'point_amount' => $totalPointsRequired,
                'description' => "Redeemed gift: {$gift->item_name} (Qty: {$quantity})",
                'reference_type' => 'point_gift',
                'reference_id' => $gift->id,
            ]);

            // Update member balance
            $member->decrement('point_balance', $totalPointsRequired);

            // Update gift redeemed count
            $gift->incrementRedeemedCount($quantity);

            DB::commit();

            $result['success'] = true;
            $result['message'] = 'Gift redeemed successfully';
            $result['transaction'] = $transaction;

        } catch (\Exception $e) {
            DB::rollback();
            $result['message'] = $e->getMessage();
        }

        return $result;
    }

    /**
     * Validate gift redemption
     */
    public function validateRedemption(PointGift $gift, Member $member, int $quantity = 1): array
    {
        $result = [
            'can_redeem' => false,
            'message' => '',
        ];

        // Check if gift is available
        if (!$gift->isAvailable()) {
            $result['message'] = 'Gift is not available';
            return $result;
        }

        // Check stock availability
        if (!$gift->is_in_stock) {
            $result['message'] = 'Gift is out of stock';
            return $result;
        }

        // Check stock quantity
        if (!$gift->is_unlimited_stock && $gift->remaining_stock < $quantity) {
            $result['message'] = "Only {$gift->remaining_stock} items remaining in stock";
            return $result;
        }

        $totalPointsRequired = $gift->required_points * $quantity;

        // Check member points
        if ($member->point_balance < $totalPointsRequired) {
            $result['message'] = "Insufficient points. Required: {$totalPointsRequired}, Available: {$member->point_balance}";
            return $result;
        }

        $result['can_redeem'] = true;
        $result['message'] = 'Gift can be redeemed';
        return $result;
    }

    /**
     * Get gift redemption history
     */
    public function getRedemptionHistory(PointGift $gift): Collection
    {
        return MemberPointTransaction::where('reference_type', 'point_gift')
            ->where('reference_id', $gift->id)
            ->where('transaction_type', 'deduct')
            ->with(['member'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get gift statistics
     */
    public function getStatistics(): array
    {
        $totalGifts = PointGift::count();
        $activeGifts = PointGift::active()->count();
        $inStockGifts = PointGift::active()->inStock()->count();
        $outOfStockGifts = PointGift::active()->outOfStock()->count();

        $totalRedemptions = PointGift::sum('redeemed_count');
        $totalPointsRedeemed = MemberPointTransaction::where('reference_type', 'point_gift')
            ->where('transaction_type', 'deduct')
            ->sum('point_amount');

        return [
            'total_gifts' => $totalGifts,
            'active_gifts' => $activeGifts,
            'inactive_gifts' => $totalGifts - $activeGifts,
            'in_stock_gifts' => $inStockGifts,
            'out_of_stock_gifts' => $outOfStockGifts,
            'total_redemptions' => $totalRedemptions,
            'total_points_redeemed' => $totalPointsRedeemed,
            'most_popular_gift' => $this->getMostPopularGift(),
        ];
    }

    /**
     * Get most popular gift
     */
    private function getMostPopularGift(): ?array
    {
        $gift = PointGift::with('item')
            ->where('redeemed_count', '>', 0)
            ->orderBy('redeemed_count', 'desc')
            ->first();

        if (!$gift) {
            return null;
        }

        return [
            'id' => $gift->id,
            'name' => $gift->item_name,
            'redeemed_count' => $gift->redeemed_count,
            'required_points' => $gift->required_points,
        ];
    }

    /**
     * Bulk update gifts status
     */
    public function bulkUpdateStatus(array $ids, bool $isActive): int
    {
        return PointGift::whereIn('id', $ids)->update(['is_active' => $isActive]);
    }

    /**
     * Get gifts for export
     */
    public function getForExport(): Collection
    {
        return PointGift::with(['branch', 'item'])
            ->withStock()
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Update stock limit
     */
    public function updateStockLimit(PointGift $gift, ?int $stockLimit): bool
    {
        return $gift->update(['stock_limit' => $stockLimit]);
    }

    /**
     * Reset redeemed count
     */
    public function resetRedeemedCount(PointGift $gift): bool
    {
        return $gift->update(['redeemed_count' => 0]);
    }
}

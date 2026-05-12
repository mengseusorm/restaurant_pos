<?php

namespace App\Http\Controllers;

use App\Exports\PointGiftExport;
use App\Http\Requests\PointGiftRequest;
use App\Http\Requests\PaginateRequest;
use App\Http\Resources\PointGiftResource;
use App\Models\PointGift;
use App\Models\Member;
use App\Services\PointGiftService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Maatwebsite\Excel\Facades\Excel;

class PointGiftController extends Controller
{
    public function __construct(
        private PointGiftService $pointGiftService
    ) {}

    /**
     * Display a listing of point gifts
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['is_active', 'in_stock_only', 'item_id', 'search']);
        $perPage = $request->get('per_page', 15);
        
        $pointGifts = $this->pointGiftService->index($filters, $perPage);
        
        return PointGiftResource::collection($pointGifts);
    }

    /**
     * Get all active point gifts
     */
    public function active(): AnonymousResourceCollection
    {
        $pointGifts = $this->pointGiftService->getActive();
        
        return PointGiftResource::collection($pointGifts);
    }

    /**
     * Get gifts affordable by member
     */
    public function affordableByMember(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'member_id' => 'required|integer|exists:members,id',
        ]);

        $member = Member::findOrFail($request->member_id);
        $pointGifts = $this->pointGiftService->getAffordableByMember($member);
        
        return PointGiftResource::collection($pointGifts);
    }

    /**
     * Store a newly created point gift
     */
    public function store(PointGiftRequest $request): JsonResponse
    {
        $pointGift = $this->pointGiftService->store($request->validated());
        
        return response()->json([
            'success' => true,
            'message' => 'Point gift created successfully',
            'data' => new PointGiftResource($pointGift->load(['branch', 'item'])),
        ], 201);
    }

    /**
     * Display the specified point gift
     */
    public function show(PointGift $pointGift): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new PointGiftResource($pointGift->load(['branch', 'item'])),
        ]);
    }

    /**
     * Update the specified point gift
     */
    public function update(PointGiftRequest $request, PointGift $pointGift): JsonResponse
    {
        $updated = $this->pointGiftService->update($pointGift, $request->validated());
        
        if ($updated) {
            return response()->json([
                'success' => true,
                'message' => 'Point gift updated successfully',
                'data' => new PointGiftResource($pointGift->fresh(['branch', 'item'])),
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to update point gift',
        ], 400);
    }

    /**
     * Remove the specified point gift
     */
    public function destroy(PointGift $pointGift): JsonResponse
    {
        $deleted = $this->pointGiftService->destroy($pointGift);
        
        if ($deleted) {
            return response()->json([
                'success' => true,
                'message' => 'Point gift deleted successfully',
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to delete point gift',
        ], 400);
    }

    /**
     * Toggle point gift status
     */
    public function toggleStatus(PointGift $pointGift): JsonResponse
    {
        $updated = $this->pointGiftService->toggleStatus($pointGift);
        
        if ($updated) {
            return response()->json([
                'success' => true,
                'message' => 'Point gift status updated successfully',
                'data' => new PointGiftResource($pointGift->fresh(['branch', 'item'])),
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to update point gift status',
        ], 400);
    }

    /**
     * Redeem gift for member
     */
    public function redeem(Request $request, PointGift $pointGift): JsonResponse
    {
        $request->validate([
            'member_id' => 'required|integer|exists:members,id',
            'quantity' => 'sometimes|integer|min:1|max:100',
        ]);

        $member = Member::findOrFail($request->member_id);
        $quantity = $request->get('quantity', 1);
        
        $result = $this->pointGiftService->redeemGift($pointGift, $member, $quantity);
        
        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'transaction' => $result['transaction'],
                    'gift' => new PointGiftResource($pointGift->fresh(['branch', 'item'])),
                    'member_balance' => $member->fresh()->point_balance,
                ],
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => $result['message'],
        ], 400);
    }

    /**
     * Validate gift redemption
     */
    public function validateRedemption(Request $request, PointGift $pointGift): JsonResponse
    {
        $request->validate([
            'member_id' => 'required|integer|exists:members,id',
            'quantity' => 'sometimes|integer|min:1|max:100',
        ]);

        $member = Member::findOrFail($request->member_id);
        $quantity = $request->get('quantity', 1);
        
        $validation = $this->pointGiftService->validateRedemption($pointGift, $member, $quantity);
        
        return response()->json([
            'success' => true,
            'data' => array_merge($validation, [
                'total_points_required' => $pointGift->required_points * $quantity,
                'member_balance' => $member->point_balance,
                'remaining_after_redemption' => $member->point_balance - ($pointGift->required_points * $quantity),
            ]),
        ]);
    }

    /**
     * Get gift redemption history
     */
    public function redemptionHistory(PointGift $pointGift): JsonResponse
    {
        $history = $this->pointGiftService->getRedemptionHistory($pointGift);
        
        return response()->json([
            'success' => true,
            'data' => $history,
        ]);
    }

    /**
     * Get point gift statistics
     */
    public function statistics(): JsonResponse
    {
        $statistics = $this->pointGiftService->getStatistics();
        
        return response()->json([
            'success' => true,
            'data' => $statistics,
        ]);
    }

    /**
     * Update stock limit
     */
    public function updateStockLimit(Request $request, PointGift $pointGift): JsonResponse
    {
        $request->validate([
            'stock_limit' => 'nullable|integer|min:0|max:999999',
        ]);

        $updated = $this->pointGiftService->updateStockLimit(
            $pointGift,
            $request->stock_limit
        );
        
        if ($updated) {
            return response()->json([
                'success' => true,
                'message' => 'Stock limit updated successfully',
                'data' => new PointGiftResource($pointGift->fresh(['branch', 'item'])),
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to update stock limit',
        ], 400);
    }

    /**
     * Reset redeemed count
     */
    public function resetRedeemedCount(PointGift $pointGift): JsonResponse
    {
        $updated = $this->pointGiftService->resetRedeemedCount($pointGift);
        
        if ($updated) {
            return response()->json([
                'success' => true,
                'message' => 'Redeemed count reset successfully',
                'data' => new PointGiftResource($pointGift->fresh(['branch', 'item'])),
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to reset redeemed count',
        ], 400);
    }

    /**
     * Bulk update status
     */
    public function bulkUpdateStatus(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:point_gifts,id',
            'is_active' => 'required|boolean',
        ]);

        $updated = $this->pointGiftService->bulkUpdateStatus(
            $request->ids,
            $request->is_active
        );
        
        return response()->json([
            'success' => true,
            'message' => "{$updated} point gifts updated successfully",
        ]);
    }

    /**
     * Export point gifts
     */
    public function export(PaginateRequest $request)
    {
        try {
            return Excel::download(new PointGiftExport($this->pointGiftService, $request), 'PointGifts.xlsx');
        } catch (\Exception $exception) {
            return response()->json(['errors' => $exception->getMessage()], 422);
        }
    }
}

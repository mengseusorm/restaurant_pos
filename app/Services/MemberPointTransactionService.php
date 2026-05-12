<?php

namespace App\Services;

use Exception;
use App\Models\Member;
use App\Models\MemberPointTransaction;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\PaginateRequest;
use Illuminate\Support\Facades\DB;

class MemberPointTransactionService
{
    public array $transactionFilter = [
        'member_id',
        'branch_id',
        'type',
        'points',
        'reference_type',
        'reference_id',
        'note'
    ];

    /**
     * @throws Exception
     */
    public function list(PaginateRequest $request)
    {
        try {
            $requests    = $request->all();
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType   = $request->get('order_type') ?? 'desc';

            return MemberPointTransaction::with('member')->where(function ($query) use ($requests) {
                foreach ($requests as $key => $request) {
                    if (in_array($key, $this->transactionFilter)) {
                        if (in_array($key, ['member_id', 'points', 'reference_id'])) {
                            $query->where($key, $request);
                        } else {
                            $query->where($key, 'like', '%' . $request . '%');
                        }
                    }
                }
                
                // Date range filtering
                if (isset($requests['start_date']) && isset($requests['end_date'])) {
                    $query->betweenDates($requests['start_date'], $requests['end_date']);
                } elseif (isset($requests['start_date'])) {
                    $query->where('created_at', '>=', $requests['start_date']);
                } elseif (isset($requests['end_date'])) {
                    $query->where('created_at', '<=', $requests['end_date']);
                }

            })->orderBy($orderColumn, $orderType)->$method(
                $methodValue
            );
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function store($request)
    {
        try {
            DB::beginTransaction();

            $member = Member::findOrFail($request->member_id);

            // Validate point balance for debit transactions
            if (in_array($request->type, ['redeem', 'revert_earn']) && $member->point_balance < $request->points) {
                throw new Exception('Insufficient points balance', 422);
            }

            // Create transaction
            $transaction = MemberPointTransaction::create([
                'member_id' => $request->member_id,
                'branch_id' => $request->branch_id ?? $member->branch_id,
                'type' => $request->type,
                'points' => $request->points,
                'reference_type' => $request->reference_type,
                'reference_id' => $request->reference_id,
                'note' => $request->note,
            ]);

            // Update member point balance
            $this->updateMemberBalance($member, $request->type, $request->points);

            DB::commit();
            return $transaction->load('member');
        } catch (Exception $exception) {
            DB::rollBack();
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function show(MemberPointTransaction $transaction)
    {
        try {
            return MemberPointTransaction::with('member', 'reference')->findOrFail($transaction->id);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function destroy(MemberPointTransaction $transaction)
    {
        try {
            DB::beginTransaction();

            $member = $transaction->member;
            
            // Reverse the transaction effect on member balance
            $this->reverseMemberBalance($member, $transaction->type, $transaction->points);

            // Delete the transaction
            $transaction->delete();

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Get transactions for a specific member
     * @throws Exception
     */
    public function getByMember($memberId, PaginateRequest $request)
    {
        try {
            $method = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'created_at';
            $orderType = $request->get('order_type') ?? 'desc';

            return MemberPointTransaction::forMember($memberId)
                ->with('member')
                ->orderBy($orderColumn, $orderType)
                ->$method($methodValue);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Get transactions by reference
     * @throws Exception
     */
    public function getByReference($referenceType, $referenceId, PaginateRequest $request)
    {
        try {
            $method = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';

            return MemberPointTransaction::forReference($referenceType, $referenceId)
                ->with('member')
                ->orderBy('created_at', 'desc')
                ->$method($methodValue);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Get transaction statistics
     * @throws Exception
     */
    public function getStatistics($memberId = null, $startDate = null, $endDate = null)
    {
        try {
            $query = MemberPointTransaction::query();

            if ($memberId) {
                $query->forMember($memberId);
            }

            if ($startDate && $endDate) {
                $query->betweenDates($startDate, $endDate);
            }

            $earned = (clone $query)->earned()->sum('points');
            $redeemed = (clone $query)->redeemed()->sum('points');
            $revertedEarn = (clone $query)->revertedEarn()->sum('points');
            $revertedRedeem = (clone $query)->revertedRedeem()->sum('points');

            return [
                'total_transactions' => $query->count(),
                'total_earned' => $earned,
                'total_redeemed' => $redeemed,
                'total_reverted_earn' => $revertedEarn,
                'total_reverted_redeem' => $revertedRedeem,
                'net_earned' => $earned - $revertedEarn,
                'net_redeemed' => $redeemed - $revertedRedeem,
                'net_balance' => ($earned - $revertedEarn) - ($redeemed - $revertedRedeem),
            ];
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Revert a transaction (create opposite transaction)
     * @throws Exception
     */
    public function revertTransaction(MemberPointTransaction $transaction, $note = null)
    {
        try {
            DB::beginTransaction();

            $reverseType = $this->getReverseTransactionType($transaction->type);
            
            if (!$reverseType) {
                throw new Exception('Transaction type cannot be reverted', 422);
            }

            // Create reverse transaction
            $reverseTransaction = MemberPointTransaction::create([
                'member_id' => $transaction->member_id,
                'branch_id' => $transaction->branch_id,
                'type' => $reverseType,
                'points' => $transaction->points,
                'reference_type' => $transaction->reference_type,
                'reference_id' => $transaction->reference_id,
                'note' => $note ?? "Revert of transaction #{$transaction->id}",
            ]);

            // Update member balance
            $member = $transaction->member;
            $this->updateMemberBalance($member, $reverseType, $transaction->points);

            DB::commit();
            return $reverseTransaction->load('member');
        } catch (Exception $exception) {
            DB::rollBack();
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Update member point balance based on transaction type
     */
    private function updateMemberBalance(Member $member, string $type, int $points)
    {
        switch ($type) {
            case 'earn':
            case 'revert_redeem':
                $member->increment('point_balance', $points);
                break;
            case 'redeem':
            case 'revert_earn':
                $member->decrement('point_balance', $points);
                break;
        }
    }

    /**
     * Reverse member point balance (for transaction deletion)
     */
    private function reverseMemberBalance(Member $member, string $type, int $points)
    {
        switch ($type) {
            case 'earn':
            case 'revert_redeem':
                $member->decrement('point_balance', $points);
                break;
            case 'redeem':
            case 'revert_earn':
                $member->increment('point_balance', $points);
                break;
        }
    }

    /**
     * Get reverse transaction type
     */
    private function getReverseTransactionType(string $type): ?string
    {
        $reverseMap = [
            'earn' => 'revert_earn',
            'redeem' => 'revert_redeem',
            'revert_earn' => 'earn',
            'revert_redeem' => 'redeem',
        ];

        return $reverseMap[$type] ?? null;
    }
}

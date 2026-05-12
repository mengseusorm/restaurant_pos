<?php

namespace App\Services;

use App\Enums\MemberPointTransactionType;
use Exception;
use App\Models\Member;
use App\Models\User;
use App\Models\MemberPointTransaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\PaginateRequest;
use Illuminate\Support\Facades\DB;

class MemberService
{
    public array $memberFilter = [
        'name', 
        'phone', 
        'card_number', 
        'point_balance', 
        'is_active',
        'branch_id'
    ];

    public array $userFilter = ['user_id'];

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

            return Member::with('user', 'pointTransactions')->where(function ($query) use ($requests) {
                foreach ($requests as $key => $request) {
                    if (in_array($key, $this->memberFilter)) {
                        if ($key === 'is_active') {
                            $query->where($key, $request);
                        } else {
                            $query->where($key, 'like', '%' . $request . '%');
                        }
                    }
                    if (in_array($key, $this->userFilter)) {
                        $query->where($key, $request);
                    }
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
            
            $member = Member::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'user_id' => $request->user_id ?? null,
                'branch_id' => $request->branch_id ?? auth()->user()->branch_id,
                'card_number' => $request->card_number ?? $this->generateCardNumber(),
                'point_balance' => $request->point_balance ?? 0,
                'is_active' => $request->is_active ?? true,
            ]);

            // If initial points are provided, create a transaction record
            if ($request->point_balance && $request->point_balance > 0) {
                MemberPointTransaction::create([
                    'member_id' => $member->id,
                    'branch_id' => $member->branch_id,
                    'type' => 'CREDIT',
                    'points' => $request->point_balance,
                    'reference_type' => 'INITIAL_BALANCE',
                    'reference_id' => null,
                    'note' => 'Initial point balance',
                ]);
            }

            DB::commit();
            return $member;
        } catch (Exception $exception) {
            DB::rollBack();
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function update($request, Member $member)
    {
        try {
            DB::beginTransaction();
            
            $oldPointBalance = $member->point_balance;
            
            $member->update([
                'name' => $request->name ?? $member->name,
                'phone' => $request->phone ?? $member->phone,
                'user_id' => $request->user_id ?? $member->user_id,
                'branch_id' => $request->branch_id ?? $member->branch_id,
                'card_number' => $request->card_number ?? $member->card_number,
                'point_balance' => $request->point_balance ?? $member->point_balance,
                'is_active' => $request->is_active ?? $member->is_active,
            ]);

            // If point balance changed, create a transaction record
            if (isset($request->point_balance) && $request->point_balance != $oldPointBalance) {
                $pointDifference = $request->point_balance - $oldPointBalance;
                MemberPointTransaction::create([
                    'member_id' => $member->id,
                    'branch_id' => $member->branch_id,
                    'type' => $pointDifference > 0 ? 'CREDIT' : 'DEBIT',
                    'points' => abs($pointDifference),
                    'reference_type' => 'MANUAL_ADJUSTMENT',
                    'reference_id' => null,
                    'note' => 'Point balance adjusted manually',
                ]);
            }

            DB::commit();
            return $member;
        } catch (Exception $exception) {
            DB::rollBack();
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function destroy(Member $member)
    {
        try {
            DB::beginTransaction();
            
            // Delete related point transactions
            $member->pointTransactions()->delete();
            
            // Delete the member
            $member->delete();

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function show(Member $member)
    {
        try {
            return Member::with('user', 'pointTransactions')->findOrFail($member->id);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Find member by phone or card number
     * @throws Exception
     */
    public function findByPhoneOrCard($value)
    {
        try {
            return Member::forPhoneOrCardNumber($value)->with('user', 'pointTransactions')->first();
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Add points to member
     * @throws Exception
     */
    public function addPoints(Member $member, int $points, string $referenceType, int $referenceId, string $note)
    {
        try {
            DB::beginTransaction();

            // Update member point balance
            $member->increment('point_balance', $points);

            // Create transaction record
            MemberPointTransaction::create([
                'member_id' => $member->id,
                'branch_id' => $member->branch_id,
                'type' => MemberPointTransactionType::EARN,
                'points' => $points,
                'reference_type' => $referenceType ?? 'MANUAL_CREDIT',
                'reference_id' => $referenceId,
                'note' => $note ?? 'Points added',
            ]);

            DB::commit();
            return $member->fresh();
        } catch (Exception $exception) {
            DB::rollBack();
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Deduct points from member
     * @throws Exception
     */
    public function deductPoints(Member $member, int $points, string $referenceType, int $referenceId, string $note)
    {
        try {
            if ($member->point_balance < $points) {
                throw new Exception('Insufficient points balance', 422);
            }

            DB::beginTransaction();

            // Update member point balance
            $member->decrement('point_balance', $points);

            // Create transaction record
            MemberPointTransaction::create([
                'member_id' => $member->id,
                'branch_id' => $member->branch_id,
                'type' => MemberPointTransactionType::REDEEM,
                'points' => $points,
                'reference_type' => $referenceType ?? 'MANUAL_DEBIT',
                'reference_id' => $referenceId,
                'note' => $note ?? 'Points deducted',
            ]);

            DB::commit();
            return $member->fresh();
        } catch (Exception $exception) {
            DB::rollBack();
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Generate unique card number
     */
    private function generateCardNumber(): string
    {
        do {
            $cardNumber = 'MEM' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (Member::where('card_number', $cardNumber)->exists());

        return $cardNumber;
    }

    /**
     * Get member statistics
     */
    public function getStatistics()
    {
        try {
            return [
                'total_members' => Member::count(),
                'active_members' => Member::active()->count(),
                'inactive_members' => Member::where('is_active', false)->count(),
                'total_points_distributed' => Member::sum('point_balance'),
                'members_with_points' => Member::withPointBalance(1)->count(),
            ];
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}

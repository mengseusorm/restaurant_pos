<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\MemberPointTransaction;
use App\Services\MemberPointTransactionService;
use App\Http\Requests\PaginateRequest;
use App\Http\Resources\MemberPointTransactionResource;
use App\Http\Requests\MemberPointTransactionRequest;
use App\Exports\MemberPointTransactionExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class MemberPointTransactionController extends AdminController
{
    private MemberPointTransactionService $transactionService;

    public function __construct(MemberPointTransactionService $transactionService)
    {
        parent::__construct();
        $this->transactionService = $transactionService;
        $this->middleware(['permission:member_point_transactions'])->only(
            'index',
            'statistics',
            'getByMember',
            'getByReference',
            'recent',
            'summary',
            'export'
        );
        $this->middleware(['permission:member_point_transactions_create'])->only('store');
        $this->middleware(['permission:member_point_transactions_edit'])->only('revert');
        $this->middleware(['permission:member_point_transactions_delete'])->only('destroy');
        $this->middleware(['permission:member_point_transactions_show'])->only('show');
    }

    /**
     * Display a listing of the point transactions.
     */
    public function index(PaginateRequest $request)
    {
        try {
            return MemberPointTransactionResource::collection($this->transactionService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Store a newly created point transaction in storage.
     */
    public function store(MemberPointTransactionRequest $request)
    {
        try {
            return new MemberPointTransactionResource($this->transactionService->store($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Display the specified point transaction.
     */
    public function show(MemberPointTransaction $transaction)
    {
        try {
            return new MemberPointTransactionResource($this->transactionService->show($transaction));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Remove the specified point transaction from storage.
     */
    public function destroy(MemberPointTransaction $transaction)
    {
        try {
            $this->transactionService->destroy($transaction);
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Get transactions for a specific member.
     */
    public function getByMember($memberId, PaginateRequest $request)
    {
        try {
            return MemberPointTransactionResource::collection(
                $this->transactionService->getByMember($memberId, $request)
            );
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Get transactions by reference.
     */
    public function getByReference(Request $request)
    {
        try {
            $request->validate([
                'reference_type' => 'required|string',
                'reference_id' => 'required|integer'
            ]);

            $paginateRequest = new PaginateRequest($request->all());

            return MemberPointTransactionResource::collection(
                $this->transactionService->getByReference(
                    $request->reference_type,
                    $request->reference_id,
                    $paginateRequest
                )
            );
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Get point transaction statistics.
     */
    public function statistics(Request $request)
    {
        try {
            $request->validate([
                'member_id' => 'nullable|exists:members,id',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date'
            ]);

            $statistics = $this->transactionService->getStatistics(
                $request->member_id,
                $request->start_date,
                $request->end_date
            );

            return response(['status' => true, 'data' => $statistics]);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Revert a point transaction.
     */
    public function revert(MemberPointTransaction $transaction, Request $request)
    {
        try {
            $request->validate([
                'note' => 'nullable|string|max:255'
            ]);

            $reverseTransaction = $this->transactionService->revertTransaction(
                $transaction,
                $request->note
            );

            return new MemberPointTransactionResource($reverseTransaction);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Get recent transactions.
     */
    public function recent(Request $request)
    {
        try {
            $request->validate([
                'limit' => 'nullable|integer|min:1|max:50',
                'member_id' => 'nullable|exists:members,id'
            ]);

            $limit = $request->get('limit', 10);
            $query = MemberPointTransaction::with('member')->recent($limit);

            if ($request->member_id) {
                $query->forMember($request->member_id);
            }

            $transactions = $query->get();

            return MemberPointTransactionResource::collection($transactions);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Get transactions summary by type.
     */
    public function summary(Request $request)
    {
        try {
            $request->validate([
                'member_id' => 'nullable|exists:members,id',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date'
            ]);

            $query = MemberPointTransaction::query();

            if ($request->member_id) {
                $query->forMember($request->member_id);
            }

            if ($request->start_date && $request->end_date) {
                $query->betweenDates($request->start_date, $request->end_date);
            }

            $summary = [
                'earn' => [
                    'count' => (clone $query)->earned()->count(),
                    'total_points' => (clone $query)->earned()->sum('points')
                ],
                'redeem' => [
                    'count' => (clone $query)->redeemed()->count(),
                    'total_points' => (clone $query)->redeemed()->sum('points')
                ],
                'revert_earn' => [
                    'count' => (clone $query)->revertedEarn()->count(),
                    'total_points' => (clone $query)->revertedEarn()->sum('points')
                ],
                'revert_redeem' => [
                    'count' => (clone $query)->revertedRedeem()->count(),
                    'total_points' => (clone $query)->revertedRedeem()->sum('points')
                ]
            ];

            return response(['status' => true, 'data' => $summary]);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Export point transactions to Excel.
     */
    public function export(PaginateRequest $request)
    {
        try {
            return Excel::download(
                new MemberPointTransactionExport($this->transactionService, $request), 
                'MemberPointTransactions.xlsx'
            );
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}

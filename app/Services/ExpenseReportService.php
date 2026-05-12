<?php

namespace App\Services;

use Exception;
use App\Models\Expense;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExpenseReportService
{
    public function dailySummary($request)
    {
        try {
            $fromDate = $request->get('from_date');
            $toDate = $request->get('to_date');
            $branchId = $request->get('branch_id');

            $query = Expense::select(
                'expense_date',
                'branch_id',
                DB::raw('COUNT(*) as total_transactions'),
                DB::raw('SUM(amount) as total_amount')
            )
            ->where('is_deleted', false)
            ->where('status', '!=', 'rejected');

            if ($fromDate) {
                $query->whereDate('expense_date', '>=', $fromDate);
            }
            if ($toDate) {
                $query->whereDate('expense_date', '<=', $toDate);
            }
            if ($branchId) {
                $query->where('branch_id', $branchId);
            }

            $data = $query->with('branch:id,name')
                ->groupBy('expense_date', 'branch_id')
                ->orderBy('expense_date', 'desc')
                ->get();

            return [
                'data' => $data,
                'summary' => [
                    'total_amount' => $data->sum('total_amount'),
                    'total_transactions' => $data->sum('total_transactions'),
                ]
            ];
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function breakdownByType($request)
    {
        try {
            $fromDate = $request->get('from_date');
            $toDate = $request->get('to_date');
            $branchId = $request->get('branch_id');

            $query = Expense::select(
                'expense_type_id',
                DB::raw('COUNT(*) as total_transactions'),
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('AVG(amount) as average_amount')
            )
            ->where('is_deleted', false)
            ->where('status', '!=', 'rejected');

            if ($fromDate) {
                $query->whereDate('expense_date', '>=', $fromDate);
            }
            if ($toDate) {
                $query->whereDate('expense_date', '<=', $toDate);
            }
            if ($branchId) {
                $query->where('branch_id', $branchId);
            }

            $data = $query->with('expenseType:id,name')
                ->groupBy('expense_type_id')
                ->orderBy('total_amount', 'desc')
                ->get();

            $totalAmount = $data->sum('total_amount');

            // Calculate percentage for each type
            $data = $data->map(function ($item) use ($totalAmount) {
                $item->percentage = $totalAmount > 0 ? round(($item->total_amount / $totalAmount) * 100, 2) : 0;
                return $item;
            });

            return [
                'data' => $data,
                'summary' => [
                    'total_amount' => $totalAmount,
                    'total_transactions' => $data->sum('total_transactions'),
                    'total_categories' => $data->count()
                ]
            ];
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function paymentMethodReport($request)
    {
        try {
            $fromDate = $request->get('from_date');
            $toDate = $request->get('to_date');
            $branchId = $request->get('branch_id');

            $query = Expense::select(
                'payment_method_id',
                DB::raw('COUNT(*) as total_transactions'),
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('AVG(amount) as average_amount')
            )
            ->where('is_deleted', false)
            ->where('status', '!=', 'rejected');

            if ($fromDate) {
                $query->whereDate('expense_date', '>=', $fromDate);
            }
            if ($toDate) {
                $query->whereDate('expense_date', '<=', $toDate);
            }
            if ($branchId) {
                $query->where('branch_id', $branchId);
            }

            $data = $query->with('paymentMethod:id,name')
                ->groupBy('payment_method_id')
                ->orderBy('total_amount', 'desc')
                ->get();

            $totalAmount = $data->sum('total_amount');

            // Calculate percentage for each payment method
            $data = $data->map(function ($item) use ($totalAmount) {
                $item->percentage = $totalAmount > 0 ? round(($item->total_amount / $totalAmount) * 100, 2) : 0;
                return $item;
            });

            return [
                'data' => $data,
                'summary' => [
                    'total_amount' => $totalAmount,
                    'total_transactions' => $data->sum('total_transactions'),
                    'total_payment_methods' => $data->count()
                ]
            ];
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}

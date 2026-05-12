<?php

namespace App\Services;

use Exception;
use App\Models\Expense;
use App\Models\Branch;
use App\Models\ExpenseType;
use App\Models\ExpensePaymentMethod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ShopExpenseReportService
{
    public function dailySummary($request)
    {
        try {
            $fromDate = $request->get('from_date');
            $toDate = $request->get('to_date');

            // Get all branches
            $branches = Branch::orderBy('name')->get();

            // Get date range
            $query = Expense::select('expense_date')
                ->where('is_deleted', false)
                ->where('status', '!=', 'rejected')
                ->groupBy('expense_date')
                ->orderBy('expense_date', 'asc');

            if ($fromDate) {
                $query->whereDate('expense_date', '>=', $fromDate);
            }
            if ($toDate) {
                $query->whereDate('expense_date', '<=', $toDate);
            }

            $dates = $query->pluck('expense_date')->toArray();

            // Get expense data
            $expenseQuery = Expense::select(
                'branch_id',
                'expense_date',
                DB::raw('SUM(amount) as total_amount')
            )
            ->where('is_deleted', false)
            ->where('status', '!=', 'rejected');

            if ($fromDate) {
                $expenseQuery->whereDate('expense_date', '>=', $fromDate);
            }
            if ($toDate) {
                $expenseQuery->whereDate('expense_date', '<=', $toDate);
            }

            $expenseData = $expenseQuery->groupBy('branch_id', 'expense_date')->get();

            // Build matrix data
            $matrix = [];
            $branchTotals = [];
            $dateTotals = [];
            $grandTotal = 0;

            foreach ($branches as $branch) {
                $row = [
                    'branch_id' => $branch->id,
                    'branch_name' => $branch->name,
                    'dates' => []
                ];

                $branchTotal = 0;

                foreach ($dates as $date) {
                    $expense = $expenseData->first(function ($item) use ($branch, $date) {
                        return $item->branch_id == $branch->id && $item->expense_date == $date;
                    });

                    $amount = $expense ? floatval($expense->total_amount) : 0;
                    $dateKey = date('Y-m-d', strtotime($date));
                    $row['dates'][$dateKey] = $amount;
                    $branchTotal += $amount;

                    if (!isset($dateTotals[$dateKey])) {
                        $dateTotals[$dateKey] = 0;
                    }
                    $dateTotals[$dateKey] += $amount;
                    $grandTotal += $amount;
                }

                $row['total'] = $branchTotal;
                $branchTotals[$branch->id] = $branchTotal;
                $matrix[] = $row;
            }

            return [
                'dates' => $dates,
                'branches' => $branches,
                'matrix' => $matrix,
                'date_totals' => $dateTotals,
                'grand_total' => $grandTotal
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

            // Get all branches
            $branches = Branch::orderBy('name')->get();

            // Get all expense types
            $expenseTypes = ExpenseType::orderBy('name')->get();

            // Get expense data
            $expenseQuery = Expense::select(
                'branch_id',
                'expense_type_id',
                DB::raw('SUM(amount) as total_amount')
            )
            ->where('is_deleted', false)
            ->where('status', '!=', 'rejected');

            if ($fromDate) {
                $expenseQuery->whereDate('expense_date', '>=', $fromDate);
            }
            if ($toDate) {
                $expenseQuery->whereDate('expense_date', '<=', $toDate);
            }

            $expenseData = $expenseQuery->groupBy('branch_id', 'expense_type_id')->get();

            // Build matrix data
            $matrix = [];
            $typeTotals = [];
            $grandTotal = 0;

            foreach ($branches as $branch) {
                $row = [
                    'branch_id' => $branch->id,
                    'branch_name' => $branch->name,
                    'types' => []
                ];

                $branchTotal = 0;

                foreach ($expenseTypes as $type) {
                    $expense = $expenseData->first(function ($item) use ($branch, $type) {
                        return $item->branch_id == $branch->id && $item->expense_type_id == $type->id;
                    });

                    $amount = $expense ? floatval($expense->total_amount) : 0;
                    $row['types'][$type->id] = $amount;
                    $branchTotal += $amount;

                    if (!isset($typeTotals[$type->id])) {
                        $typeTotals[$type->id] = 0;
                    }
                    $typeTotals[$type->id] += $amount;
                    $grandTotal += $amount;
                }

                $row['total'] = $branchTotal;
                $matrix[] = $row;
            }

            return [
                'expense_types' => $expenseTypes,
                'branches' => $branches,
                'matrix' => $matrix,
                'type_totals' => $typeTotals,
                'grand_total' => $grandTotal
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

            // Get all branches
            $branches = Branch::orderBy('name')->get();

            // Get all payment methods
            $paymentMethods = ExpensePaymentMethod::orderBy('name')->get();

            // Get expense data
            $expenseQuery = Expense::select(
                'branch_id',
                'payment_method_id',
                DB::raw('SUM(amount) as total_amount')
            )
            ->where('is_deleted', false)
            ->where('status', '!=', 'rejected');

            if ($fromDate) {
                $expenseQuery->whereDate('expense_date', '>=', $fromDate);
            }
            if ($toDate) {
                $expenseQuery->whereDate('expense_date', '<=', $toDate);
            }

            $expenseData = $expenseQuery->groupBy('branch_id', 'payment_method_id')->get();

            // Build matrix data
            $matrix = [];
            $methodTotals = [];
            $grandTotal = 0;

            foreach ($branches as $branch) {
                $row = [
                    'branch_id' => $branch->id,
                    'branch_name' => $branch->name,
                    'methods' => []
                ];

                $branchTotal = 0;

                foreach ($paymentMethods as $method) {
                    $expense = $expenseData->first(function ($item) use ($branch, $method) {
                        return $item->branch_id == $branch->id && $item->payment_method_id == $method->id;
                    });

                    $amount = $expense ? floatval($expense->total_amount) : 0;
                    $row['methods'][$method->id] = $amount;
                    $branchTotal += $amount;

                    if (!isset($methodTotals[$method->id])) {
                        $methodTotals[$method->id] = 0;
                    }
                    $methodTotals[$method->id] += $amount;
                    $grandTotal += $amount;
                }

                $row['total'] = $branchTotal;
                $matrix[] = $row;
            }

            return [
                'payment_methods' => $paymentMethods,
                'branches' => $branches,
                'matrix' => $matrix,
                'method_totals' => $methodTotals,
                'grand_total' => $grandTotal
            ];
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}

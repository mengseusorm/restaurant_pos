<?php

namespace App\Services;

use Exception;
use App\Models\Expense;
use App\Exports\ExpenseExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;

class ExpenseService
{
    public object $expense;
    protected array $expenseFilter = [
        'expense_code',
        'expense_date',
        'expense_type_id',
        'payment_method_id',
        'paid_to',
        'status',
        'branch_id'
    ];

    protected array $exceptFilter = [
        'excepts'
    ];

    public function list($request)
    {
        try {
            $requests    = $request->all();
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType   = $request->get('order_type') ?? 'desc';

            return Expense::with('branch', 'expenseType', 'paymentMethod', 'recordedBy', 'approvedBy')
                ->where(function ($query) use ($requests) {
                    foreach ($requests as $key => $request) {
                        if (in_array($key, $this->expenseFilter)) {
                            if ($key == 'expense_date') {
                                $query->whereDate($key, $request);
                            } elseif ($key == 'expense_type_id' || $key == 'payment_method_id' || $key == 'branch_id') {
                                $query->where($key, $request);
                            } elseif ($key == 'status') {
                                $query->where($key, $request);
                            } else {
                                $query->where($key, 'like', '%' . $request . '%');
                            }
                        }

                        if (in_array($key, $this->exceptFilter)) {
                            $explodes = explode('|', $request);
                            if (is_array($explodes)) {
                                foreach ($explodes as $explode) {
                                    $query->where('id', '!=', $explode);
                                }
                            }
                        }
                    }

                    // Handle date range filters
                    if (!empty($requests['from_date'])) {
                        $query->whereDate('expense_date', '>=', $requests['from_date']);
                    }
                    if (!empty($requests['to_date'])) {
                        $query->whereDate('expense_date', '<=', $requests['to_date']);
                    }
                })
                ->where('is_deleted', false)
                ->orderBy($orderColumn, $orderType)
                ->$method($methodValue);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function store($request)
    {
        try {
            DB::transaction(function () use ($request) {
                $this->expense = Expense::create([
                    'branch_id'         => $request->branch_id,
                    'expense_code'      => $this->generateExpenseCode($request->branch_id),
                    'expense_date'      => $request->expense_date,
                    'expense_type_id'   => $request->expense_type_id,
                    'amount'            => $request->amount,
                    'payment_method_id' => $request->payment_method_id,
                    'description'       => $request->description,
                    'paid_to'           => $request->paid_to,
                    'reference_no'      => $request->reference_no,
                    'recorded_by'       => Auth::id(),
                    'approved_by'       => null,
                    'status'            => $request->status ?? 'pending',
                    'is_deleted'        => false
                ]);

                if ($request->receipt_image) {
                    $this->expense->addMedia($request->receipt_image)->toMediaCollection('expense');
                }
            });
            return $this->expense;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function update($request, $expense)
    {
        try {
            DB::transaction(function () use ($request, $expense) {
                $expense->update([
                    'branch_id'         => $request->branch_id,
                    'expense_date'      => $request->expense_date,
                    'expense_type_id'   => $request->expense_type_id,
                    'amount'            => $request->amount,
                    'payment_method_id' => $request->payment_method_id,
                    'description'       => $request->description,
                    'paid_to'           => $request->paid_to,
                    'reference_no'      => $request->reference_no,
                    'status'            => $request->status
                ]);

                if ($request->receipt_image) {
                    $expense->clearMediaCollection('expense');
                    $expense->addMedia($request->receipt_image)->toMediaCollection('expense');
                }
            });
            return $expense;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function destroy($expense)
    {
        try {
            DB::transaction(function () use ($expense) {
                $expense->update(['is_deleted' => true]);
                $expense->delete();
            });
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function show($expense): Expense
    {
        try {
            return $expense->load('branch', 'expenseType', 'paymentMethod', 'recordedBy', 'approvedBy');
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function export($request): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        try {
            return Excel::download(new ExpenseExport($this->list($request)), 'Expense.xlsx');
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function getStatistics($request): array
    {
        try {
            $requests = $request->all();
            
            $query = Expense::where('is_deleted', false);
            
            foreach ($requests as $key => $request) {
                if (in_array($key, $this->expenseFilter)) {
                    if ($key == 'expense_date') {
                        $query->whereDate($key, $request);
                    } else {
                        $query->where($key, 'like', '%' . $request . '%');
                    }
                }
            }

            $totalExpenses = $query->count();
            $totalAmount = $query->sum('amount');
            $pendingExpenses = (clone $query)->where('status', 'pending')->count();
            $approvedExpenses = (clone $query)->where('status', 'approved')->count();
            $rejectedExpenses = (clone $query)->where('status', 'rejected')->count();

            return [
                'total_expenses'    => $totalExpenses,
                'total_amount'      => number_format($totalAmount, 2),
                'pending_expenses'  => $pendingExpenses,
                'approved_expenses' => $approvedExpenses,
                'rejected_expenses' => $rejectedExpenses
            ];
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function approve($request, $expense)
    {
        try {
            $expense->update([
                'status'      => 'approved',
                'approved_by' => Auth::id()
            ]);
            return $expense;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function reject($request, $expense)
    {
        try {
            $expense->update([
                'status'      => 'rejected',
                'approved_by' => Auth::id()
            ]);
            return $expense;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    private function generateExpenseCode($branchId): string
    {
        $date = date('Ymd');
        $lastExpense = Expense::where('branch_id', $branchId)
            ->whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastExpense ? intval(substr($lastExpense->expense_code, -3)) + 1 : 1;
        
        return 'EXP-' . $date . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }
}

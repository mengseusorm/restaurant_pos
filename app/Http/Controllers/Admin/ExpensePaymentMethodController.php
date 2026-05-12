<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ExpensePaymentMethodExport;
use App\Http\Requests\ExpensePaymentMethodRequest;
use App\Http\Requests\PaginateRequest;
use App\Http\Resources\ExpensePaymentMethodResource;
use App\Models\ExpensePaymentMethod;
use App\Services\ExpensePaymentMethodService;
use Exception;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExpensePaymentMethodController extends AdminController
{
    public ExpensePaymentMethodService $expensePaymentMethodService;

    public function __construct(ExpensePaymentMethodService $expensePaymentMethodService)
    {
        parent::__construct();
        $this->expensePaymentMethodService = $expensePaymentMethodService;
        $this->middleware(['permission:expense-payment-methods'])->only('index', 'export', 'statistics', 'active');
        $this->middleware(['permission:expense-payment-methods_create'])->only('store');
        $this->middleware(['permission:expense-payment-methods_edit'])->only('update');
        $this->middleware(['permission:expense-payment-methods_show'])->only('show');
        $this->middleware(['permission:expense-payment-methods_delete'])->only('destroy');
    }

    /**
     * Display a listing of expense payment methods
     */
    public function index(PaginateRequest $request): \Illuminate\Http\Response|\Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return ExpensePaymentMethodResource::collection($this->expensePaymentMethodService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Get all active expense payment methods
     */
    public function active(): \Illuminate\Http\Response|\Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $expensePaymentMethods = ExpensePaymentMethod::active()->get();
            return ExpensePaymentMethodResource::collection($expensePaymentMethods);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Store a newly created expense payment method
     */
    public function store(ExpensePaymentMethodRequest $request): \Illuminate\Http\Response|ExpensePaymentMethodResource|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return new ExpensePaymentMethodResource($this->expensePaymentMethodService->store($request, new ExpensePaymentMethod()));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Display the specified expense payment method
     */
    public function show(ExpensePaymentMethod $expensePaymentMethod): \Illuminate\Http\Response|ExpensePaymentMethodResource|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return new ExpensePaymentMethodResource($this->expensePaymentMethodService->show($expensePaymentMethod));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Update the specified expense payment method
     */
    public function update(ExpensePaymentMethodRequest $request, ExpensePaymentMethod $expensePaymentMethod): \Illuminate\Http\Response|ExpensePaymentMethodResource|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return new ExpensePaymentMethodResource($this->expensePaymentMethodService->update($request, $expensePaymentMethod));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Remove the specified expense payment method
     */
    public function destroy(ExpensePaymentMethod $expensePaymentMethod): \Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $this->expensePaymentMethodService->destroy($expensePaymentMethod);
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Export expense payment methods
     */
    public function export(PaginateRequest $request): \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return Excel::download(new ExpensePaymentMethodExport($this->expensePaymentMethodService, $request), 'ExpensePaymentMethods.xlsx');
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Get expense payment method statistics
     */
    public function statistics(): \Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $statistics = $this->expensePaymentMethodService->getStatistics();
            
            return response([
                'status' => true,
                'data' => $statistics,
            ]);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}

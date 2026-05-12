<?php

namespace App\Http\Controllers\Admin;

use App\Models\Expense;
use App\Http\Requests\ExpenseRequest;
use App\Http\Resources\ExpenseResource;
use App\Services\ExpenseService;
use Exception;
use Illuminate\Http\Request;

class ExpenseController extends AdminController
{
    private ExpenseService $expenseService;

    public function __construct(ExpenseService $expenseService)
    {
        parent::__construct();
        $this->expenseService = $expenseService;
        $this->middleware(['permission:expenses'])->only('index', 'export', 'show');
        $this->middleware(['permission:expenses_create'])->only('store');
        $this->middleware(['permission:expenses_edit'])->only('update');
        $this->middleware(['permission:expenses_delete'])->only('destroy');
    }

    public function index(Request $request): \Illuminate\Http\Response | \Illuminate\Http\Resources\Json\AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return ExpenseResource::collection($this->expenseService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function store(ExpenseRequest $request): \Illuminate\Http\Response | ExpenseResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return new ExpenseResource($this->expenseService->store($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function update(ExpenseRequest $request, Expense $expense): \Illuminate\Http\Response | ExpenseResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return new ExpenseResource($this->expenseService->update($request, $expense));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function destroy(Expense $expense): \Illuminate\Http\Response | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $this->expenseService->destroy($expense);
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function show(Expense $expense): \Illuminate\Http\Response | ExpenseResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return new ExpenseResource($this->expenseService->show($expense));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function export(Request $request): \Illuminate\Http\Response | \Symfony\Component\HttpFoundation\BinaryFileResponse | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return $this->expenseService->export($request);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function statistics(Request $request): \Illuminate\Http\Response | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return response($this->expenseService->getStatistics($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function approve(Request $request, Expense $expense): \Illuminate\Http\Response | ExpenseResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return new ExpenseResource($this->expenseService->approve($request, $expense));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function reject(Request $request, Expense $expense): \Illuminate\Http\Response | ExpenseResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return new ExpenseResource($this->expenseService->reject($request, $expense));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}

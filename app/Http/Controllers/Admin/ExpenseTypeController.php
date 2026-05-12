<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ExpenseTypeExport;
use App\Http\Requests\ExpenseTypeRequest;
use App\Http\Requests\PaginateRequest;
use App\Http\Resources\ExpenseTypeResource;
use App\Models\ExpenseType;
use App\Services\ExpenseTypeService;
use Exception;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExpenseTypeController extends AdminController
{
    public ExpenseTypeService $expenseTypeService;

    public function __construct(ExpenseTypeService $expenseTypeService)
    {
        parent::__construct();
        $this->expenseTypeService = $expenseTypeService;
        $this->middleware(['permission:expense-types'])->only('index', 'export', 'statistics', 'active');
        $this->middleware(['permission:expense-types_create'])->only('store');
        $this->middleware(['permission:expense-types_edit'])->only('update');
        $this->middleware(['permission:expense-types_show'])->only('show');
        $this->middleware(['permission:expense-types_delete'])->only('destroy');
    }

    /**
     * Display a listing of expense types
     */
    public function index(PaginateRequest $request): \Illuminate\Http\Response|\Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return ExpenseTypeResource::collection($this->expenseTypeService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Get all active expense types
     */
    public function active(): \Illuminate\Http\Response|\Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $expenseTypes = ExpenseType::active()->get();
            return ExpenseTypeResource::collection($expenseTypes);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Store a newly created expense type
     */
    public function store(ExpenseTypeRequest $request): \Illuminate\Http\Response|ExpenseTypeResource|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return new ExpenseTypeResource($this->expenseTypeService->store($request, new ExpenseType()));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Display the specified expense type
     */
    public function show(ExpenseType $expenseType): \Illuminate\Http\Response|ExpenseTypeResource|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return new ExpenseTypeResource($this->expenseTypeService->show($expenseType));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Update the specified expense type
     */
    public function update(ExpenseTypeRequest $request, ExpenseType $expenseType): \Illuminate\Http\Response|ExpenseTypeResource|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return new ExpenseTypeResource($this->expenseTypeService->update($request, $expenseType));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Remove the specified expense type
     */
    public function destroy(ExpenseType $expenseType): \Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $this->expenseTypeService->destroy($expenseType);
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Export expense types
     */
    public function export(PaginateRequest $request): \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return Excel::download(new ExpenseTypeExport($this->expenseTypeService, $request), 'ExpenseTypes.xlsx');
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Get expense type statistics
     */
    public function statistics(): \Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $statistics = $this->expenseTypeService->getStatistics();
            
            return response([
                'status' => true,
                'data' => $statistics,
            ]);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Exports\PaywayTransactionExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\PaginateRequest;
use App\Http\Resources\PaywayTransactionResource;
use App\Models\PaywayTransaction;
use App\Services\PaywayTransactionService;
use Exception;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class PaywayTransactionController extends Controller
{
    protected PaywayTransactionService $paywayTransactionService;

    public function __construct(PaywayTransactionService $paywayTransactionService)
    {
        $this->paywayTransactionService = $paywayTransactionService;
    }

    /**
     * Display a listing of PayWay transactions
     */
    public function index(PaginateRequest $request)
    {
        try {
            return PaywayTransactionResource::collection($this->paywayTransactionService->list($request));
        } catch (Exception $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Display the specified PayWay transaction
     */
    public function show(PaywayTransaction $paywayTransaction)
    {
        try {
            return new PaywayTransactionResource($this->paywayTransactionService->show($paywayTransaction));
        } catch (Exception $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Export PayWay transactions to Excel
     */
    public function export(PaginateRequest $request):\Illuminate\Http\Response | \Symfony\Component\HttpFoundation\BinaryFileResponse | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try { 
            return Excel::download(new PaywayTransactionExport($this->paywayTransactionService, $request), 'Payway-Transaction.xlsx'); 
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422); 
        }
    }
}

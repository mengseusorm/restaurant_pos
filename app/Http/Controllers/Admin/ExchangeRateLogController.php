<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\PaginateRequest;
use App\Http\Resources\ExchangeRateLogResource;
use App\Services\ExchangeRateLogService;
use Exception;

class ExchangeRateLogController extends AdminController
{
    private ExchangeRateLogService $exchangeRateLogService;

    public function __construct(ExchangeRateLogService $exchangeRateLogService)
    {
        parent::__construct();
        $this->exchangeRateLogService = $exchangeRateLogService;
        $this->middleware(['permission:settings'])->only('index');
    }

    public function index(PaginateRequest $request) : \Illuminate\Http\Response | \Illuminate\Http\Resources\Json\AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return ExchangeRateLogResource::collection($this->exchangeRateLogService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}

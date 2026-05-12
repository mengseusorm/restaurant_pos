<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\OrderTypeRequest;
use App\Http\Requests\PaginateRequest;
use App\Http\Resources\OrderTypeResource;
use App\Models\OrderType;
use App\Services\OrderTypeService;
use Exception;

class OrderTypeController extends AdminController
{
    private OrderTypeService $orderTypeService;

    public function __construct(OrderTypeService $orderTypeService)
    {
        parent::__construct();
        $this->orderTypeService = $orderTypeService;
        // $this->middleware(['permission:settings'])->only('store', 'update', 'destroy', 'show');
    }

    public function index(PaginateRequest $request) : \Illuminate\Http\Response | \Illuminate\Http\Resources\Json\AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return OrderTypeResource::collection($this->orderTypeService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function store(OrderTypeRequest $request) : OrderTypeResource | \Illuminate\Http\Response | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return new OrderTypeResource($this->orderTypeService->store($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function update(OrderTypeRequest $request, OrderType $orderType) : OrderTypeResource | \Illuminate\Http\Response | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return new OrderTypeResource($this->orderTypeService->update($request, $orderType));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function destroy(OrderType $orderType) : \Illuminate\Http\Response | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $this->orderTypeService->destroy($orderType);
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function show(OrderType $orderType) : OrderTypeResource | \Illuminate\Http\Response | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return new OrderTypeResource($orderType);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}

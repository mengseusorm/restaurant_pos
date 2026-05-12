<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\OrderStatusRequest;
use App\Http\Requests\PaginateRequest;
use App\Http\Resources\OrderStatusResource;
use App\Models\OrderStatus;
use App\Services\OrderStatusService;
use Exception;

class OrderStatusController extends AdminController
{
    private OrderStatusService $orderStatusService;

    public function __construct(OrderStatusService $orderStatusService)
    {
        parent::__construct();
        $this->orderStatusService = $orderStatusService;
        $this->middleware(['permission:settings'])->only('store', 'update', 'destroy', 'show');
    }

    public function index(PaginateRequest $request) : \Illuminate\Http\Response | \Illuminate\Http\Resources\Json\AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return OrderStatusResource::collection($this->orderStatusService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function store(OrderStatusRequest $request) : OrderStatusResource | \Illuminate\Http\Response | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return new OrderStatusResource($this->orderStatusService->store($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function update(OrderStatusRequest $request, OrderStatus $orderStatus) : OrderStatusResource | \Illuminate\Http\Response | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return new OrderStatusResource($this->orderStatusService->update($request, $orderStatus));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function destroy(OrderStatus $orderStatus) : \Illuminate\Http\Response | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $this->orderStatusService->destroy($orderStatus);
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function show(OrderStatus $orderStatus) : OrderStatusResource | \Illuminate\Http\Response | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return new OrderStatusResource($orderStatus);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}

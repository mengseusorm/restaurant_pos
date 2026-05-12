<?php

namespace App\Http\Controllers\OnlineOrder;


use App\Http\Controllers\Controller;
use App\Http\Requests\OnlineOrderOrderRequest;
use App\Http\Requests\TableOrderRequest;
use App\Models\FrontendOrder;
use App\Models\Order;
use Exception;
use App\Services\OrderService;
use App\Http\Resources\OrderDetailsResource;


class OnlineOrderOrderController extends Controller
{
    private OrderService $orderService;

    public function __construct(OrderService $order)
    {
        $this->orderService = $order;
    }

    public function store(OnlineOrderOrderRequest $request): \Illuminate\Http\Response|OrderDetailsResource|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return new OrderDetailsResource($this->orderService->onlineOrderOrderStore($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function show(FrontendOrder $frontendOrder): \Illuminate\Http\Response|OrderDetailsResource|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $frontendOrder->load('paymentMethod');
            $frontendOrder->load('posPaymentMethod');
            return new OrderDetailsResource($frontendOrder);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
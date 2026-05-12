<?php

namespace App\Http\Controllers\TelegramMiniApp;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\TelegramMiniAppOrderRequest;
use App\Models\FrontendOrder;
use Exception;
use App\Services\OrderService;
use App\Http\Resources\OrderDetailsResource;
use App\Http\Resources\OrderResource;
use App\Http\Resources\FrontendOrderResource;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class TelegramMiniAppOrderController extends Controller
{
    private OrderService $orderService;

    public function __construct(OrderService $order)
    {
        $this->orderService = $order;
    }

    public function store(TelegramMiniAppOrderRequest $request): \Illuminate\Http\Response|OrderDetailsResource|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            Log::info('TelegramMiniAppOrderRequest data: ', $request->all());
            return new OrderDetailsResource($this->orderService->telegramMiniAppOrderStore($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function show(FrontendOrder $frontendOrder): \Illuminate\Http\Response|OrderDetailsResource|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            // $frontendOrder->load('paymentMethod');
            // $frontendOrder->load('posPaymentMethod');
            $frontendOrder->load([
                'paymentMethod', 
                'posPaymentMethod', 
                'transactions'
            ]);
            
            Log::info('TelegramMiniApp FrontendOrder data: ', $frontendOrder->toArray());
            return new OrderDetailsResource($frontendOrder);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function getOrderCount($telegramUserId): \Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $count = FrontendOrder::where('telegram_user_id', $telegramUserId)->count();
            return response(['status' => true, 'data' => ['count' => $count]], 200);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function getUserOrders($telegramUserId, PaginateRequest $request)
    {
        try {
            
            $orders = $this->orderService->getTelegramUserOrders($telegramUserId, $request);
            
            return FrontendOrderResource::collection($orders);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
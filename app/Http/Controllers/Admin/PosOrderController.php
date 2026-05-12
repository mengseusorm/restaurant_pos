<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PaymentStatus;
use App\Enums\PosPaymentMethod;
use Exception;
use App\Models\Order;
use App\Exports\OrderExport;
use App\Http\Requests\ChangeOrderStatusRequest;
use App\Http\Requests\ItemRequest;
use App\Services\OrderService;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Resources\OrderResource;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\OrderStatusRequest;
use App\Http\Requests\PaymentStatusRequest;
use App\Http\Requests\PosPaymentMethodRequest;
use App\Http\Resources\DiningTableResource;
use App\Http\Resources\OrderDeletedResource;
use App\Http\Resources\OrderDetailsResource;
use App\Http\Resources\OrderItemResource;
use App\Models\DiningTable;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PosOrderController extends AdminController
{
    private OrderService $orderService;

    public function __construct(OrderService $order)
    {
        parent::__construct();
        $this->orderService = $order;
        $this->middleware(['permission:pos-orders'])->only(
            'index',
            'show',
            'destroy',
            'export',
            'changeStatus',
            'changePaymentStatus',
            'changePaymentMethod',
        );
    }

    public function index(
        PaginateRequest $request
    ): \Illuminate\Http\Response | \Illuminate\Http\Resources\Json\AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return OrderResource::collection($this->orderService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function listorderDeleted(
        PaginateRequest $request
    ): \Illuminate\Http\Response | \Illuminate\Http\Resources\Json\AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return OrderDeletedResource::collection($this->orderService->listOrderDeleted($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function listPending(
        PaginateRequest $request
    ): \Illuminate\Http\Response | \Illuminate\Http\Resources\Json\AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return OrderResource::collection($this->orderService->listPendings($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function listUnpaid(
        PaginateRequest $request
    ): \Illuminate\Http\Response | \Illuminate\Http\Resources\Json\AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return OrderResource::collection($this->orderService->listUnpaids($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function show(
        Order $order
    ): \Illuminate\Http\Response | OrderDetailsResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            $order->load('paymentMethod');
            $order->load('posPaymentMethod');
            $order->load('member');
            return new OrderDetailsResource($this->orderService->show($order, false));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    // /**
    //  * @deprecated
    //  */
    // public function destroyPosOrder(
    //     $id,
    //     Order $order
    // ): \Illuminate\Http\Response | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
    //     try {
    //         $this->orderService->destroyPosOrder($id,$order);
    //         return response('', 202);
    //     } catch (Exception $exception) {
    //         return response(['status' => false, 'message' => $exception->getMessage()], 422);
    //     }
    // }

    public function destroyOrderItem(
        Request $request,
        OrderItem $orderItem
    ): \Illuminate\Http\Response | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            $this->orderService->destroyOrderItem($orderItem,$request);
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function destroy(
        Order $order
    ): \Illuminate\Http\Response | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            $this->orderService->destroy($order);
            return response('', 202);
        } catch (Exception $exception) {
            Log::info($exception);
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function updateOrderInfo(Order $order, Request $request): \Illuminate\Http\Response | OrderDetailsResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            if ($order->payment_status === PaymentStatus::PAID) {
                return response(['status' => false, 'message' => 'Cannot update a paid order.'], 403);
            }
            return new OrderDetailsResource($this->orderService->updateOrderInfo($order, $request));
        } catch (Exception $exception) {
            Log::error('Error updating order items: ' . $exception->getMessage());
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }


    public function updateOrderItem(OrderItem $orderItem, Request $request): \Illuminate\Http\Response | OrderItemResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return new OrderItemResource($this->orderService->updateOrderItems($orderItem, $request));
        } catch (Exception $exception) {
            Log::error('Error updating order items: ' . $exception->getMessage());
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function payOrder(Request $request): \Illuminate\Http\Response | OrderDetailsResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return new OrderDetailsResource($this->orderService->payOrder($request));
        } catch (Exception $exception) {
            Log::error('Error updating order items: ' . $exception->getMessage());
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function export(
        PaginateRequest $request
    ): \Illuminate\Http\Response | \Symfony\Component\HttpFoundation\BinaryFileResponse | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return Excel::download(new OrderExport($this->orderService, $request), 'Online-Order.xlsx');
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function changeStatus(
        Order $order,
        // OrderStatusRequest $request 
        ChangeOrderStatusRequest $request
    ): \Illuminate\Http\Response | OrderDetailsResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return new OrderDetailsResource($this->orderService->changeStatus($order, false, $request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function changePaymentStatus(
        Order $order,
        PaymentStatusRequest $request
    ): \Illuminate\Http\Response | OrderDetailsResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return new OrderDetailsResource($this->orderService->changePaymentStatus($order, false, $request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
    public function changePaymentMethod(
        Order $order,
        PosPaymentMethodRequest $request
    ): \Illuminate\Http\Response | OrderDetailsResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            Log::info($request->all());
            return new OrderDetailsResource($this->orderService->changePaymentMethod($order, false, $request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
    public function addDiningTable(
        Order $order,
        Request $request
    ): \Illuminate\Http\Response | OrderDetailsResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return new OrderDetailsResource($this->orderService->addDiningTable($order, $request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
    public function releaseDiningTable(
        DiningTable $diningTable
    ): \Illuminate\Http\Response | DiningTableResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return new DiningTableResource($this->orderService->releaseDiningTable($diningTable));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function discount(
        Order $order,
        Request $request
    ): \Illuminate\Http\Response | OrderDetailsResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return new OrderDetailsResource($this->orderService->discount($order, $request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Find member by phone or card number
     */
    public function findMember(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $request->validate([
                'identifier' => 'required|string',
            ]);

            $member = $this->orderService->findMemberByPhoneOrCard($request->identifier);

            if ($member) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'id' => $member->id,
                        'name' => $member->name,
                        'phone' => $member->phone,
                        'card_number' => $member->card_number,
                        'point_balance' => $member->point_balance,
                    ],
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Member not found',
            ], 404);
        } catch (Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }
    }

    /**
     * Get member point summary for order
     */
    public function getMemberPointSummary(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $request->validate([
                'member_id' => 'required|integer|exists:members,id',
                'order_total' => 'required|numeric|min:0',
            ]);

            $member = \App\Models\Member::findOrFail($request->member_id);
            $pointsEarned = $this->orderService->calculatePointsEarned((object) ['total' => $request->order_total]);

            return response()->json([
                'success' => true,
                'data' => [
                    'member' => [
                        'id' => $member->id,
                        'name' => $member->name,
                        'current_balance' => $member->point_balance,
                    ],
                    'points_earned' => $pointsEarned,
                    'projected_balance' => $member->point_balance + $pointsEarned,
                ],
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }
    }

    /**
     * Remove member from order
     */
    public function removeMemberFromOrder(Order $order): \Illuminate\Http\Response | OrderDetailsResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            if ($order->payment_status === PaymentStatus::PAID) {
                return response(['status' => false, 'message' => 'Cannot update a paid order.'], 403);
            }
            return new OrderDetailsResource($this->orderService->removeMemberFromOrder($order));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Set member to order
     */
    public function setMemberToOrder(Order $order, Request $request): \Illuminate\Http\Response | OrderDetailsResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            if ($order->payment_status === PaymentStatus::PAID) {
                return response(['status' => false, 'message' => 'Cannot update a paid order.'], 403);
            }
            $request->validate([
                'member_id' => 'required|integer|exists:members,id',
            ]);
            return new OrderDetailsResource($this->orderService->setMemberToOrder($order, $request->member_id));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Count all online orders with pending status
     *
     * @return \Illuminate\Http\Response | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
     */
    public function countPending(): \Illuminate\Http\Response | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $count = $this->orderService->countPendingOrders();

            return response([
                'status' => true,
                'data' => [
                    'count' => $count
                ]
            ], 200);
        } catch (Exception $exception) {
            return response([
                'status' => false,
                'message' => $exception->getMessage()
            ], 422);
        }
    }

    /**
     * Combine multiple orders into one target order
     * Moves all order items and dining tables from source orders to target order
     *
     * @param CombineOrderRequest $request
     * @return \Illuminate\Http\Response | OrderDetailsResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
     */
    public function combineOrders(\App\Http\Requests\CombineOrderRequest $request): \Illuminate\Http\Response | OrderDetailsResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            // Validate that source orders are not paid
            $sourceOrders = Order::whereIn('id', $request->source_order_ids)->get();
            foreach ($sourceOrders as $order) {
                if ($order->payment_status === PaymentStatus::PAID) {
                    return response([
                        'status' => false,
                        'message' => "Cannot combine paid order #{$order->order_serial_no}. Only unpaid orders can be combined."
                    ], 403);
                }
            }

            // Validate that target order is not paid
            $targetOrder = Order::findOrFail($request->target_order_id);
            if ($targetOrder->payment_status === PaymentStatus::PAID) {
                return response([
                    'status' => false,
                    'message' => "Cannot combine into paid order #{$targetOrder->order_serial_no}. Target order must be unpaid."
                ], 403);
            }

            // Perform the combination
            $combinedOrder = $this->orderService->combineOrders(
                $request->source_order_ids,
                $request->target_order_id
            );

            return new OrderDetailsResource($combinedOrder);
        } catch (Exception $exception) {
            return response([
                'status' => false,
                'message' => $exception->getMessage()
            ], 422);
        }
    }

    public function transferItems(\App\Http\Requests\TransferOrderItemsRequest $request): \Illuminate\Http\Response | OrderDetailsResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            // Validate that source order is not paid
            $sourceOrder = Order::findOrFail($request->sourceOrderId);
            if ($sourceOrder->payment_status === PaymentStatus::PAID) {
                return response([
                    'status' => false,
                    'message' => "Cannot transfer items from paid order #{$sourceOrder->order_serial_no}."
                ], 403);
            }

            // Validate that target order is not paid
            $targetOrder = Order::findOrFail($request->targetOrderId);
            if ($targetOrder->payment_status === PaymentStatus::PAID) {
                return response([
                    'status' => false,
                    'message' => "Cannot transfer items to paid order #{$targetOrder->order_serial_no}."
                ], 403);
            }

            // Perform the transfer
            $updatedTargetOrder = $this->orderService->transferOrderItems(
                $request->sourceOrderId,
                $request->targetOrderId,
                $request->items
            );

            return new OrderDetailsResource($updatedTargetOrder);
        } catch (Exception $exception) {
            return response([
                'status' => false,
                'message' => $exception->getMessage()
            ], 422);
        }
    }
}

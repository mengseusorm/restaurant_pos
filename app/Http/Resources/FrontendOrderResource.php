<?php

namespace App\Http\Resources;

use App\Libraries\AppLibrary;
use Illuminate\Http\Resources\Json\JsonResource;

class FrontendOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id'                             => $this->id,
            'order_serial_no'                => $this->order_serial_no,

            'token'                          => $this->token,
            'number_of_people'              => $this->number_of_people,

            'points_earned'                   => $this->points_earned,
            'points_redeemed'                 => $this->points_redeemed,

            'user_id'                        => $this->user_id,
            'member_id'                      => $this->member_id,
            'order_user_id'                  => $this->order_user_id,
            'branch_id'                      => $this->branch_id,
            'branch_name'                    => optional($this->branch)->name,
            'order_items_count'                    => optional($this->orderItems)->count(),  //TODO: Use Resource
            'order_items'                    => OrderItemResource::collection($this->orderItems),
            // "total_currency_price"           => AppLibrary::currencyAmountFormat($this->total),
            // "total_tax_currency_price"       => AppLibrary::currencyAmountFormat($this->total_tax),
            "total_currency_price"           => AppLibrary::flatAmountFormat($this->total),
            "total_tax_currency_price"       => AppLibrary::flatAmountFormat($this->total_tax),
            // "total_amount_price"             => AppLibrary::flatAmountFormat($this->total),

            "total_amount_price"             => AppLibrary::flatAmountFormat($this->total + $this->total_tax),

            "paid_amount"                    => AppLibrary::flatAmountFormat($this->paid_amount ?? 0),
            "balance_due"                    => AppLibrary::flatAmountFormat($this->balance_due ?? ($this->total + $this->total_tax)),

            "pos_received_amount"                 => $this->pos_received_amount,
            "pos_change_amount"                   => $this->pos_received_amount - ($this->total + $this->total_tax),

            "discount_currency_price"        => AppLibrary::currencyAmountFormat($this->discount),
            'discount_percentage'             => $this->discount_percentage,
            "delivery_charge_currency_price" => AppLibrary::currencyAmountFormat($this->delivery_charge),
            'payment_method'                 => $this->payment_method,
            'payment_method_name'            => $this->paymentMethod->name ?? null,
            'payment_method_id'              => $this->paymentMethod->id ?? null,

            'pos_payment_method_name'        => $this->posPaymentMethod->name ?? null,
            'pos_payment_method_id'          => $this->posPaymentMethod->id ?? null,

            'payment_method_info'                 => $this->whenLoaded('paymentMethod', function () {
                return $this->paymentMethod ? new PaymentMethodMinimalResource($this->paymentMethod) : null;
            }),

            'pos_payment_method_info'             => $this->whenLoaded('posPaymentMethod', function () {
                return $this->posPaymentMethod ? new PaymentMethodMinimalResource($this->posPaymentMethod) : null;
            }),

            // 'payment_method_info' => new PaymentMethodResource($this->whenLoaded('paymentMethod')),

            'payment_status'                 => $this->payment_status,
            'preparation_time'               => $this->preparation_time,
            'order_type'                     => $this->order_type,
            'order_datetime'                 => AppLibrary::datetime($this->order_datetime),
            'status'                         => $this->status,
            'source'                         => $this->source,

            'customer_name'                  => $this->customer_name,
            'customer_phone_number'          => $this->customer_phone_number,
            'customer_address'               => $this->customer_address,

            'is_advance_order'               => $this->is_advance_order,
            'status_name'                    => trans('orderStatus.' . $this->status),
            'customer'                       => new OrderUserMinimalResource($this->user),
            'transaction'                    => new TransactionResource($this->transaction),
            'transactions'                   => TransactionResource::collection($this->transactions ?? collect()),
            'waiting_number'                 => $this->waiting_number,
            'order_item_status'              => $this->order_item_status,
            'order_dinings'                  => OrderDiningResource::collection($this->orderDinings),
            'dining_tables'                  => DiningTableResource::collection($this->diningTables),
            'order_note'                     => $this->order_note,

            // Member and Points Information
            'points_earned'                  => $this->points_earned ?? 0,
            'points_redeemed'                => $this->points_redeemed ?? 0,
            'net_point_change'              => $this->net_point_change ?? 0,
            'formatted_points_earned'       => $this->formatted_points_earned ?? '0 points',
            'formatted_points_redeemed'     => $this->formatted_points_redeemed ?? '0 points',
            'customer_display_name'         => $this->customer_display_name ?? 'Guest Customer',
            'has_point_activity'            => $this->hasPointActivity() ?? false,

            'member'                        => $this->whenLoaded('member', function () {
                return $this->member ? new MemberMinimalResource($this->member) : null;
            }),
        ];
    }
}

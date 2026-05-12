<?php

namespace App\Http\Resources;

use App\Enums\Ask;
use App\Libraries\AppLibrary;
use App\Models\OrderStatus;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class OrderDetailsResource extends JsonResource
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
            'id'                                  => $this->id,
            'order_serial_no'                     => $this->order_serial_no,
            'invoice_number'                      => $this->invoice_number,
            'token'                               => $this->token,
            'number_of_people'                    => $this->number_of_people,

            'points_earned'                   => $this->points_earned,
            'points_redeemed'                 => $this->points_redeemed,

            "subtotal_currency_price"             => AppLibrary::branchCurrencyAmountFormat($this->subtotal,$this->branch),
            "discount_currency"                   => $this->discount,
            "subtotal_price"                      => $this->subtotal,
            // "subtotal_without_tax_currency_price" => AppLibrary::branchCurrencyAmountFormat($this->subtotal - $this->total_tax,$this->branch),
            "subtotal_without_tax_currency_price" => AppLibrary::branchCurrencyAmountFormat($this->subtotal, $this->branch),
            "discount_currency_price"             => AppLibrary::branchCurrencyAmountFormat($this->discount,$this->branch),
            'discount_percentage'                 => $this->discount_percentage,
            "delivery_charge_currency_price"      => AppLibrary::branchCurrencyAmountFormat($this->delivery_charge,$this->branch),
            "total_currency_price"                => AppLibrary::branchCurrencyAmountFormat($this->total + $this->total_tax,$this->branch),
            "total_tax_currency_price"            => AppLibrary::branchCurrencyAmountFormat($this->total_tax,$this->branch),

            "total_price"                         => AppLibrary::flatAmountFormat($this->total),
            "total_tax_price"                     => AppLibrary::flatAmountFormat($this->total_tax),
            "total_amount_price"                  => AppLibrary::flatAmountFormat($this->total + $this->total_tax),
            "paid_amount"                         => AppLibrary::flatAmountFormat($this->paid_amount ?? 0),
            "balance_due"                         => AppLibrary::flatAmountFormat($this->balance_due ?? ($this->total + $this->total_tax)),
            "pos_received_amount"                 => AppLibrary::flatAmountFormat($this->pos_received_amount),
            "pos_change_amount"                   => AppLibrary::flatAmountFormat($this->pos_received_amount - ($this->total + $this->total_tax)),

            'order_type'                          => $this->order_type,
            'order_datetime'                      => AppLibrary::datetime($this->order_datetime),
            'order_date'                          => AppLibrary::date($this->order_datetime),
            'order_time'                          => AppLibrary::time($this->order_datetime),
            'delivery_date'                       => $this->is_advance_order == Ask::YES ? AppLibrary::increaseDate($this->order_datetime, 1) : AppLibrary::date($this->order_datetime),
            'delivery_time'                       => AppLibrary::deliveryTime($this->delivery_time),
            'payment_method'                      => $this->payment_method,
            'payment_method_name'                 => $this->paymentMethod->name ?? null,
            'payment_method_id'                   => $this->paymentMethod->id ?? null,
            'payment_method_info'                 => $this->whenLoaded('paymentMethod', function () {
                return $this->paymentMethod ? new PaymentMethodMinimalResource($this->paymentMethod) : null;
            }),

            'pos_payment_method'                  => new PaymentMethodMinimalResource($this->posPaymentMethod),
            'pos_payment_note'                    => $this->pos_payment_note,
            'pos_payment_method_name'             => $this->posPaymentMethod->name ?? null,
            'pos_payment_method_id'               => $this->posPaymentMethod->id ?? null,
            'pos_payment_method_info'             => $this->whenLoaded('posPaymentMethod', function () {
                return $this->posPaymentMethod ? new PaymentMethodMinimalResource($this->posPaymentMethod) : null;
            }),

            'payment_status'                      => $this->payment_status,
            'is_advance_order'                    => $this->is_advance_order,
            'preparation_time'                    => $this->preparation_time,
            'status'                              => $this->orderStatus,
            'source'                              => $this->source,

            'customer_name'                  => $this->customer_name,
            'customer_phone_number'          => $this->customer_phone_number,
            'customer_address'               => $this->customer_address,

            'status_name'                         => trans('orderStatus.' . $this->status),
            'reason'                              => $this->reason,
            'user'                                => new OrderUserMinimalResource($this->user),
            'order_user'                          => new OrderUserMinimalResource($this->orderUser),
            'order_address'                       => new AddressResource($this->address),
            'branch'                              => new BranchMinimalResource($this->branch),
            'transaction'                         => new TransactionResource($this->transaction),
            'transactions'                        => TransactionResource::collection($this->transactions ?? collect()),
            'order_items'                         => OrderItemDetailResource::collection($this->orderItems ?? collect()),
            'order_items_unique'                  => OrderItemDetailResource::collection( $this->orderItemsUnique ?? $this->orderItems ?? collect()),

            // 'table_name'                          => $this->diningTable?->name,
            // 'table_name'                          => $this->diningTable,

            'waiting_number'                      => $this->waiting_number,
            'dining_tables'                       => $this->diningTables,
            'order_note'                          => $this->order_note,
            // 'payment_method_id'                   => $this->paymentMethod,

            'pos_received_currency_amount'        => AppLibrary::currencyAmountFormat($this->pos_received_amount),
            'cash_back_amount'                    => $this->pos_received_amount - $this->total,
            'cash_back_currency_amount'           => AppLibrary::currencyAmountFormat($this->pos_received_amount - $this->total),

            'member_id'                      => $this->member_id,
            // 'member'                        => $this->whenLoaded('member', function () {
            //     return $this->member ? new MemberMinimalResource($this->member) : null;
            // }),
            'member' => $this->relationLoaded('member') && $this->member
                ? new MemberMinimalResource($this->member)
                : null,

            'rejection_reason'                     => $this->rejection_reason,
            'rejected_at'                          => $this->rejected_at,

            'subtotal'                              => AppLibrary::flatAmountFormat($this->subtotal),
            'discount'                              => AppLibrary::flatAmountFormat($this->discount),
            'total_tax'                             => AppLibrary::flatAmountFormat($this->total_tax),
            'total'                                 => AppLibrary::flatAmountFormat($this->total),

            // Check-in and Check-out times
            'check_in_time'                 => $this->check_in_time ? AppLibrary::datetime($this->check_in_time) : null,
            'check_out_time'                => $this->check_out_time ? AppLibrary::datetime($this->check_out_time) : null,
            'checkout'                      => $this->check_out_time
                ? AppLibrary::datetime($this->check_out_time)
                : ($this->checkout ? AppLibrary::datetime($this->checkout) : null),
            'order_item_deleted'              => ItemOrderDeletedResource::collection($this->itemDeleted ?? collect()),
            'group_session_id'               => $this->group_session_id,
        ];
    }
}

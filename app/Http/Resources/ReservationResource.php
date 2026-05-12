<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'reservation_code' => $this->reservation_code,
            'customer_name' => $this->customer_name,
            'customer_phone' => $this->customer_phone,
            'customer_email' => $this->customer_email,
            'reservation_date' => $this->reservation_date,
            'reservation_time' => $this->reservation_time ? $this->reservation_time->format('H:i') : null,
            'number_of_people' => $this->number_of_people,
            'table_id' => $this->table_id,
            'table' => $this->table ? [
                'id' => $this->table->id,
                'name' => $this->table->name,
            ] : null,
            'status' => $this->status,
            'special_request' => $this->special_request,
            'created_by' => $this->created_by,
            'creator' => $this->createdBy ? [
                'id' => $this->createdBy->id,
                'name' => $this->createdBy->name,
            ] : null,
            'branch_id' => $this->branch_id,
            'branch' => $this->branch ? [
                'id' => $this->branch->id,
                'name' => $this->branch->name,
            ] : null,
            'deposit_amount' => $this->deposit_amount,
            'payment_status' => $this->payment_status,
            'check_in_time' => $this->check_in_time,
            'check_out_time' => $this->check_out_time,
            'cancel_reason' => $this->cancel_reason,
            'reminder_sent' => $this->reminder_sent,
            'duration_minutes' => $this->duration_minutes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

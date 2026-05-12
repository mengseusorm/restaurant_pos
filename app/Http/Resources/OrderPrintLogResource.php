<?php

namespace App\Http\Resources;

use App\Enums\PrintType;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderPrintLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'branch_id' => $this->branch_id,
            'order_serial_number' => $this->order_serial_number,
            'print_type' => $this->print_type,
            'print_type_name' => $this->getPrintTypeName(),
            'print_success' => $this->print_success,
            'print_success_text' => $this->print_success ? 'Success' : 'Failed',
            'error_message' => $this->error_message,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    private function getPrintTypeName()
    {
        switch ($this->print_type) {
            case PrintType::MENU:
                return 'Menu';
            case PrintType::INVOICE:
                return 'Invoice';
            case PrintType::BILL:
                return 'Bill';
            case PrintType::LABEL:
                return 'Label';
            default:
                return 'Unknown';
        }
    }
}

<?php

namespace App\Http\Resources;

use App\Libraries\AppLibrary;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceReportResource extends JsonResource
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
            'therapist_id'    => $this->therapist_id,
            'therapist_name'  => $this->therapist_name ?: 'N/A',
            'total_orders'    => (int) $this->total_orders,
            'total_customers' => (int) $this->total_customers,
            'total_hours'     => (float) $this->total_hours,
            'total_revenue'   => AppLibrary::flatAmountFormat($this->total_revenue),
        ];
    }
}

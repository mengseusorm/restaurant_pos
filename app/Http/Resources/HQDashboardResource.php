<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Libraries\AppLibrary;

class HQDashboardResource extends JsonResource
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
            'total_sales' => $this->resource['total_sales'], // Now handled in service layer
            'total_orders' => $this->resource['total_orders'],
            'total_customers' => $this->resource['total_customers'],
            'total_branches' => $this->resource['total_branches'],
            'branch_sales_comparison' => $this->resource['branch_sales_comparison'],
            'top_performing_branches' => $this->resource['top_performing_branches'],
            'order_status_summary' => $this->resource['order_status_summary'],
            'payment_method_summary' => $this->resource['payment_method_summary'],
            'sales_trend' => $this->resource['sales_trend'],
            'shop_category_sales' => $this->resource['shop_category_sales'],
        ];
    }
}

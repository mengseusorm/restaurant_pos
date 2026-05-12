<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PointUsageRuleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'branch_id' => $this->branch_id,
            'name' => $this->name,
            'usage_type' => $this->usage_type,
            'usage_type_label' => $this->getUsageTypeLabel(),
            'point_to_currency' => $this->point_to_currency,
            'min_point_usage' => $this->min_point_usage,
            'max_point_usage' => $this->max_point_usage,
            'is_active' => $this->is_active,
            'conversion_rate' => "1 point = {$this->point_to_currency} currency",
            'usage_range' => $this->getUsageRangeDescription(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Relationships
            'branch' => $this->whenLoaded('branch', function () {
                return [
                    'id' => $this->branch->id,
                    'name' => $this->branch->name,
                ];
            }),
        ];
    }

    /**
     * Get usage type label
     */
    private function getUsageTypeLabel(): string
    {
        return match($this->usage_type) {
            'deduct_order' => 'Deduct from Order',
            'exchange_gift' => 'Exchange for Gift',
            default => ucfirst(str_replace('_', ' ', $this->usage_type)),
        };
    }

    /**
     * Get usage range description
     */
    private function getUsageRangeDescription(): string
    {
        if ($this->max_point_usage) {
            return "From {$this->min_point_usage} to {$this->max_point_usage} points";
        }
        
        return "Minimum {$this->min_point_usage} points";
    }
}

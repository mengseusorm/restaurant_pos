<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PointGiftResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'branch_id' => $this->branch_id,
            'item_id' => $this->item_id,
            'required_points' => $this->required_points,
            'stock_limit' => $this->stock_limit,
            'redeemed_count' => $this->redeemed_count,
            'is_active' => $this->is_active,
            
            // Computed attributes
            'remaining_stock' => $this->remaining_stock,
            'is_unlimited_stock' => $this->is_unlimited_stock,
            'is_in_stock' => $this->is_in_stock,
            'item_name' => $this->item_name,
            'item_price' => $this->item_price,
            'formatted_required_points' => $this->formatted_required_points,
            'points_saved' => $this->points_saved,
            
            // Status labels
            'stock_status' => $this->getStockStatus(),
            'availability_status' => $this->getAvailabilityStatus(),
            
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Relationships
            'branch' => $this->whenLoaded('branch', function () {
                return [
                    'id' => $this->branch->id,
                    'name' => $this->branch->name,
                ];
            }),
            
            'item' => $this->whenLoaded('item', function () {
                return [
                    'id' => $this->item->id,
                    'name' => $this->item->name,
                    'price' => $this->item->price,
                    'image' => $this->item->image ?? null,
                    'category' => $this->item->category ?? null,
                ];
            }),
        ];
    }

    /**
     * Get stock status description
     */
    private function getStockStatus(): string
    {
        if ($this->is_unlimited_stock) {
            return 'Unlimited';
        }
        
        if ($this->remaining_stock > 0) {
            return "In Stock ({$this->remaining_stock} remaining)";
        }
        
        return 'Out of Stock';
    }

    /**
     * Get availability status
     */
    private function getAvailabilityStatus(): string
    {
        if (!$this->is_active) {
            return 'Inactive';
        }
        
        if (!$this->item || $this->item->trashed()) {
            return 'Item Not Available';
        }
        
        if (!$this->is_in_stock) {
            return 'Out of Stock';
        }
        
        return 'Available';
    }
}

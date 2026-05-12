<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PointEarnRuleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'branch_id' => $this->branch_id,
            'name' => $this->name, // Added field
            'currency_amount' => $this->currency_amount,
            'point' => $this->point,
            'is_active' => $this->is_active,
            'rate' => round($this->point / $this->currency_amount, 2),
            'description' => "Earn {$this->point} points for every {$this->currency_amount} spent",
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
}

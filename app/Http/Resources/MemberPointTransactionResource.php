<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MemberPointTransactionResource extends JsonResource
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
            'id' => $this->id,
            'member_id' => $this->member_id,
            'branch_id' => $this->branch_id,
            'type' => $this->type,
            'points' => $this->points,
            'reference_type' => $this->reference_type,
            'reference_id' => $this->reference_id,
            'note' => $this->note,
            'member' => $this->whenLoaded('member', function () {
                return [
                    'id' => $this->member->id,
                    'name' => $this->member->name,
                    'phone' => $this->member->phone,
                    'card_number' => $this->member->card_number,
                    'point_balance' => $this->member->point_balance,
                ];
            }),
            'branch' => $this->whenLoaded('branch', function () {
                return [
                    'id' => $this->branch->id,
                    'name' => $this->branch->name,
                ];
            }),
            'reference' => $this->whenLoaded('reference', function () {
                return $this->reference;
            }),
            'formatted_type' => $this->getFormattedType(),
            'is_credit' => in_array($this->type, ['earn', 'revert_redeem']),
            'is_debit' => in_array($this->type, ['redeem', 'revert_earn']),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_at_formatted' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
        ];
    }

    /**
     * Get formatted transaction type for display
     */
    private function getFormattedType(): string
    {
        $typeMap = [
            'earn' => 'Points Earned',
            'redeem' => 'Points Redeemed',
            'revert_earn' => 'Earn Reverted',
            'revert_redeem' => 'Redeem Reverted',
        ];

        return $typeMap[$this->type] ?? ucfirst($this->type);
    }
}

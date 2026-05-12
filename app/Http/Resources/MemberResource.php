<?php

namespace App\Http\Resources;

use App\Libraries\AppLibrary;
use Illuminate\Http\Resources\Json\JsonResource;

class MemberResource extends JsonResource
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
            'id'                => $this->id,
            'name'              => $this->name,
            'phone'             => $this->phone,
            // 'card_number' => $this->card_number,
            // 'phone'             => AppLibrary::maskMiddle($this->phone),
            'card_number'       => AppLibrary::maskMiddle($this->card_number),

            'point_balance'     => $this->point_balance,
            'is_active'         => $this->is_active,
            'user_id'          => $this->user_id,
            'branch_id'        => $this->branch_id,
            'user'             => $this->whenLoaded('user', function () {
                return [
                    'id'        => $this->user->id,
                    'name'              => $this->user->name,
                    'email'             => $this->user->email,
                    'phone'             => $this->user->phone,
                ];
            }),
            'branch' => $this->whenLoaded('branch', function () {
                return [
                    'id' => $this->branch->id,
                    'name' => $this->branch->name,
                ];
            }),
            'point_transactions' => $this->whenLoaded('pointTransactions', function () {
                return $this->pointTransactions->map(function ($transaction) {
                    return [
                        'id' => $transaction->id,
                        'type' => $transaction->type,
                        'points' => $transaction->points,
                        'reference_type' => $transaction->reference_type,
                        'reference_id' => $transaction->reference_id,
                        'note' => $transaction->note,
                        'created_at' => $transaction->created_at,
                    ];
                });
            }),
            'total_transactions' => $this->whenLoaded('pointTransactions', function () {
                return $this->pointTransactions->count();
            }),
            'total_earned_points' => $this->whenLoaded('pointTransactions', function () {
                return $this->pointTransactions->where('type', 'CREDIT')->sum('points');
            }),
            'total_spent_points' => $this->whenLoaded('pointTransactions', function () {
                return $this->pointTransactions->where('type', 'DEBIT')->sum('points');
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

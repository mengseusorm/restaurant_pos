<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TherapistProfileResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'              => $this->id,
            'user_id'         => $this->user_id,
            'branch_id'       => $this->branch_id,
            'user'            => $this->user ? [
                'id'    => $this->user->id,
                'name'  => $this->user->name,
                'email' => $this->user->email,
                'phone' => $this->user->phone,
                'country_code' => $this->user->country_code,
                'image' => $this->user->getFirstMediaUrl('profile'),
            ] : null,
            'code'            => $this->code,
            'verify_code'     => $this->verify_code,
            'commission_rate' => $this->commission_rate,
            'status'          => $this->status,
            'created_at'      => $this->created_at,
            'updated_at'      => $this->updated_at,
        ];
    }
}

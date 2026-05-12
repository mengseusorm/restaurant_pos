<?php

namespace App\Http\Resources;

use App\Libraries\AppLibrary;
use Illuminate\Http\Resources\Json\JsonResource; 

class SessionItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'             => $this->id,
            'sub_session_id' => $this->sub_session_id,
            'guest_name'     => $this->whenLoaded('subSession', fn() => $this->subSession?->guest_name),
            'sub_session_status' => $this->whenLoaded('subSession', fn() => $this->subSession?->status),
            'group_session_id'   => $this->whenLoaded('subSession', fn() => $this->subSession?->group_session_id),
            'item_id'        => $this->item_id,
            'item'           => $this->item ? [
                'id'        => $this->item->id,
                'name'      => $this->item->name,
                'name_kh'   => $this->item->name_kh,
                'name_cn'   => $this->item->name_cn,
                'price'     => $this->item->price,
                'duration'  => $this->item->duration,
                'item_kind' => $this->item->item_kind ?? 1,
                'image'     => $this->item->getFirstMediaUrl('item'),
            ] : null,
            'room_id'        => $this->room_id,
            'room'           => $this->room ? [
                'id'   => $this->room->id,
                'name' => $this->room->name,
            ] : null,
            'bed_id'         => $this->bed_id,
            'bed'            => $this->bed ? [
                'id'   => $this->bed->id,
                'name' => $this->bed->name,
            ] : null,
            'therapist_id'   => $this->therapist_id,
            'therapist'      => $this->therapist ? [
                'id'   => $this->therapist->id,
                'name' => $this->therapist->name,
                'image'=> $this->therapist->getFirstMediaUrl('profile'),
            ] : null,
            'quantity'         => $this->quantity,
            'duration_minutes' => $this->duration_minutes,
            'unit_price'       => $this->unit_price,
            'total_price'      => $this->total_price,
            'notes'            => $this->notes,
            'started_at'       => $this->started_at ? AppLibrary::datetime($this->started_at) : null,
            'ended_at'         => $this->ended_at ? AppLibrary::datetime($this->ended_at) : null,
            'started_at_raw'   => $this->started_at ? $this->started_at->toIso8601String() : null,
            'ended_at_raw'     => $this->ended_at ? $this->ended_at->toIso8601String() : null,
            'created_at'       => $this->created_at,
            'start_time'     => $this->start_time ? AppLibrary::datetime($this->start_time) : null,
            'end_time'       => $this->end_time ? AppLibrary::datetime($this->end_time) : null,
            'started_time'   => $this->started_time ? AppLibrary::datetime($this->started_time) : null,
            'ended_time'     => $this->ended_time   ? AppLibrary::datetime($this->ended_time)   : null,
            'start_time_raw' => $this->start_time ? \Carbon\Carbon::parse($this->start_time)->toIso8601String() : null,
            'end_time_raw'   => $this->end_time ? \Carbon\Carbon::parse($this->end_time)->toIso8601String() : null,
            'started_time_raw' => $this->started_time ? \Carbon\Carbon::parse($this->started_time)->toIso8601String() : null,
            'ended_time_raw'   => $this->ended_time ? \Carbon\Carbon::parse($this->ended_time)->toIso8601String() : null,
            'duration'       => $this->duration,
            'price'          => $this->price,
            'quantity'       => $this->quantity,
            'discount'       => $this->discount,
            'final_price'    => $this->final_price,
            'status'         => $this->status,
            'notes'          => $this->notes,
            'created_at'     => AppLibrary::datetime($this->created_at),
        ];
    }
}

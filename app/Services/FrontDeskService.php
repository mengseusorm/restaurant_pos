<?php

namespace App\Services;

use Exception;
use App\Models\GroupSession;
use App\Models\Room;
use App\Models\SubSession;
use App\Models\SessionItem;
use App\Models\TherapistProfile;
use App\Models\SessionQueue;
use App\Enums\GroupSessionStatus;
use App\Enums\RoomStatus;
use App\Enums\SessionItemStatus;
use App\Enums\SubSessionStatus;
use App\Enums\TherapistStatus;
use App\Libraries\AppLibrary;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class FrontDeskService
{
    public function board(int $branchId = 0): array
    {
        try {
            // ── 1. Rooms with their active session items ──
            $rooms = Room::with([
                'sessionItems' => function ($q) {
                    $q->whereIn('status', [SessionItemStatus::PENDING, SessionItemStatus::IN_PROGRESS])
                      ->whereHas('subSession', fn($sq) => $sq->where('is_checked_out', false))
                      ->with(['subSession.groupSession', 'item', 'therapist', 'bed']);
                },
            ])
            ->when($branchId > 0, fn($q) => $q->where('branch_id', $branchId))
            ->orderBy('name')
            ->get();

            $roomData = $rooms->map(function (Room $room) {
                $activeItems = $room->sessionItems;

                $sessionData = $activeItems->map(fn($si) => [
                    'session_item_id'   => $si->id,
                    'sub_session_id'    => $si->sub_session_id,
                    'group_session_id'  => $si->subSession?->group_session_id,
                    'guest_name'        => $si->subSession?->guest_name,
                    'sub_session_status'=> $si->subSession?->status,
                    'item_id'           => $si->item_id,
                    'item_name'         => $si->item?->name,
                    'item_kind'         => $si->item?->item_kind,
                    'item_duration'     => $si->item?->duration,
                    'therapist_id'      => $si->therapist_id,
                    'therapist_code'     => $si->therapist?->code,
                    'therapist_name'    => $si->therapist?->name,
                    'bed_id'            => $si->bed_id,
                    'bed_name'          => $si->bed?->name,
                    'start_time'        => $this->formattedDateTime($si->start_time),
                    'end_time'          => $this->formattedDateTime($si->end_time),
                    'started_time'      => $this->formattedDateTime($si->started_time),
                    'started_time_raw'  => $this->isoDateTime($si->started_time),
                    'ended_time'        => $this->formattedDateTime($si->ended_time),
                    'ended_time_raw'    => $this->isoDateTime($si->ended_time),
                    'duration'          => ((int) ($si->duration ?? 0) > 0) ? $si->duration : $si->item?->duration,
                    'price'             => $si->price,
                    'discount'          => $si->discount,
                    'final_price'       => $si->final_price,
                    'status'            => $si->status,
                    'room_id'           => $si->room_id,
                    'room_name'         => $room->name,
                ])->values()->toArray();

                return [
                    'id'             => $room->id,
                    'name'           => $room->name,
                    'status'         => $room->status,
                    'active_items'   => $sessionData,
                    'is_occupied'    => $activeItems->isNotEmpty(),
                    'qr_code_token'  => $room->qr_code_token ?? null,
                ];
            })->values()->toArray();

            // ── 2. All active session items flattened ──
            $activeItems = collect($roomData)->flatMap(fn($r) => $r['active_items'])->values()->toArray();

            // ── 3. Therapist availability ──
            $busyTherapistIds = collect($activeItems)->pluck('therapist_id')->filter()->unique()->toArray();

            $therapistData = TherapistProfile::with('user')
                ->get()
                ->map(function (TherapistProfile $t) use ($busyTherapistIds, $activeItems) {
                    $isBusy  = in_array($t->user_id, $busyTherapistIds);
                    $si      = $isBusy
                        ? collect($activeItems)->first(fn($a) => $a['therapist_id'] === $t->user_id)
                        : null;

                    return [
                        'id'          => $t->id,
                        'user_id'     => $t->user_id,
                        'name'        => $t->user?->name ?? ('Therapist #' . $t->id),
                        'status'      => $isBusy ? TherapistStatus::BUSY : ($t->status ?? TherapistStatus::AVAILABLE),
                        'room_name'   => $si['room_name'] ?? null,
                        'item_name'   => $si['item_name'] ?? null,
                        'guest_name'  => $si['guest_name'] ?? null,
                    ];
                })->values()->toArray();

            // ── 4. Summary ──
            $roomsInUse          = collect($roomData)->filter(fn($r) => $r['is_occupied'])->count();
            $availableTherapists = collect($therapistData)->filter(fn($t) => $t['status'] === TherapistStatus::AVAILABLE)->count();

            // ── 5. Open / In-Progress group sessions ──
            $groupSessions = GroupSession::with([
                'subSessions.sessionItems.item',
                'subSessions.sessionItems.room',
                'subSessions.sessionItems.bed',
                'subSessions.sessionItems.therapist',
            ])
            ->whereIn('status', [GroupSessionStatus::OPEN, GroupSessionStatus::IN_PROGRESS])
            ->orderByDesc('id')
            ->get()
            ->map(function (GroupSession $g) {
                return [
                    'id'                => $g->id,
                    'code'              => $g->code,
                    'status'            => $g->status,
                    'notes'             => $g->notes,
                    'arrival_time'      => AppLibrary::datetime($g->arrival_time),
                    'total_guests'      => $g->total_guests,
                    'is_group_checkout' => (bool) $g->is_group_checkout,
                    'sub_sessions'      => $g->subSessions->map(fn($s) => $this->sessionSummary($s))->values()->toArray(),
                    'sub_session_count' => $g->subSessions->count(),
                    'total_amount'      => $g->subSessions->sum(fn($s) => $s->subtotal),
                ];
            })->values()->toArray();

            // ── 6. Waiting queues ──
            $queueQuery = SessionQueue::with(['room', 'service', 'therapist'])
                ->where('status', 'waiting');
            if ($branchId > 0) {
                $queueQuery->where('branch_id', $branchId);
            }
            $waitingQueues = $queueQuery->orderBy('position', 'asc')
                ->get()
                ->map(function (SessionQueue $q) {
                    return [
                        'id'             => $q->id,
                        'position'       => $q->position,
                        'customer_name'  => $q->customer_name,
                        'customer_phone' => $q->customer_phone,
                        'status'         => $q->status,
                        'room_id'        => $q->room_id,
                        'room_name'      => $q->room?->name,
                        'service_id'     => $q->service_id,
                        'service_name'   => $q->service?->name,
                        'therapist_id'   => $q->therapist_id,
                        'therapist_name' => $q->therapist?->name,
                        'notes'          => $q->notes,
                    ];
                })->values()->toArray();

            return [
                'rooms'           => $roomData,
                'therapists'      => $therapistData,
                'active_items'    => $activeItems,
                'group_sessions'  => $groupSessions,
                'waiting_queues'  => $waitingQueues,
                'summary'         => [
                    'rooms_in_use'          => $roomsInUse,
                    'available_therapists'  => $availableTherapists,
                    'total_active_items'    => count($activeItems),
                    'total_group_sessions'  => count($groupSessions),
                    'waiting_queue_count'   => count($waitingQueues),
                ],
            ];
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function roomBoard(int $roomId): array
    {
        try {
            // ── 1. Active items for the specific room ──
            $room = Room::with([
                'sessionItems' => function ($q) {
                    $q->whereIn('status', [SessionItemStatus::PENDING, SessionItemStatus::IN_PROGRESS])
                      ->with(['subSession.groupSession', 'item', 'therapist', 'bed']);
                },
            ])->findOrFail($roomId);

            $activeItems = $room->sessionItems->map(fn($si) => [
                'session_item_id'    => $si->id,
                'sub_session_id'     => $si->sub_session_id,
                'group_session_id'   => $si->subSession?->group_session_id,
                'guest_name'         => $si->subSession?->guest_name,
                'sub_session_status' => $si->subSession?->status,
                'item_id'            => $si->item_id,
                'item_name'          => $si->item?->name,
                'item_kind'          => $si->item?->item_kind,
                'item_duration'      => $si->item?->duration,
                'therapist_id'       => $si->therapist_id,
                'therapist_code'     => $si->therapist?->code,
                'therapist_name'     => $si->therapist?->name,
                'bed_id'             => $si->bed_id,
                'bed_name'           => $si->bed?->name,
                'start_time'         => $this->formattedDateTime($si->start_time),
                'end_time'           => $this->formattedDateTime($si->end_time),
                'started_time'       => $this->formattedDateTime($si->started_time),
                'started_time_raw'   => $this->isoDateTime($si->started_time),
                'ended_time'         => $this->formattedDateTime($si->ended_time),
                'ended_time_raw'     => $this->isoDateTime($si->ended_time),
                'duration'           => ((int) ($si->duration ?? 0) > 0) ? $si->duration : $si->item?->duration,
                'price'              => $si->price,
                'discount'           => $si->discount,
                'final_price'        => $si->final_price,
                'status'             => $si->status,
                'room_id'            => $si->room_id,
                'room_name'          => $room->name,
            ])->values()->toArray();

            // ── 2. Therapists assigned to this room's active items ──
            $busyTherapistIds = collect($activeItems)->pluck('therapist_id')->filter()->unique()->toArray();

            $therapistData = TherapistProfile::with('user')
                ->get()
                ->map(function (TherapistProfile $t) use ($busyTherapistIds, $activeItems) {
                    $isBusy = in_array($t->user_id, $busyTherapistIds);
                    $si     = $isBusy
                        ? collect($activeItems)->first(fn($a) => $a['therapist_id'] === $t->user_id)
                        : null;

                    return [
                        'id'         => $t->id,
                        'user_id'    => $t->user_id,
                        'name'       => $t->user?->name ?? ('Therapist #' . $t->id),
                        'status'     => $isBusy ? TherapistStatus::BUSY : ($t->status ?? TherapistStatus::AVAILABLE),
                        'room_name'  => $si['room_name'] ?? null,
                        'item_name'  => $si['item_name'] ?? null,
                        'guest_name' => $si['guest_name'] ?? null,
                    ];
                })->values()->toArray();

            // ── 3. Waiting queues for this room ──
            $waitingQueues = SessionQueue::with(['room', 'service', 'therapist'])
                ->where('status', 'waiting')
                ->where('room_id', $roomId)
                ->orderBy('position', 'asc')
                ->get()
                ->map(fn(SessionQueue $q) => [
                    'id'             => $q->id,
                    'position'       => $q->position,
                    'customer_name'  => $q->customer_name,
                    'customer_phone' => $q->customer_phone,
                    'status'         => $q->status,
                    'room_id'        => $q->room_id,
                    'room_name'      => $q->room?->name,
                    'service_id'     => $q->service_id,
                    'service_name'   => $q->service?->name,
                    'therapist_id'   => $q->therapist_id,
                    'therapist_name' => $q->therapist?->name,
                    'notes'          => $q->notes,
                ])->values()->toArray();

            return [
                'room'           => ['id' => $room->id, 'name' => $room->name, 'status' => $room->status],
                'active_items'   => $activeItems,
                'therapists'     => $therapistData,
                'waiting_queues' => $waitingQueues,
            ];
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    private function sessionSummary(SubSession $session): array
    {
        return [
            'id'             => $session->id,
            'status'         => $session->status,
            'guest_name'     => $session->guest_name,
            'phone'          => $session->phone,
            'notes'          => $session->notes,
            'is_checked_out' => (bool) $session->is_checked_out,
            'start_time'     => $this->formattedDateTime($session->start_time),
            'end_time'       => $this->formattedDateTime($session->end_time),
            'session_items'  => $session->sessionItems->map(fn($si) => [
                'id'             => $si->id,
                'item_id'        => $si->item_id,
                'item_name'      => $si->item?->name,
                'room_id'        => $si->room_id,
                'room_name'      => $si->room?->name,
                'bed_id'         => $si->bed_id,
                'bed_name'       => $si->bed?->name,
                'therapist_id'   => $si->therapist_id,
                'therapist_name' => $si->therapist?->name,
                'price'          => $si->price,
                'discount'       => $si->discount,
                'final_price'    => $si->final_price,
                'duration'       => $si->duration,
                'started_time'     => $this->formattedDateTime($si->started_time),
                'started_time_raw' => $this->isoDateTime($si->started_time),
                'ended_time'       => $this->formattedDateTime($si->ended_time),
                'ended_time_raw'   => $this->isoDateTime($si->ended_time),
                'status'           => $si->status,
            ])->values()->toArray(),
            'subtotal' => $session->subtotal,
        ];
    }

    private function formattedDateTime($dateTime): ?string
    {
        return $dateTime ? AppLibrary::datetime($dateTime) : null;
    }

    private function isoDateTime($dateTime): ?string
    {
        return $dateTime ? Carbon::parse($dateTime)->toIso8601String() : null;
    }
}

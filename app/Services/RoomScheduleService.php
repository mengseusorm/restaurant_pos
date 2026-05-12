<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\SubSession;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RoomScheduleService
{
    public function schedule(string $date, int $branchId = 1): array
    {
        if ($branchId === 0) {
            $branchId = Auth::user()->branch_id ?? 0;
        }

        $branch = Branch::find($branchId);
        $openTime  = $branch?->open_time  ?? '08:00';
        $closeTime = $branch?->close_time ?? '23:00';

        $targetDate = Carbon::parse($date)->startOfDay();

        $rooms = Room::where('branch_id', $branchId)
            ->orderBy('name')
            ->get(['id', 'name', 'status']);

        // Fallback: if no rooms found for this branch, fetch rooms referenced by sessions
        if ($rooms->isEmpty()) {
            $roomIds = SubSession::where('branch_id', $branchId)
                ->whereDate('created_at', $targetDate)
                ->pluck('room_id')
                ->merge(
                    SubSession::where('branch_id', $branchId)
                        ->whereDate('started_at', $targetDate)
                        ->pluck('room_id')
                )
                ->unique()
                ->filter()
                ->values();

            if ($roomIds->isNotEmpty()) {
                $rooms = Room::whereIn('id', $roomIds)->orderBy('name')->get(['id', 'name', 'status']);
            }
        }

        $sessions = SubSession::with(['therapist:id,name', 'service:id,name,price'])
            ->where('branch_id', $branchId)
            ->where(function ($q) use ($targetDate) {
                // Sessions that started on this date
                $q->whereDate('started_at', $targetDate)
                  // Sessions that end on this date
                  ->orWhereDate('ended_at', $targetDate)
                  // Pending sessions (no started_at yet) created on this date
                  ->orWhere(function ($q2) use ($targetDate) {
                      $q2->whereNull('started_at')
                         ->whereDate('created_at', $targetDate);
                  });
            })
            ->whereNotIn('status', ['cancelled'])
            ->get(['id', 'room_id', 'order_id', 'therapist_id', 'service_id',
                   'customer_name', 'started_at', 'ended_at', 'duration_minutes',
                   'extra_minutes', 'status', 'created_at']);

        $sessionsFormatted = $sessions->map(function ($s) {
            // For pending sessions without started_at, use created_at as a display fallback
            $startedAt = $s->started_at
                ? Carbon::parse($s->started_at)->format('Y-m-d H:i')
                : Carbon::parse($s->created_at)->format('Y-m-d H:i');

            $endedAt = null;
            if ($s->ended_at) {
                $endedAt = Carbon::parse($s->ended_at)->format('Y-m-d H:i');
            } elseif ($s->started_at && $s->duration_minutes) {
                $endedAt = Carbon::parse($s->started_at)
                    ->addMinutes($s->duration_minutes + ($s->extra_minutes ?? 0))
                    ->format('Y-m-d H:i');
            } elseif ($s->duration_minutes) {
                // Pending session: estimate end from created_at + duration
                $endedAt = Carbon::parse($s->created_at)
                    ->addMinutes($s->duration_minutes + ($s->extra_minutes ?? 0))
                    ->format('Y-m-d H:i');
            }

            return [
                'id'              => $s->id,
                'room_id'         => $s->room_id,
                'order_id'        => $s->order_id,
                'customer_name'   => $s->customer_name,
                'therapist_name'  => $s->therapist?->name,
                'service_name'    => $s->service?->name,
                'started_at'      => $startedAt,
                'ended_at'        => $endedAt,
                'duration_minutes'=> $s->duration_minutes,
                'extra_minutes'   => $s->extra_minutes ?? 0,
                'status'          => $s->status,
                'is_pending'      => is_null($s->started_at),
            ];
        })->values();


        Log::info('Room Schedule', [
            'date' => $targetDate->toDateString(),
            'branch_id' => $branchId,
            'sessions_count' => $sessionsFormatted,
        ]);
        return [
            'date'       => $targetDate->format('Y-m-d'),
            'open_time'  => $openTime,
            'close_time' => $closeTime,
            'rooms'      => $rooms->values(),
            'sessions'   => $sessionsFormatted,
        ];
    }
}

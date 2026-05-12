<?php

namespace App\Services;

use Exception;
use App\Models\GroupSession;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\SubSession;
use App\Models\Room;
use App\Models\SessionItem;
use App\Models\TherapistProfile;
use App\Enums\GroupSessionStatus;
use App\Enums\ItemKind;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\PaymentStatus;
use App\Enums\RoomStatus;
use App\Enums\Source;
use App\Enums\TherapistStatus;
use App\Enums\SubSessionStatus;
use App\Enums\SessionItemStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\SubSessionRequest;
use App\Http\Requests\SessionAddItemRequest;
use App\Http\Requests\SessionChangeTherapistRequest;
use App\Http\Requests\SessionChangeStartTimeRequest;
use App\Http\Requests\SessionExtendRequest;
use App\Http\Requests\SessionAddItemsRequest;
use App\Libraries\QueryExceptionLibrary;
use Smartisan\Settings\Facades\Settings;

class SubSessionService
{
    protected array $sessionFilter = [
        'group_session_id',
        'status',
    ];

    public function list(PaginateRequest $request)
    {
        try {
            $requests    = $request->all();
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType   = $request->get('order_type') ?? 'desc';

            return SubSession::with(['groupSession.orders', 'sessionItems.item', 'sessionItems.room', 'sessionItems.bed', 'sessionItems.therapist', 'order'])
                ->where(function ($query) use ($requests) {
                    foreach ($requests as $key => $request) {
                        if (in_array($key, $this->sessionFilter)) {
                            $query->where($key, $request);
                        }
                    }
                    if (!empty($requests['guest_name'])) {
                        $query->where('guest_name', 'like', '%' . $requests['guest_name'] . '%');
                    }
                    if (!empty($requests['from_date'])) {
                        $fromDate = $this->parseFilterDateTime($requests['from_date'])->toDateTimeString();
                        $query->where(function ($subQuery) use ($fromDate) {
                            $subQuery->where('start_time', '>=', $fromDate)
                                ->orWhere(function ($fallbackQuery) use ($fromDate) {
                                    $fallbackQuery->whereNull('start_time')
                                        ->where('created_at', '>=', $fromDate);
                                });
                        });
                    }
                    if (!empty($requests['to_date'])) {
                        $toDate = $this->parseFilterDateTime($requests['to_date'])->toDateTimeString();
                        $query->where(function ($subQuery) use ($toDate) {
                            $subQuery->where('start_time', '<=', $toDate)
                                ->orWhere(function ($fallbackQuery) use ($toDate) {
                                    $fallbackQuery->whereNull('start_time')
                                        ->where('created_at', '<=', $toDate);
                                });
                        });
                    }
                })
                ->orderBy($orderColumn, $orderType)
                ->$method($methodValue);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    private function parseFilterDateTime(string $dateTime): Carbon
    {
        $timezone = config('app.timezone');
        $formats = [
            env('DATE_FORMAT', 'd-m-Y') . ', ' . env('TIME_FORMAT', 'h:i A'),
            env('DATE_FORMAT', 'd-m-Y') . ' ' . env('TIME_FORMAT', 'h:i A'),
            'Y-m-d h:i:s A',
            'Y-m-d H:i:s',
        ];

        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $dateTime, $timezone);
            } catch (Exception) {
                continue;
            }
        }

        return Carbon::parse($dateTime, $timezone);
    }

    public function store(SubSessionRequest $request): SubSession
    {
        try {
            return DB::transaction(function () use ($request) {
                $data = $request->validated();

                // Auto-create a GroupSession if no group_session_id provided
                if (empty($data['group_session_id'])) {
                    $groupSession = GroupSession::create([
                        'status'       => GroupSessionStatus::OPEN,
                        'arrival_time' => now(),
                    ]);
                    $data['group_session_id'] = $groupSession->id;
                }

                $data['status'] = SubSessionStatus::WAITING;
                $session = SubSession::create($data);

                // Sync guest count on parent group session
                if ($session->group_session_id) {
                    GroupSession::find($session->group_session_id)?->syncGuestCount();
                }

                return $session->load(['groupSession', 'sessionItems.item', 'sessionItems.room', 'sessionItems.bed', 'sessionItems.therapist']);
            });
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function update(SubSessionRequest $request, SubSession $subSession): SubSession
    {
        try {
            $subSession->update($request->validated());
            return $subSession->load(['groupSession', 'sessionItems.item', 'sessionItems.room', 'sessionItems.bed', 'sessionItems.therapist']);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function destroy(SubSession $subSession): void
    {
        try {
            $groupSessionId = $subSession->group_session_id;
            $subSession->delete();
            if ($groupSessionId) {
                GroupSession::find($groupSessionId)?->syncGuestCount();
            }
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }

    public function show(SubSession $subSession): SubSession
    {
        try {
            return $subSession->load(['groupSession.orders', 'sessionItems.item', 'sessionItems.room', 'sessionItems.bed', 'sessionItems.therapist', 'order']);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function start(SubSession $subSession): SubSession
    {
        try {
            return DB::transaction(function () use ($subSession) {
                $subSession->update([
                    'status'     => SubSessionStatus::IN_SERVICE,
                    'start_time' => $subSession->start_time ?? now(),
                ]);

                $subSession->loadMissing('sessionItems');
                foreach ($subSession->sessionItems as $si) {
                    $si->update([
                        'status'       => SessionItemStatus::IN_PROGRESS,
                        'started_time' => now(),
                    ]);
                    if ($si->room_id) {
                        Room::where('id', $si->room_id)->update(['status' => RoomStatus::OCCUPIED]);
                    }
                    if ($si->therapist_id) {
                        TherapistProfile::where('user_id', $si->therapist_id)->update(['status' => TherapistStatus::BUSY]);
                    }
                }

                return $subSession->load(['groupSession', 'sessionItems.item', 'sessionItems.room', 'sessionItems.bed', 'sessionItems.therapist']);
            });
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function startItem(SubSession $subSession, SessionItem $sessionItem): SessionItem
    {
        try {
            return DB::transaction(function () use ($subSession, $sessionItem) {
                // Move session to in_service if still waiting
                if ($subSession->status === SubSessionStatus::WAITING) {
                    $subSession->update([
                        'status'     => SubSessionStatus::IN_SERVICE,
                        'start_time' => $subSession->start_time ?? now(),
                    ]);
                }

                $sessionItem->update([
                    'status'       => SessionItemStatus::IN_PROGRESS,
                    'started_time' => now(),
                ]);

                if ($sessionItem->room_id) {
                    Room::where('id', $sessionItem->room_id)->update(['status' => RoomStatus::OCCUPIED]);
                }
                if ($sessionItem->therapist_id) {
                    TherapistProfile::where('user_id', $sessionItem->therapist_id)->update(['status' => TherapistStatus::BUSY]);
                }

                return $sessionItem->load(['item', 'room', 'bed', 'therapist']);
            });
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function completeItem(SubSession $subSession, SessionItem $sessionItem): SessionItem
    {
        try {
            return DB::transaction(function () use ($subSession, $sessionItem) {
                $sessionItem->update([
                    'status'     => SessionItemStatus::COMPLETED,
                    'ended_time' => now(),
                ]);

                if ($sessionItem->room_id) {
                    Room::where('id', $sessionItem->room_id)->update(['status' => RoomStatus::CLEANING]);
                }
                if ($sessionItem->therapist_id) {
                    TherapistProfile::where('user_id', $sessionItem->therapist_id)->update(['status' => TherapistStatus::AVAILABLE]);
                }

                return $sessionItem->load(['item', 'room', 'bed', 'therapist']);
            });
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function complete(SubSession $subSession): SubSession
    {
        try {
            return DB::transaction(function () use ($subSession) {
                $subSession->update([
                    'status'   => SubSessionStatus::DONE,
                    'end_time' => $subSession->end_time ?? now(),
                ]);

                $subSession->loadMissing('sessionItems');
                foreach ($subSession->sessionItems as $si) {
                    $si->update([
                        'status'     => SessionItemStatus::COMPLETED,
                        'ended_time' => now(),
                    ]);
                    if ($si->room_id) {
                        Room::where('id', $si->room_id)->update(['status' => RoomStatus::CLEANING]);
                    }
                    if ($si->therapist_id) {
                        TherapistProfile::where('user_id', $si->therapist_id)->update(['status' => TherapistStatus::AVAILABLE]);
                    }
                }

                return $subSession->load(['groupSession', 'sessionItems.item', 'sessionItems.room', 'sessionItems.bed', 'sessionItems.therapist']);
            });
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function addItem(SessionAddItemRequest $request, SubSession $subSession): SessionItem
    {
        try {
            return DB::transaction(function () use ($request, $subSession) {
                $item  = Item::find($request->item_id);

                $price = $request->price;
                if ($price === null || $price === '') {
                    $price = $item?->price ?? 0;
                }

                $duration = $request->duration;
                if ($duration === null || $duration === '') {
                    $duration = $item?->duration ?? 0;
                }

                $quantity = (int) ($request->quantity ?? 1);
                if ($quantity < 1) $quantity = 1;

                $startTime = $request->start_time ? \Carbon\Carbon::parse($request->start_time) : now();
                $endTime   = $request->end_time
                    ? \Carbon\Carbon::parse($request->end_time)
                    : $startTime->copy()->addMinutes((int) $duration);

                $discount   = $request->discount ?? 0;
                $finalPrice = $price * $quantity - $discount;

                $sessionItem = SessionItem::create([
                    'sub_session_id' => $subSession->id,
                    'item_id'        => $request->item_id,
                    'quantity'       => $quantity,
                    'room_id'        => $request->room_id ?? null,
                    'bed_id'         => $request->bed_id ?? null,
                    'therapist_id'   => $request->therapist_id ?? null,
                    'start_time'     => $startTime,
                    'end_time'       => $endTime,
                    'duration'       => $duration,
                    'price'          => $price,
                    'discount'       => $discount,
                    'final_price'    => $finalPrice,
                    'status'         => SessionItemStatus::PENDING,
                    'notes'          => $request->notes ?? null,
                ]);

                if ($sessionItem->room_id) {
                    Room::where('id', $sessionItem->room_id)->update(['status' => RoomStatus::OCCUPIED]);
                }
                if ($sessionItem->therapist_id) {
                    TherapistProfile::where('user_id', $sessionItem->therapist_id)->update(['status' => TherapistStatus::BUSY]);
                }

                return $sessionItem->load(['item', 'room', 'bed', 'therapist']);
            });
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function changeStartTime(SessionChangeStartTimeRequest $request, SubSession $subSession): SubSession
    {
        try {
            $subSession->update([
                'started_at' => $request->started_at,
                'ended_at'   => $request->ended_at ?? $subSession->ended_at,
            ]);
            return $subSession->fresh(['groupSession', 'order', 'room', 'therapist', 'service', 'serviceItems.item']);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function addItems(SessionAddItemsRequest $request, SubSession $subSession): array
    {
        try {
            $item = Item::findOrFail($request->item_id);

            $unitPrice  = $request->unit_price ?? $item->price;
            $totalPrice = $unitPrice * $request->quantity;

            $itemType = ($item->item_kind == ItemKind::SERVICE) ? 'service' : 'product';

            $serviceItem = SessionItem::create([
                'branch_id'        => $request->branch_id
                                   ?: ($subSession->branch_id > 0 ? $subSession->branch_id : null)
                                   ?: (Auth::user()->branch_id > 0 ? Auth::user()->branch_id : null),
                'session_id'       => $subSession->id,
                'type'             => $itemType,
                'name'             => $item->name,
                'item_id'          => $item->id,
                'therapist_id'     => $request->therapist_id ?? null,
                'quantity'         => $request->quantity,
                'duration_minutes' => $request->duration_minutes ?? null,
                'unit_price'       => $unitPrice,
                'total_price'      => $totalPrice,
                'started_at'       => $request->started_at ?? null,
                'ended_at'         => $request->ended_at   ?? null,
                'notes'            => $request->notes,
            ]);

            // Update sub-session subtotal and cascade to group
            $subSession->recalcSubtotal();

            return $serviceItem->load(['item', 'therapist']);
            return DB::transaction(function () use ($request, $subSession) {
                $sessionItems = [];

                foreach ($request->items as $data) {
                    $item = Item::find($data['item_id']);

                    $price = $data['price'] ?? null;
                    if ($price === null || $price === '') {
                        $price = $item?->price ?? 0;
                    }

                    $duration = $data['duration'] ?? null;
                    if ($duration === null || $duration === '') {
                        $duration = $item?->duration ?? 0;
                    }

                    $quantity = (int) ($data['quantity'] ?? 1);
                    if ($quantity < 1) $quantity = 1;

                    $startTime = isset($data['start_time']) ? \Carbon\Carbon::parse($data['start_time']) : now();
                    $endTime   = isset($data['end_time'])
                        ? \Carbon\Carbon::parse($data['end_time'])
                        : $startTime->copy()->addMinutes((int) $duration);

                    $discount   = $data['discount'] ?? 0;
                    $finalPrice = $price * $quantity - $discount;

                    $sessionItem = SessionItem::create([
                        'sub_session_id' => $subSession->id,
                        'item_id'        => $data['item_id'],
                        'quantity'       => $quantity,
                        'room_id'        => $data['room_id'] ?? null,
                        'bed_id'         => $data['bed_id'] ?? null,
                        'therapist_id'   => $data['therapist_id'] ?? null,
                        'start_time'     => $startTime,
                        'end_time'       => $endTime,
                        'duration'       => $duration,
                        'price'          => $price,
                        'discount'       => $discount,
                        'final_price'    => $finalPrice,
                        'status'         => SessionItemStatus::PENDING,
                        'notes'          => $data['notes'] ?? null,
                    ]);

                    if ($sessionItem->room_id) {
                        Room::where('id', $sessionItem->room_id)->update(['status' => RoomStatus::OCCUPIED]);
                    }
                    if ($sessionItem->therapist_id) {
                        TherapistProfile::where('user_id', $sessionItem->therapist_id)->update(['status' => TherapistStatus::BUSY]);
                    }

                    $sessionItems[] = $sessionItem->load(['item', 'room', 'bed', 'therapist']);
                }

                return $sessionItems;
            });
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function updateItem(SessionAddItemRequest $request, SubSession $subSession, SessionItem $sessionItem): SubSession
    {
        try {
            if ($sessionItem->sub_session_id !== $subSession->id) {
                throw new Exception('Session item does not belong to this session.', 422);
            }

            $price    = $request->unit_price ?? $sessionItem->price;
            $quantity = $request->quantity   ?? $sessionItem->quantity;
            $duration = $request->duration_minutes ?? $sessionItem->duration;
            $discount = $sessionItem->discount ?? 0;
            $finalPrice = ($price * $quantity) - $discount;

            if ($request->item_id && $request->item_id != $sessionItem->item_id) {
                $newItem = Item::findOrFail($request->item_id);
                $sessionItem->item_id = $newItem->id;
                $price = $request->unit_price ?? $newItem->price;
                $duration = $request->duration_minutes ?? $newItem->duration ?? $duration;
                $finalPrice = ($price * $quantity) - $discount;
            }

            $startTime = $request->start_time ?? $request->started_at ?? $sessionItem->start_time;
            $endTime   = $request->end_time   ?? $request->ended_at   ?? $sessionItem->end_time;
            $startedAt = $request->started_at ?? $sessionItem->started_at;
            $endedAt   = $request->ended_at   ?? $sessionItem->ended_at;

            if (($request->filled('started_at') || $request->filled('start_time') || $request->filled('duration_minutes')) && $startTime && $duration) {
                $calculatedEndTime = \Carbon\Carbon::parse($startTime)->copy()->addMinutes((int) $duration);
                $endTime = $request->end_time ?? $request->ended_at ?? $calculatedEndTime;
                if ($request->filled('started_at') && !$request->filled('ended_at')) {
                    $endedAt = $calculatedEndTime;
                }
            }

            $sessionItem->update([
                'item_id'      => $sessionItem->item_id,
                'therapist_id' => $request->has('therapist_id') ? $request->therapist_id : $sessionItem->therapist_id,
                'quantity'     => $quantity,
                'start_time'   => $startTime,
                'end_time'     => $endTime,
                'duration'     => $duration,
                'price'        => $price,
                'final_price'  => $finalPrice,
                'started_at'   => $startedAt,
                'ended_at'     => $endedAt,
                'notes'        => $request->notes ?? $sessionItem->notes,
            ]);

            return $this->show($subSession);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function removeItem(SubSession $subSession, SessionItem $sessionItem): SubSession
    {
        try {
            if ($sessionItem->sub_session_id !== $subSession->id) {
                throw new Exception('Session item does not belong to this sub-session.', 422);
            }
            if ($sessionItem->room_id) {
                Room::where('id', $sessionItem->room_id)->update(['status' => RoomStatus::AVAILABLE]);
            }
            if ($sessionItem->therapist_id) {
                TherapistProfile::where('user_id', $sessionItem->therapist_id)->update(['status' => TherapistStatus::AVAILABLE]);
            }
            $sessionItem->delete();
            return $this->show($subSession);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function checkout(SubSession $subSession): array
    {
        try {
            return DB::transaction(function () use ($subSession) {
                $subSession->load(['sessionItems.item', 'groupSession']);

                if ($subSession->is_checked_out) {
                    throw new Exception('This session has already been checked out.', 422);
                }

                if ($subSession->sessionItems->isEmpty()) {
                    throw new Exception('Cannot checkout a session with no session items.', 422);
                }

                $currency   = Settings::group('site')->get('site_default_currency') ?? 'USD';
                $currencyId = Settings::group('site')->get('site_default_currency_id') ?? 1;
                $branchId   = Auth::user()->branch_id
                    ?: $subSession->groupSession?->branch_id
                    ?: $subSession->sessionItems->first()?->item?->branch_id
                    ?: 1;
                $subtotal   = $subSession->sessionItems->sum('final_price');

                $today         = date('Y-m-d');
                $waitingNumber = (Order::whereDate('created_at', $today)->max('waiting_number') ?? 0) + 1;

                $order = Order::create([
                    'user_id'               => Auth::id(),
                    'order_user_id'         => Auth::id(),
                    'branch_id'             => $branchId,
                    'status'                => OrderStatus::PENDING,
                    'payment_status'        => PaymentStatus::UNPAID,
                    'order_type'            => OrderType::POS,
                    'source'                => Source::POS,
                    'is_advance_order'      => 0,
                    'subtotal'              => $subtotal,
                    'total_tax'             => 0,
                    'total'                 => $subtotal,
                    'paid_amount'           => 0,
                    'balance_due'           => $subtotal,
                    'currency'              => $currency,
                    'currency_id'           => $currencyId,
                    'customer_name'         => $subSession->guest_name,
                    'customer_phone_number' => $subSession->phone,
                    'order_datetime'        => now(),
                    'business_date'         => now(),
                    'waiting_number'        => $waitingNumber,
                    'check_in_time'         => $subSession->start_time ?? now(),
                    'group_session_id'      => $subSession->group_session_id,
                ]);
                $order->update(['order_serial_no' => date('dmy') . $order->id]);

                foreach ($subSession->sessionItems as $si) {
                    OrderItem::create([
                        'order_id'               => $order->id,
                        'branch_id'              => $branchId,
                        'item_id'                => $si->item_id,
                        'order_item_custom_name' => null,
                        'quantity'               => 1,
                        'price'                  => $si->price,
                        'total_price'            => $si->final_price,
                        'discount'               => $si->discount,
                        'discount_percentage'    => 0,
                        'tax_name'               => null,
                        'tax_rate'               => 0,
                        'tax_type'               => 1,
                        'tax_amount'             => 0,
                        'item_variations'        => '[]',
                        'item_extras'            => '[]',
                        'item_variation_total'   => 0,
                        'item_extra_total'       => 0,
                    ]);
                }

                $subSession->update([
                    'is_checked_out' => true,
                    'status'         => SubSessionStatus::DONE,
                    'end_time'       => $subSession->end_time ?? now(),
                    'order_id'       => $order->id,
                ]);

                foreach ($subSession->sessionItems as $si) {
                    $si->update(['status' => SessionItemStatus::COMPLETED]);
                    if ($si->room_id) {
                        Room::where('id', $si->room_id)->update(['status' => RoomStatus::CLEANING]);
                    }
                    if ($si->therapist_id) {
                        TherapistProfile::where('user_id', $si->therapist_id)->update(['status' => TherapistStatus::AVAILABLE]);
                    }
                }

                if ($subSession->group_session_id) {
                    $group = GroupSession::find($subSession->group_session_id);
                    if ($group) {
                        $allDone = $group->subSessions()->where('is_checked_out', false)->doesntExist();
                        if ($allDone) {
                            $group->syncStatusFromOrders();
                        }
                    }
                }

                return [
                    'session' => $subSession->fresh(['groupSession', 'sessionItems.item', 'sessionItems.room', 'sessionItems.bed', 'sessionItems.therapist']),
                    'order'   => $order->load('orderItems'),
                ];
            });
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function listByRoom(Room $room): array
    {
        try {
            $activeItems = SessionItem::with([
                'subSession.groupSession',
                'item',
                'therapist',
            ])
                ->where('room_id', $room->id)
                ->whereNotIn('status', [SessionItemStatus::COMPLETED])
                ->get();

            return [
                'room'          => $room,
                'session_items' => $activeItems,
            ];
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}

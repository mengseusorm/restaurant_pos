<?php

namespace App\Services;

use Exception;
use App\Models\GroupSession;
use App\Models\SubSession;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Room;
use App\Models\TherapistProfile;
use App\Enums\GroupSessionStatus;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\PaymentStatus;
use App\Enums\RoomStatus;
use App\Enums\Source;
use App\Enums\SubSessionStatus;
use App\Enums\TherapistStatus;
use App\Http\Requests\GroupSessionRequest;
use App\Http\Requests\GroupSessionCheckoutRequest;
use App\Http\Requests\PaginateRequest;
use App\Libraries\AppLibrary;
use App\Libraries\QueryExceptionLibrary;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Smartisan\Settings\Facades\Settings;

class GroupSessionService
{
    protected array $groupSessionFilter = ['status'];

    private function withRelations(GroupSession $gs): GroupSession
    {
        $gs->syncStatusFromOrders();

        return $gs->load([
            'subSessions.sessionItems.item',
            'subSessions.sessionItems.room',
            'subSessions.sessionItems.bed',
            'subSessions.sessionItems.therapist',
            'subSessions.order',
            'orders',
        ]);
    }

    /**
     * @throws Exception
     */
    public function list(PaginateRequest $request)
    {
        try {

        Log::info('GroupSession List Request: ' . json_encode($request->all()));
            $requests    = $request->all();
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType   = $request->get('order_type') ?? 'desc';

            return GroupSession::with([
                'subSessions.sessionItems.item',
                'subSessions.sessionItems.room',
            'subSessions.sessionItems.bed',
                'subSessions.sessionItems.therapist',
            ])
                ->where(function ($query) use ($requests) {
                    foreach ($requests as $key => $value) {
                        if (in_array($key, $this->groupSessionFilter)) {
                            $query->where($key, $value);
                        }
                    }
                    if (!empty($requests['from_date'])) {
                        $fromDate = AppLibrary::filterDateTime($requests['from_date'])->toDateTimeString();
                        $query->where(function ($subQuery) use ($fromDate) {
                            $subQuery->where('arrival_time', '>=', $fromDate)
                                ->orWhere(function ($fallbackQuery) use ($fromDate) {
                                    $fallbackQuery->whereNull('arrival_time')
                                        ->where('created_at', '>=', $fromDate);
                                });
                        });
                    }
                    if (!empty($requests['to_date'])) {
                        $toDate = AppLibrary::filterDateTime($requests['to_date'])->toDateTimeString();
                        $query->where(function ($subQuery) use ($toDate) {
                            $subQuery->where('arrival_time', '<=', $toDate)
                                ->orWhere(function ($fallbackQuery) use ($toDate) {
                                    $fallbackQuery->whereNull('arrival_time')
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

    public function store(GroupSessionRequest $request): GroupSession
    {
        try {
            return DB::transaction(function () use ($request) {
                $data = $request->validated();
                $data['status']       = GroupSessionStatus::OPEN;
                $data['arrival_time'] = now();
                $data['branch_id']    = Auth::user()->branch_id ?? 0;
                $groupSession = GroupSession::create($data);
                return $this->withRelations($groupSession);
            });
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function show(GroupSession $groupSession): GroupSession
    {
        try {
            return $this->withRelations($groupSession);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function update(GroupSessionRequest $request, GroupSession $groupSession): GroupSession
    {
        try {
            $groupSession->update($request->validated());
            return $this->withRelations($groupSession);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function destroy(GroupSession $groupSession): void
    {
        try {
            $groupSession->delete();
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }

    public function addSubSession(GroupSession $groupSession, array $data): GroupSession
    {
        try {
            return DB::transaction(function () use ($groupSession, $data) {
                SubSession::create(array_merge($data, [
                    'group_session_id' => $groupSession->id,
                    'status'           => SubSessionStatus::WAITING,
                ]));
                $groupSession->syncGuestCount();
                return $this->withRelations($groupSession->fresh());
            });
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function removeSubSession(GroupSession $groupSession, SubSession $subSession): GroupSession
    {
        try {
            if ($subSession->group_session_id !== $groupSession->id) {
                throw new Exception('Sub-session does not belong to this group session.', 422);
            }
            $subSession->delete();
            $groupSession->syncGuestCount();
            return $this->withRelations($groupSession->fresh());
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function checkout(GroupSession $groupSession, GroupSessionCheckoutRequest $request): array
    {
        try {
            return DB::transaction(function () use ($groupSession, $request) {
                if ($groupSession->status === GroupSessionStatus::COMPLETED) {
                    throw new Exception('This group session has already been completed.', 422);
                }

                $groupSession->load(['subSessions.sessionItems.item']);
                $activeSubs = $groupSession->subSessions->filter(fn($s) => !$s->is_checked_out);

                if ($activeSubs->isEmpty()) {
                    throw new Exception('All sub-sessions are already checked out.', 422);
                }

                $allItems = $activeSubs->flatMap(fn($s) => $s->sessionItems);
                if ($allItems->isEmpty()) {
                    throw new Exception('No session items found to check out.', 422);
                }

                $currency      = Settings::group('site')->get('site_default_currency') ?? 'USD';
                $currencyId    = Settings::group('site')->get('site_default_currency_id') ?? 1;
                $branchId      = $groupSession->branch_id ?: $allItems->first()->item?->branch_id;
                $subtotal      = $allItems->sum('final_price');
                $today         = date('Y-m-d');
                $waitingNumber = (Order::whereDate('created_at', $today)->max('waiting_number') ?? 0) + 1;
                $customerNames = $activeSubs->pluck('guest_name')->filter()->unique()->implode(', ');

                $order = Order::create([
                    'user_id'            => Auth::id(),
                    'order_user_id'      => Auth::id(),
                    'branch_id'          => $branchId,
                    'status'             => OrderStatus::PENDING,
                    'payment_status'     => PaymentStatus::UNPAID,
                    'order_type'         => OrderType::POS,
                    'source'             => Source::POS,
                    'is_advance_order'   => 0,
                    'subtotal'           => $subtotal,
                    'total_tax'          => 0,
                    'total'              => $subtotal,
                    'paid_amount'        => 0,
                    'balance_due'        => $subtotal,
                    'currency'           => $currency,
                    'currency_id'        => $currencyId,
                    'customer_name'      => $customerNames ?: null,
                    'order_datetime'     => now(),
                    'business_date'      => now(),
                    'waiting_number'     => $waitingNumber,
                    'check_in_time'      => $groupSession->arrival_time ?? now(),
                    'order_note'         => $request->note ?? null,
                    'pos_payment_method' => $request->payment_method ?? null,
                    'payment_method_id'  => $request->payment_method_id ?? null,
                    'group_session_id'   => $groupSession->id,
                ]);
                $order->update(['order_serial_no' => date('dmy') . $order->id]);

                foreach ($activeSubs as $subSession) {
                    foreach ($subSession->sessionItems as $si) {
                        OrderItem::create([
                            'order_id'               => $order->id,
                            'branch_id'              => $branchId,
                            'item_id'                => $si->item_id,
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
                    $this->freeResources($subSession);
                }

                $groupSession->syncStatusFromOrders();

                return [
                    'group_session' => $this->withRelations($groupSession->fresh()),
                    'order'         => $order->load('orderItems'),
                ];
            });
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function checkoutSplit(GroupSession $groupSession): array
    {
        try {
            return DB::transaction(function () use ($groupSession) {
                if ($groupSession->status === GroupSessionStatus::COMPLETED) {
                    throw new Exception('This group session has already been completed.', 422);
                }

                $groupSession->load(['subSessions.sessionItems.item']);
                $activeSubs = $groupSession->subSessions->filter(fn($s) => !$s->is_checked_out);

                if ($activeSubs->isEmpty()) {
                    throw new Exception('All sub-sessions are already checked out.', 422);
                }

                $currency   = Settings::group('site')->get('site_default_currency') ?? 'USD';
                $currencyId = Settings::group('site')->get('site_default_currency_id') ?? 1;
                $branchId   = $groupSession->branch_id
                              ?: $activeSubs->flatMap(fn($s) => $s->sessionItems)->first()?->item?->branch_id;
                $orders     = [];

                foreach ($activeSubs as $subSession) {
                    if ($subSession->sessionItems->isEmpty()) continue;

                    $subtotal      = $subSession->sessionItems->sum('final_price');
                    $today         = date('Y-m-d');
                    $waitingNumber = (Order::whereDate('created_at', $today)->max('waiting_number') ?? 0) + 1;

                    $order = Order::create([
                        'user_id'               => Auth::id(),
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
                        'group_session_id'      => $groupSession->id,
                    ]);
                    $order->update(['order_serial_no' => date('dmy') . $order->id]);

                    foreach ($subSession->sessionItems as $si) {
                        OrderItem::create([
                            'order_id'               => $order->id,
                            'branch_id'              => $branchId,
                            'item_id'                => $si->item_id,
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
                    $this->freeResources($subSession);
                    $orders[] = $order->load('orderItems');
                }

                $allDone = $groupSession->subSessions()->where('is_checked_out', false)->doesntExist();
                if ($allDone) {
                    $groupSession->syncStatusFromOrders();
                }

                return [
                    'group_session' => $this->withRelations($groupSession->fresh()),
                    'orders'        => $orders,
                ];
            });
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    private function freeResources(SubSession $subSession): void
    {
        $subSession->loadMissing('sessionItems');
        $roomIds      = $subSession->sessionItems->pluck('room_id')->filter()->unique();
        $therapistIds = $subSession->sessionItems->pluck('therapist_id')->filter()->unique();
        foreach ($roomIds as $roomId) {
            Room::where('id', $roomId)->update(['status' => RoomStatus::CLEANING]);
        }
        foreach ($therapistIds as $therapistId) {
            TherapistProfile::where('user_id', $therapistId)->update(['status' => TherapistStatus::AVAILABLE]);
        }
    }
}

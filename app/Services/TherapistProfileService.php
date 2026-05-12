<?php

namespace App\Services;

use Exception;
use App\Enums\Ask;
use App\Enums\PaymentStatus;
use App\Enums\Role;
use App\Enums\Status;
use App\Models\User;
use App\Models\SessionItem;
use App\Models\TherapistProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\TherapistProfileRequest;
use App\Libraries\QueryExceptionLibrary;

class TherapistProfileService
{
    protected array $therapistFilter = [
        'status',
        'user_id',
    ];

    /**
     * @throws Exception
     */
    public function list(PaginateRequest $request)
    {
        try {
            $requests    = $request->all();
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType   = $request->get('order_type') ?? 'asc';

            return TherapistProfile::with(['user'])->where(function ($query) use ($requests) {
                foreach ($requests as $key => $request) {
                    if (in_array($key, $this->therapistFilter)) {
                        $query->where($key, $request);
                    }
                }
            })->orderBy($orderColumn, $orderType)->$method($methodValue);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function store(TherapistProfileRequest $request): TherapistProfile
    {
        try {
            return DB::transaction(function () use ($request) {
                $validated = $request->validated();
                $userId = $validated['user_id'] ?? null;

                if (!$userId) {
                    $user = User::create([
                        'name'              => $validated['name'],
                        'email'             => $validated['email'],
                        'phone'             => $validated['phone'] ?? null,
                        'username'          => $this->username($validated['email']),
                        'password'          => Hash::make($validated['password']),
                        'branch_id'         => $validated['branch_id'],
                        'status'            => Status::ACTIVE,
                        'email_verified_at' => now(),
                        'country_code'      => $validated['country_code'],
                        'is_guest'          => Ask::NO,
                    ]);
                    $user->assignRole(Role::THERAPIST);
                    $userId = $user->id;
                }

                return TherapistProfile::create([
                    'branch_id'       => $validated['branch_id'],
                    'user_id'         => $userId,
                    'code'            => $validated['code'] ?? null,
                    'verify_code'     => $validated['verify_code'] ?? null,
                    'commission_rate' => $validated['commission_rate'],
                    'status'          => $validated['status'],
                ])->load(['user']);
            });
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function update(TherapistProfileRequest $request, TherapistProfile $therapistProfile): TherapistProfile
    {
        try {
            return DB::transaction(function () use ($request, $therapistProfile) {
                $validated = $request->validated();

                $therapistProfile->update([
                    'branch_id'       => $validated['branch_id'],
                    'user_id'         => $validated['user_id'] ?? $therapistProfile->user_id,
                    'code'            => $validated['code'] ?? null,
                    'verify_code'     => $validated['verify_code'] ?? null,
                    'commission_rate' => $validated['commission_rate'],
                    'status'          => $validated['status'],
                ]);

                $user = $therapistProfile->user;
                if ($user && (array_key_exists('name', $validated) || array_key_exists('email', $validated) || array_key_exists('phone', $validated) || array_key_exists('country_code', $validated) || !empty($validated['password']))) {
                    if (!empty($validated['name'])) {
                        $user->name = $validated['name'];
                    }
                    if (!empty($validated['email'])) {
                        $user->email = $validated['email'];
                    }
                    if (array_key_exists('phone', $validated)) {
                        $user->phone = $validated['phone'];
                    }
                    if (!empty($validated['country_code'])) {
                        $user->country_code = $validated['country_code'];
                    }
                    if (!empty($validated['password'])) {
                        $user->password = Hash::make($validated['password']);
                    }
                    $user->branch_id = $validated['branch_id'];
                    $user->save();
                }

                return $therapistProfile->refresh()->load(['user']);
            });
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function destroy(TherapistProfile $therapistProfile): void
    {
        try {
            $therapistProfile->delete();
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function show(TherapistProfile $therapistProfile): TherapistProfile
    {
        try {
            return $therapistProfile->load(['user']);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function changeStatus(TherapistProfile $therapistProfile, string $status): TherapistProfile
    {
        try {
            $therapistProfile->update(['status' => $status]);
            return $therapistProfile;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function verifyByCode(int $id, string $verifyCode): TherapistProfile
    {
        try {
            $therapist = TherapistProfile::withoutGlobalScopes()
                ->with(['user'])
                ->where('user_id', $id)
                ->where('verify_code', $verifyCode)
                ->first();

            if (!$therapist) {
                throw new Exception('Invalid therapist ID or verify code. id: ' . $id . ', verify code: ' . $verifyCode, 404);
            }

            return $therapist;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), $exception->getCode() ?: 422);
        }
    }

    /**
     * @throws Exception
     */
    public function therapistProfileReport(PaginateRequest $request)
    {
        try {
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'total_revenue';
            $orderType   = $request->get('order_type') ?? 'desc';


            
            $query = SessionItem::select([
                'session_items.therapist_id',
                DB::raw("COALESCE(users.name, 'N/A') as therapist_name"),
                DB::raw('COUNT(DISTINCT sub_sessions.order_id) as total_orders'),
                DB::raw('COUNT(DISTINCT NULLIF(sub_sessions.phone, "")) as total_customers'),
                DB::raw('ROUND(SUM(COALESCE(session_items.duration, 0)) / 60, 2) as total_hours'),
                DB::raw('SUM(session_items.final_price) as total_revenue'),
            ])
            ->leftJoin('users', 'session_items.therapist_id', '=', 'users.id')
            ->leftJoin('therapist_profiles', 'session_items.therapist_id', '=', 'therapist_profiles.user_id')
            ->join('sub_sessions', 'session_items.sub_session_id', '=', 'sub_sessions.id')
            ->join('group_sessions', 'sub_sessions.group_session_id', '=', 'group_sessions.id')
            ->join('orders', 'sub_sessions.order_id', '=', 'orders.id')
            ->where('orders.payment_status', PaymentStatus::PAID)
            ->where(function ($q) use ($request) {
                if ($request->get('therapist_id')) {
                    $q->where('session_items.therapist_id', $request->get('therapist_id'));
                }
                if ($request->get('therapist_code')) {
                    $q->where('therapist_profiles.code', 'like', '%' . $request->get('therapist_code') . '%');
                }
                if ($request->get('branch_id')) {
                    $q->where('group_sessions.branch_id', $request->get('branch_id'));
                }
                if ($request->get('from_date')) {
                    $q->where(DB::raw('COALESCE(session_items.started_at, session_items.created_at)'), '>=', $request->get('from_date'));
                }
                if ($request->get('to_date')) {
                    $q->where(DB::raw('COALESCE(session_items.started_at, session_items.created_at)'), '<=', $request->get('to_date'));
                }
                if ($request->get('status')) {
                    $q->where('session_items.status', $request->get('status'));
                }
            })
            ->groupBy('session_items.therapist_id', 'users.name')
            ->orderBy($orderColumn, $orderType);

            return $query->$method($methodValue);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    private function username(string $email): string
    {
        $emails = explode('@', $email);
        return $emails[0] . mt_rand();
    }
}

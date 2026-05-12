<?php

namespace App\Services;

use Exception;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\PaginateRequest;
use App\Libraries\QueryExceptionLibrary;
use App\Http\Requests\ReservationRequest;

class ReservationService
{
    protected $reservationFilter = [
        'reservation_code',
        'customer_name',
        'customer_phone',
        'customer_email',
        'reservation_date',
        'status',
        'payment_status',
        'branch_id',
        'table_id'
    ];

    protected $exceptFilter = [
        'excepts'
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
            $orderType   = $request->get('order_type') ?? 'desc';

            return Reservation::with(['table', 'branch', 'createdBy'])->where(function ($query) use ($requests) {
                foreach ($requests as $key => $request) {
                    if (in_array($key, $this->reservationFilter)) {
                        if (in_array($key, ['branch_id', 'table_id', 'status', 'payment_status'])) {
                            $query->where($key, $request);
                        } elseif ($key == 'reservation_date') {
                            $query->whereDate($key, $request);
                        } else {
                            $query->where($key, 'like', '%' . $request . '%');
                        }
                    }

                    if (in_array($key, $this->exceptFilter)) {
                        $explodes = explode('|', $request);
                        if (is_array($explodes)) {
                            foreach ($explodes as $explode) {
                                $query->where('id', '!=', $explode);
                            }
                        }
                    }
                }
                
                // Handle date range filters
                if (!empty($requests['from_date'])) {
                    $query->whereDate('reservation_date', '>=', $requests['from_date']);
                }
                if (!empty($requests['to_date'])) {
                    $query->whereDate('reservation_date', '<=', $requests['to_date']);
                }
            })->orderBy($orderColumn, $orderType)->$method(
                $methodValue
            );
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function store(ReservationRequest $request)
    {
        try {
            $data = $request->validated();
            
            // Generate unique reservation code
            $data['reservation_code'] = Reservation::generateReservationCode($request->branch_id);
            
            // Set created_by to current authenticated user
            $data['created_by'] = Auth::id();

            return Reservation::create($data);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function update(ReservationRequest $request, Reservation $reservation): Reservation
    {
        try {
            $data = $request->validated();
            $reservation->update($data);
            return $reservation->load(['table', 'branch', 'createdBy']);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function destroy(Reservation $reservation)
    {
        try {
            $reservation->delete();
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function show(Reservation $reservation)
    {
        try {
            return $reservation->load(['table', 'branch', 'createdBy']);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function checkIn(Request $request, Reservation $reservation)
    {
        try {
            $reservation->update([
                'status' => \App\Enums\ReservationStatus::CHECKED_IN,
                'check_in_time' => now()
            ]);
            return $reservation;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function checkOut(Request $request, Reservation $reservation)
    {
        try {
            $reservation->update([
                'status' => \App\Enums\ReservationStatus::COMPLETED,
                'check_out_time' => now()
            ]);
            return $reservation;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function cancel(Request $request, Reservation $reservation)
    {
        try {
            $reservation->update([
                'status' => \App\Enums\ReservationStatus::CANCELLED,
                'cancel_reason' => $request->cancel_reason
            ]);
            return $reservation;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\Reservation;
use Illuminate\Http\Request;
use App\Services\ReservationService;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\ReservationRequest;
use App\Http\Resources\ReservationResource;

class ReservationController extends AdminController
{
    private ReservationService $reservationService;

    public function __construct(ReservationService $reservationService)
    {
        parent::__construct();
        $this->reservationService = $reservationService;
        $this->middleware(['permission:reservations_create'])->only('store');
        $this->middleware(['permission:reservations_edit'])->only('update', 'checkIn', 'checkOut', 'cancel');
        $this->middleware(['permission:reservations_delete'])->only('destroy');
        $this->middleware(['permission:reservations_show'])->only('show');
    }

    public function index(
        PaginateRequest $request
    ): \Illuminate\Http\Response | \Illuminate\Http\Resources\Json\AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return ReservationResource::collection($this->reservationService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function store(
        ReservationRequest $request
    ): \Illuminate\Http\Response | ReservationResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return new ReservationResource($this->reservationService->store($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function show(
        Reservation $reservation
    ): \Illuminate\Http\Response | ReservationResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return new ReservationResource($this->reservationService->show($reservation));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function update(
        ReservationRequest $request,
        Reservation $reservation
    ): \Illuminate\Http\Response | ReservationResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return new ReservationResource($this->reservationService->update($request, $reservation));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function destroy(
        Reservation $reservation
    ): \Illuminate\Http\Response | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            $this->reservationService->destroy($reservation);
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function checkIn(
        Request $request,
        Reservation $reservation
    ): \Illuminate\Http\Response | ReservationResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return new ReservationResource($this->reservationService->checkIn($request, $reservation));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function checkOut(
        Request $request,
        Reservation $reservation
    ): \Illuminate\Http\Response | ReservationResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return new ReservationResource($this->reservationService->checkOut($request, $reservation));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function cancel(
        Request $request,
        Reservation $reservation
    ): \Illuminate\Http\Response | ReservationResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return new ReservationResource($this->reservationService->cancel($request, $reservation));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}

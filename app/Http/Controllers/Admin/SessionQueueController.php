<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\SessionQueue;
use App\Services\SessionQueueService;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\SessionQueueRequest;
use App\Http\Resources\SessionQueueResource;

class SessionQueueController extends AdminController
{
    private SessionQueueService $sessionQueueService;

    public function __construct(SessionQueueService $sessionQueueService)
    {
        parent::__construct();
        $this->sessionQueueService = $sessionQueueService;
        $this->middleware(['permission:session_queue_create'])->only('store');
        $this->middleware(['permission:session_queue_edit'])->only('update', 'call', 'seat', 'cancel');
        $this->middleware(['permission:session_queue_delete'])->only('destroy');
        $this->middleware(['permission:session_queue_show'])->only('show');
    }

    public function index(PaginateRequest $request)
    {
        try {
            return SessionQueueResource::collection($this->sessionQueueService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function store(SessionQueueRequest $request)
    {
        try {
            return new SessionQueueResource($this->sessionQueueService->store($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function show(SessionQueue $sessionQueue)
    {
        try {
            return new SessionQueueResource($this->sessionQueueService->show($sessionQueue));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function update(SessionQueueRequest $request, SessionQueue $sessionQueue)
    {
        try {
            return new SessionQueueResource($this->sessionQueueService->update($request, $sessionQueue));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function destroy(SessionQueue $sessionQueue)
    {
        try {
            $this->sessionQueueService->destroy($sessionQueue);
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function call(SessionQueue $sessionQueue)
    {
        try {
            return new SessionQueueResource($this->sessionQueueService->call($sessionQueue));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function seat(SessionQueue $sessionQueue)
    {
        try {
            return new SessionQueueResource($this->sessionQueueService->seat($sessionQueue));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function cancel(SessionQueue $sessionQueue)
    {
        try {
            return new SessionQueueResource($this->sessionQueueService->cancel($sessionQueue));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}

<?php

namespace App\Services;

use Exception;
use App\Models\SessionQueue;
use App\Enums\SessionQueueStatus;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\SessionQueueRequest;
use App\Libraries\QueryExceptionLibrary;

class SessionQueueService
{
    protected array $queueFilter = ['branch_id', 'status', 'customer_name'];

    public function list(PaginateRequest $request)
    {
        try {
            $requests    = $request->all();
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'position';
            $orderType   = $request->get('order_type') ?? 'asc';

            return SessionQueue::with(['room', 'service', 'therapist'])
                ->where(function ($query) use ($requests) {
                    foreach ($requests as $key => $value) {
                        if (!in_array($key, $this->queueFilter)) { continue; }
                        if ($value === '' || $value === null) { continue; }
                        if ($key === 'status' && str_contains((string) $value, ',')) {
                            $query->whereIn('status', explode(',', $value));
                        } else {
                            $query->where($key, $value);
                        }
                    }
                })
                ->orderBy($orderColumn, $orderType)
                ->$method($methodValue);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function show(SessionQueue $queue): SessionQueue
    {
        try {
            return $queue->load(['room', 'service', 'therapist']);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function store(SessionQueueRequest $request): SessionQueue
    {
        try {
            $data = $request->validated();
            // Auto-assign position as max+1 for this branch
            $data['position'] = SessionQueue::where('branch_id', $data['branch_id'] ?? 0)
                ->where('status', SessionQueueStatus::WAITING)
                ->max('position') + 1;
            $data['status'] = SessionQueueStatus::WAITING;
            return SessionQueue::create($data)->load(['room', 'service', 'therapist']);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function update(SessionQueueRequest $request, SessionQueue $queue): SessionQueue
    {
        try {
            $queue->update($request->validated());
            return $queue->load(['room', 'service', 'therapist']);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function destroy(SessionQueue $queue): void
    {
        try {
            $queue->delete();
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }

    public function call(SessionQueue $queue): SessionQueue
    {
        try {
            $queue->update(['status' => SessionQueueStatus::CALLED]);
            return $queue->load(['room', 'service', 'therapist']);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function seat(SessionQueue $queue): SessionQueue
    {
        try {
            $queue->update(['status' => SessionQueueStatus::SEATED]);
            return $queue->load(['room', 'service', 'therapist']);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function cancel(SessionQueue $queue): SessionQueue
    {
        try {
            $queue->update(['status' => SessionQueueStatus::CANCELLED]);
            return $queue->load(['room', 'service', 'therapist']);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\Room;
use App\Models\SubSession;
use App\Models\SessionItem;
use App\Services\SubSessionService;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\SubSessionRequest;
use App\Http\Requests\SessionExtendRequest;
use App\Http\Requests\SessionChangeTherapistRequest;
use App\Http\Requests\SessionChangeStartTimeRequest;
use App\Http\Requests\SessionAddItemRequest;
use App\Http\Requests\SessionAddItemsRequest;
use App\Http\Resources\SessionItemResource;
use App\Http\Resources\SubSessionResource; 

class SubSessionController extends AdminController
{
    private SubSessionService $subSessionService;

    public function __construct(SubSessionService $subSessionService)
    {
        parent::__construct();
        $this->subSessionService = $subSessionService;
        $this->middleware(['permission:massage_sessions_create'])->only('store');
        $this->middleware(['permission:massage_sessions_edit'])->only('update', 'start', 'complete', 'extend', 'changeTherapist','completeItem', 'changeStartTime', 'addItem', 'updateItem', 'removeItem');
        // $this->middleware(['permission:massage_sessions_edit'])->only('update', 'start', 'complete', 'startItem', 'completeItem', 'addItem', 'addItems', 'removeItem');
        $this->middleware(['permission:massage_sessions_delete'])->only('destroy');
        $this->middleware(['permission:massage_sessions_show'])->only('show');
        $this->middleware(['permission:massage_sessions_edit'])->only('checkout');
    }

    public function index(PaginateRequest $request)
    {
        try {
            return SubSessionResource::collection($this->subSessionService->list($request))
                ->additional([
                    'date_format' => env('DATE_FORMAT', 'd-m-Y'),
                    'time_format' => env('TIME_FORMAT', 'h:i A'),
                ]);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function store(SubSessionRequest $request)
    {
        try {
            return new SubSessionResource($this->subSessionService->store($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function show(SubSession $subSession)
    {
        try {
            return new SubSessionResource($this->subSessionService->show($subSession));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function update(SubSessionRequest $request, SubSession $subSession)
    {
        try {
            return new SubSessionResource($this->subSessionService->update($request, $subSession));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function destroy(SubSession $subSession)
    {
        try {
            $this->subSessionService->destroy($subSession);
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function start(SubSession $subSession)
    {
        try {
            return new SubSessionResource($this->subSessionService->start($subSession));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function complete(SubSession $subSession)
    {
        try {
            return new SubSessionResource($this->subSessionService->complete($subSession));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function startItem(SubSession $subSession, SessionItem $sessionItem)
    {
        try {
            return new SessionItemResource($this->subSessionService->startItem($subSession, $sessionItem));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function completeItem(SubSession $subSession, SessionItem $sessionItem)
    {
        try {
            return new SessionItemResource($this->subSessionService->completeItem($subSession, $sessionItem));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function changeStartTime(SessionChangeStartTimeRequest $request, SubSession $subSession)
    {
        try {
            return new SubSessionResource($this->subSessionService->changeStartTime($request, $subSession));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function addItem(SessionAddItemRequest $request, SubSession $subSession)
    {
        try {
            $sessionItem = $this->subSessionService->addItem($request, $subSession);
            return new SessionItemResource($sessionItem);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function addItems(SessionAddItemsRequest $request, SubSession $subSession)
    {
        try {
            $sessionItems = $this->subSessionService->addItems($request, $subSession);
            return SessionItemResource::collection(collect($sessionItems));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function updateItem(SessionAddItemRequest $request, SubSession $subSession, SessionItem $sessionItem)
    {
        try {
            return new SubSessionResource($this->subSessionService->updateItem($request, $subSession, $sessionItem));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function removeItem(SubSession $subSession, SessionItem $sessionItem)
    {
        try {
            $updated = $this->subSessionService->removeItem($subSession, $sessionItem);
            return new SubSessionResource($updated);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function checkout(SubSession $subSession)
    {
        try {
            $result = $this->subSessionService->checkout($subSession);
            return response(['status' => true, 'data' => $result]);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function listByRoom(Room $room)
    {
        try {
            $data = $this->subSessionService->listByRoom($room);
            $r    = $data['room'];

            return response([
                'status' => true,
                'data'   => [
                    'id'            => $r->id,
                    'name'          => $r->name,
                    'status'        => $r->status,
                    'session_items' => SessionItemResource::collection($data['session_items'])->toArray(request()),
                ],
            ]);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\Room;
use Illuminate\Http\Request;
use App\Services\RoomService;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\RoomRequest;
use App\Http\Resources\RoomResource;

class RoomController extends AdminController
{
    private RoomService $roomService;

    public function __construct(RoomService $roomService)
    {
        parent::__construct();
        $this->roomService = $roomService;
        $this->middleware(['permission:rooms_create'])->only('store');
        $this->middleware(['permission:rooms_edit'])->only('update', 'changeStatus');
        $this->middleware(['permission:rooms_delete'])->only('destroy');
        $this->middleware(['permission:rooms_show'])->only('show');
    }

    public function index(PaginateRequest $request)
    {
        try {
            return RoomResource::collection($this->roomService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function store(RoomRequest $request)
    {
        try {
            return new RoomResource($this->roomService->store($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function show(Room $room)
    {
        try {
            return new RoomResource($this->roomService->show($room));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function update(RoomRequest $request, Room $room)
    {
        try {
            return new RoomResource($this->roomService->update($request, $room));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function destroy(Room $room)
    {
        try {
            $this->roomService->destroy($room);
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function changeStatus(Request $request, Room $room)
    {
        try {
            $request->validate(['status' => 'required|in:available,occupied,cleaning']);
            return new RoomResource($this->roomService->changeStatus($room, $request->status));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}

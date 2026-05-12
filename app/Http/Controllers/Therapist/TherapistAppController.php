<?php

namespace App\Http\Controllers\Therapist;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\SessionAddItemRequest;
use App\Http\Resources\BedResource;
use App\Http\Resources\ItemResource;
use App\Http\Resources\RoomResource;
use App\Http\Resources\SessionItemResource;
use App\Http\Resources\TherapistProfileResource;
use App\Models\Room;
use App\Models\SessionItem;
use App\Models\SubSession;
use App\Models\TherapistProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Services\BedService;
use App\Services\ItemService;
use App\Services\RoomService;
use App\Services\SubSessionService;
use App\Services\TherapistProfileService;
use Exception;
use Illuminate\Support\Facades\Log;

class TherapistAppController extends Controller
{
    public function __construct(
        private RoomService $roomService,
        private ItemService $itemService,
        private BedService $bedService,
        private TherapistProfileService $therapistProfileService,
        private SubSessionService $subSessionService,
    ) {
    }

    public function rooms(PaginateRequest $request)
    {
        try {
            return RoomResource::collection($this->roomService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function roomTasks(Room $room)
    {
        try {
            $sessionItems = SessionItem::with(['subSession.groupSession', 'item', 'room', 'bed', 'therapist'])
                ->where('room_id', $room->id)
                ->orderByDesc('start_time')
                ->orderByDesc('id')
                ->get();

            return response([
                'status' => true,
                'data'   => [
                    'id'            => $room->id,
                    'name'          => $room->name,
                    'status'        => $room->status,
                    'session_items' => SessionItemResource::collection($sessionItems)->toArray(request()),
                ],
            ]);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function items(PaginateRequest $request)
    {
        try {
            return ItemResource::collection($this->itemService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function beds(PaginateRequest $request)
    {
        try {
            return BedResource::collection($this->bedService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function therapists(PaginateRequest $request)
    {
        try {
            return TherapistProfileResource::collection($this->therapistProfileService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function addItem(SessionAddItemRequest $request, SubSession $subSession)
    {
        try {
            return new SessionItemResource($this->subSessionService->addItem($request, $subSession));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function startItem(Request $request, SubSession $subSession, SessionItem $sessionItem)
    {
        try {
            $this->ensureSessionItemBelongsToSubSession($subSession, $sessionItem);

            if ($sessionItem->therapist_id) {
                $therapistProfile = TherapistProfile::where('user_id', $sessionItem->therapist_id)->first();
                if (!$therapistProfile) {
                    throw new Exception('Assigned therapist not found.', 422);
                }

                $password = $request->input('password');
                if (!$password) {
                    throw new Exception('Password is required.', 422);
                }
                $verifyCode = $therapistProfile->verify_code;
                if ($password !== $verifyCode) {
                    throw new Exception('Verification code does not match the assigned therapist.', 422);
                }
            }

            return new SessionItemResource($this->subSessionService->startItem($subSession, $sessionItem));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function completeItem(SubSession $subSession, SessionItem $sessionItem)
    {
        try {
            $this->ensureSessionItemBelongsToSubSession($subSession, $sessionItem);
            return new SessionItemResource($this->subSessionService->completeItem($subSession, $sessionItem));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    private function ensureSessionItemBelongsToSubSession(SubSession $subSession, SessionItem $sessionItem): void
    {
        if ((int) $sessionItem->sub_session_id !== (int) $subSession->id) {
            throw new Exception('Session item does not belong to this session.', 422);
        }
    }
}

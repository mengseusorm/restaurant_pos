<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\GroupSession;
use App\Models\SubSession;
use App\Services\GroupSessionService;
use App\Http\Requests\GroupSessionRequest;
use App\Http\Requests\GroupSessionCheckoutRequest;
use App\Http\Requests\PaginateRequest;
use App\Http\Resources\GroupSessionResource;
use App\Http\Requests\SubSessionRequest;

class GroupSessionController extends AdminController
{
    private GroupSessionService $groupSessionService;

    public function __construct(GroupSessionService $groupSessionService)
    {
        parent::__construct();
        $this->groupSessionService = $groupSessionService;
        $this->middleware(['permission:massage_sessions_create'])->only('store');
        $this->middleware(['permission:massage_sessions_edit'])->only(
            'update', 'addSubSession', 'removeSubSession', 'checkout', 'checkoutSplit'
        );
        $this->middleware(['permission:massage_sessions_delete'])->only('destroy');
        $this->middleware(['permission:massage_sessions_show'])->only('show', 'index');
    }

    public function index(PaginateRequest $request)
    {
        try {
            return GroupSessionResource::collection($this->groupSessionService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function store(GroupSessionRequest $request)
    {
        try {
            return new GroupSessionResource($this->groupSessionService->store($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function show(GroupSession $groupSession)
    {
        try {
            return new GroupSessionResource($this->groupSessionService->show($groupSession));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function update(GroupSessionRequest $request, GroupSession $groupSession)
    {
        try {
            return new GroupSessionResource($this->groupSessionService->update($request, $groupSession));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function destroy(GroupSession $groupSession)
    {
        try {
            $this->groupSessionService->destroy($groupSession);
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Add a new guest (SubSession) to this group.
     * Body: { guest_name, phone, notes, share_group_bill }
     */
    public function addSubSession(SubSessionRequest $request, GroupSession $groupSession)
    {
        try {
            return new GroupSessionResource(
                $this->groupSessionService->addSubSession($groupSession, $request->validated())
            );
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Remove a sub-session from this group (detach, not delete).
     */
    public function removeSubSession(GroupSession $groupSession, SubSession $subSession)
    {
        try {
            return new GroupSessionResource(
                $this->groupSessionService->removeSubSession($groupSession, $subSession)
            );
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Option A: Single bill — one order for the entire group.
     */
    public function checkout(GroupSessionCheckoutRequest $request, GroupSession $groupSession)
    {
        try {
            $result = $this->groupSessionService->checkout($groupSession, $request);
            return response(['status' => true, 'data' => $result]);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Option B: Split bills — one order per person.
     */
    public function checkoutSplit(GroupSession $groupSession)
    {
        try {
            $result = $this->groupSessionService->checkoutSplit($groupSession);
            return response(['status' => true, 'data' => $result]);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}

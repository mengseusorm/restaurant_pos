<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\Member;
use App\Services\MemberService;
use App\Http\Requests\PaginateRequest;
use App\Http\Resources\MemberResource;
use App\Http\Requests\MemberRequest;
use App\Exports\MemberExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class MemberController extends AdminController
{
    private MemberService $memberService;

    public function __construct(MemberService $memberService)
    {
        parent::__construct();
        $this->memberService = $memberService;
        $this->middleware(['permission:members'])->only(
            'index',
            'export',
            'statistics',
            'findByPhoneOrCard'
        );
        $this->middleware(['permission:members_create'])->only('store');
        $this->middleware(['permission:members_edit'])->only('update', 'addPoints', 'deductPoints');
        $this->middleware(['permission:members_delete'])->only('destroy');
        $this->middleware(['permission:members_show'])->only('show');
    }

    /**
     * Display a listing of the members.
     */
    public function index(PaginateRequest $request)
    {
        try {
            return MemberResource::collection($this->memberService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Store a newly created member in storage.
     */
    public function store(MemberRequest $request)
    {
        try {
            return new MemberResource($this->memberService->store($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Display the specified member.
     */
    public function show(Member $member)
    {
        try {
            return new MemberResource($this->memberService->show($member));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Update the specified member in storage.
     */
    public function update(MemberRequest $request, Member $member)
    {
        try {
            return new MemberResource($this->memberService->update($request, $member));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Remove the specified member from storage.
     */
    public function destroy(Member $member)
    {
        try {
            $this->memberService->destroy($member);
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Find member by phone or card number.
     */
    public function findByPhoneOrCard(Request $request)
    {
        try {
            $request->validate([
                'value' => 'required|string'
            ]);

            $member = $this->memberService->findByPhoneOrCard($request->value);
            
            if (!$member) {
                return response(['status' => false, 'message' => 'Member not found'], 404);
            }

            return new MemberResource($member);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Add points to member.
     */
    public function addPoints(Request $request, Member $member)
    {
        try {
            $request->validate([
                'points' => 'required|integer|min:1',
                'reference_type' => 'nullable|string',
                'reference_id' => 'nullable|integer',
                'note' => 'nullable|string|max:255'
            ]);

            $updatedMember = $this->memberService->addPoints(
                $member,
                $request->points,
                $request->reference_type,
                $request->reference_id,
                $request->note
            );

            return new MemberResource($updatedMember);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Deduct points from member.
     */
    public function deductPoints(Request $request, Member $member)
    {
        try {
            $request->validate([
                'points' => 'required|integer|min:1',
                'reference_type' => 'nullable|string',
                'reference_id' => 'nullable|integer',
                'note' => 'nullable|string|max:255'
            ]);

            $updatedMember = $this->memberService->deductPoints(
                $member,
                $request->points,
                $request->reference_type,
                $request->reference_id,
                $request->note
            );

            return new MemberResource($updatedMember);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Get member statistics.
     */
    public function statistics()
    {
        try {
            $statistics = $this->memberService->getStatistics();
            return response(['status' => true, 'data' => $statistics]);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Export members to Excel.
     */
    public function export(PaginateRequest $request)
    {
        try {
            return Excel::download(new MemberExport($this->memberService, $request), 'Members.xlsx');
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}

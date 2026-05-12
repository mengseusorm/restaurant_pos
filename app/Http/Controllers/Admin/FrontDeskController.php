<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Services\FrontDeskService;
use Illuminate\Http\Request;

class FrontDeskController extends AdminController
{
    private FrontDeskService $frontDeskService;

    public function __construct(FrontDeskService $frontDeskService)
    {
        parent::__construct();
        $this->frontDeskService = $frontDeskService;
        $this->middleware(['permission:front_desk_show'])->only(['board', 'roomBoard']);
    }

    public function board(Request $request)
    {
        try {
            $branchId = (int) $request->get('branch_id', 0);
            return response(['status' => true, 'data' => $this->frontDeskService->board($branchId)]);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function roomBoard(Request $request, int $roomId)
    {
        try {
            return response(['status' => true, 'data' => $this->frontDeskService->roomBoard($roomId)]);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}

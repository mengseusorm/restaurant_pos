<?php

namespace App\Http\Controllers\Admin;

use Exception;
use Illuminate\Http\Request;
use App\Services\RoomScheduleService;

class RoomScheduleController extends AdminController
{
    private RoomScheduleService $roomScheduleService;

    public function __construct(RoomScheduleService $roomScheduleService)
    {
        parent::__construct();
        $this->roomScheduleService = $roomScheduleService;
        $this->middleware(['permission:room_schedule_show'])->only('index');
    }

    public function index(Request $request)
    {
        try {
            $date      = $request->get('date', now()->format('Y-m-d'));
            $branchId  = (int) $request->get('branch_id', 0);
            $data      = $this->roomScheduleService->schedule($date, $branchId);
            return response()->json(['status' => true, 'data' => $data]);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}

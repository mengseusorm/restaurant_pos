<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\FloorPlanGroupRequest;
use App\Http\Requests\PaginateRequest;
use App\Services\FloorPlanService;
use App\Services\DiningTableService;
use App\Http\Resources\FloorPlanGroupResource;
use App\Http\Resources\DiningTableResource;
use Exception;
use App\Models\DiningTable;
use App\Models\FloorPlanGroup;
use Illuminate\Http\Request;
use App\Http\Requests\FloorPlanGroupChangeImageRequest;
use App\Http\Requests\DiningTableChangeImageRequest;

class FloorPlanController extends AdminController
{
    public FloorPlanService $floorPlanService;
    public DiningTableService $diningTableService;

    public function __construct(FloorPlanService $floorPlanService, DiningTableService $diningTableService)
    {
        parent::__construct();
        $this->floorPlanService = $floorPlanService;
        $this->diningTableService = $diningTableService;
        $this->middleware(['permission:dining_tables_show'])->only(['index', 'getGroups', 'getTablesForGroup', 'getAnalytics', 'getTableDetails']);
        $this->middleware(['permission:dining_tables_edit'])->only(['updateTablePosition', 'updateTableProperties', 'updateCurrentGuests', 'update']);
        $this->middleware(['permission:dining_tables_create'])->only(['store']);
        $this->middleware(['permission:dining_tables_delete'])->only(['destroy']);
    }

    /**
     * Display floor plan groups with pagination
     */
    public function index(PaginateRequest $request)
    {
        try {
            return FloorPlanGroupResource::collection($this->floorPlanService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Get all floor plan groups
     */
    public function getGroups()
    {
        try {
            $groups = FloorPlanGroup::orderBy('sort_order')->get();
            return FloorPlanGroupResource::collection($groups);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Get tables for a specific group
     */
    public function getTablesForGroup($groupId)
    {
        try {
            $tables = DiningTable::with(['orders', 'floorPlanGroup'])
                ->where('floor_plan_group_id', $groupId)
                ->get();
            
            return DiningTableResource::collection($tables);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Update table position in floor plan
     */
    public function updateTablePosition(Request $request, $tableId)
    {
        try {
            $request->validate([
                'position_x' => 'required|numeric',
                'position_y' => 'required|numeric',
                'width' => 'nullable|numeric|min:40|max:600',
                'height' => 'nullable|numeric|min:40|max:600',
                'rotation' => 'nullable|integer|min:0|max:360'
            ]);

            $table = DiningTable::findOrFail($tableId);
            
            $table->update([
                'position_x' => $request->position_x,
                'position_y' => $request->position_y,
                'width' => $request->width ?? $table->width,
                'height' => $request->height ?? $table->height,
                'rotation' => $request->rotation ?? $table->rotation
            ]);

            return new DiningTableResource($table->fresh());
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Update table properties (shape, color, etc.)
     */
    public function updateTableProperties(Request $request, $tableId)
    {
        try {
            $request->validate([
                'shape' => 'nullable|in:rectangle,circle,square',
                'color' => 'nullable|regex:/^#[0-9A-F]{6}$/i',
                'size' => 'nullable|integer|min:1|max:20',
                'floor_plan_group_id' => 'nullable|exists:floor_plan_groups,id'
            ]);

            $table = DiningTable::findOrFail($tableId);
            
            $updateData = array_filter([
                'shape' => $request->shape,
                'color' => $request->color,
                'size' => $request->size,
                'floor_plan_group_id' => $request->floor_plan_group_id
            ], function($value) {
                return $value !== null;
            });

            $table->update($updateData);

            return new DiningTableResource($table->fresh());
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Update current guests count
     */
    public function updateCurrentGuests(Request $request, $tableId)
    {
        try {
            $request->validate([
                'current_guests' => 'required|integer|min:0|max:50'
            ]);

            $table = DiningTable::findOrFail($tableId);
            $table->update(['current_guests' => $request->current_guests]);

            return new DiningTableResource($table->fresh());
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Create a new floor plan group
     */
    public function store(FloorPlanGroupRequest $request)
    {
        try {
            return new FloorPlanGroupResource($this->floorPlanService->store($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Update floor plan group
     */
    public function update(FloorPlanGroupRequest $request, FloorPlanGroup $floorPlanGroup)
    {
        try {
            return new FloorPlanGroupResource($this->floorPlanService->update($request, $floorPlanGroup));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Delete floor plan group
     */
    public function destroy(FloorPlanGroup $floorPlanGroup)
    {
        try {
            $this->floorPlanService->destroy($floorPlanGroup);
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Get floor plan analytics
     */
    public function getAnalytics()
    {
        try {
            $analytics = $this->floorPlanService->getAnalytics();
            return response([
                'status' => true,
                'data' => $analytics
            ], 200);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Get table details with order information
     */
    public function getTableDetails($tableId)
    {
        try {
            $table = DiningTable::with(['orders.orderItems.item', 'orders.customer', 'floorPlanGroup'])
                ->findOrFail($tableId);

            return new DiningTableResource($table);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Release table (clear current order)
     */
    public function releaseTable($tableId)
    {
        try {
            $table = DiningTable::findOrFail($tableId);
            
            $table->update([
                'current_order_id' => null,
                'current_guests' => 0
            ]);

            return new DiningTableResource($table->fresh());
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Upload floor plan group photo
     */
    public function changeGroupImage(FloorPlanGroupChangeImageRequest $request, FloorPlanGroup $floorPlanGroup)
    {
        try {
            return new FloorPlanGroupResource($this->floorPlanService->changeGroupImage($request, $floorPlanGroup));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Upload dining table photo
     */
    public function changeTableImage(DiningTableChangeImageRequest $request, DiningTable $diningTable)
    {
        try {
            return new DiningTableResource($this->diningTableService->changeImage($request, $diningTable));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}

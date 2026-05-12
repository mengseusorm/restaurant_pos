<?php

namespace App\Services;

use App\Models\FloorPlanGroup;
use App\Models\DiningTable;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\FloorPlanGroupRequest;
use App\Http\Requests\FloorPlanGroupChangeImageRequest;
use App\Http\Requests\DiningTableChangeImageRequest;
use App\Http\Resources\FloorPlanGroupResource;
use Exception;
use Illuminate\Support\Str;

class FloorPlanService
{
    /**
     * List floor plan groups with pagination
     */
    public function list(PaginateRequest $request)
    {
        try {
            $requests = $request->all();
            $method = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType = $request->get('order_type') ?? 'desc';

            $query = FloorPlanGroup::with('diningTables')
                ->withCount([
                    'diningTables',
                    'diningTables as occupied_tables_count' => function ($query) {
                        $query->whereNotNull('current_order_id');
                    }
                ]);

            if (isset($requests['name'])) {
                $query->where('name', 'like', '%' . $requests['name'] . '%');
            }

            if (isset($requests['status'])) {
                $query->where('status', $requests['status']);
            }

            $groups = $query->orderBy($orderColumn, $orderType)->$method(
                $methodValue == '*' ? $methodValue : (int)$methodValue
            );

            return FloorPlanGroupResource::collection($groups)->response()->getData(true);
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Store a new floor plan group
     */
    public function store(FloorPlanGroupRequest $request)
    {
        try {
            $group = FloorPlanGroup::create([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'description' => $request->description,
                'branch_id' => $request->branch_id,
                'sort_order' => $request->sort_order ?? FloorPlanGroup::max('sort_order') + 1,
                'status' => $request->status ?? \App\Enums\Status::ACTIVE
            ]);

            return new FloorPlanGroupResource($group);
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Update floor plan group
     */
    public function update(FloorPlanGroupRequest $request, FloorPlanGroup $floorPlanGroup)
    {
        try {
            $floorPlanGroup->update([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'description' => $request->description,
                'sort_order' => $request->sort_order ?? $floorPlanGroup->sort_order,
                'status' => $request->status ?? $floorPlanGroup->status
            ]);

            return new FloorPlanGroupResource($floorPlanGroup);
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Delete floor plan group
     */
    public function destroy(FloorPlanGroup $floorPlanGroup)
    {
        try {
            // Move tables to no group before deleting
            DiningTable::where('floor_plan_group_id', $floorPlanGroup->id)
                ->update(['floor_plan_group_id' => null]);
            
            $floorPlanGroup->delete();

            return response(['status' => true, 'message' => trans('user_validation.delete_success')], 200);
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Get floor plan analytics
     */
    public function getAnalytics()
    {
        try {
            $totalTables = DiningTable::count();
            $occupiedTables = DiningTable::whereNotNull('current_order_id')->count();
            $availableTables = $totalTables - $occupiedTables;
            
            $occupancyRate = $totalTables > 0 ? round(($occupiedTables / $totalTables) * 100, 2) : 0;
            
            $groupAnalytics = FloorPlanGroup::withCount([
                'diningTables',
                'diningTables as occupied_tables_count' => function ($query) {
                    $query->whereNotNull('current_order_id');
                }
            ])->get()->map(function ($group) {
                $occupancyRate = $group->dining_tables_count > 0 
                    ? round(($group->occupied_tables_count / $group->dining_tables_count) * 100, 2) 
                    : 0;
                
                return [
                    'id' => $group->id,
                    'name' => $group->name,
                    'total_tables' => $group->dining_tables_count,
                    'occupied_tables' => $group->occupied_tables_count,
                    'available_tables' => $group->dining_tables_count - $group->occupied_tables_count,
                    'occupancy_rate' => $occupancyRate
                ];
            });

            return [
                'overall' => [
                    'total_tables' => $totalTables,
                    'occupied_tables' => $occupiedTables,
                    'available_tables' => $availableTables,
                    'occupancy_rate' => $occupancyRate
                ],
                'groups' => $groupAnalytics
            ];
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Optimize table layout automatically
     */
    public function optimizeLayout($groupId)
    {
        try {
            $tables = DiningTable::where('floor_plan_group_id', $groupId)->get();
            
            // Simple grid layout optimization
            $gridSize = 100; // pixels
            $columns = ceil(sqrt($tables->count()));
            $currentRow = 0;
            $currentCol = 0;
            
            foreach ($tables as $table) {
                $table->update([
                    'position_x' => $currentCol * $gridSize,
                    'position_y' => $currentRow * $gridSize,
                    'width' => 80,
                    'height' => 80,
                    'rotation' => 0
                ]);
                
                $currentCol++;
                if ($currentCol >= $columns) {
                    $currentCol = 0;
                    $currentRow++;
                }
            }
            
            return true;
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Change floor plan group image
     */
    public function changeGroupImage(FloorPlanGroupChangeImageRequest $request, FloorPlanGroup $floorPlanGroup): FloorPlanGroup
    {
        try {
            if ($request->image) {
                $floorPlanGroup->clearMediaCollection('floor_plan');
                $floorPlanGroup->addMedia($request->image)->toMediaCollection('floor_plan');
            }
            return $floorPlanGroup;
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Change dining table image
     */
    public function changeTableImage(DiningTableChangeImageRequest $request, DiningTable $diningTable): DiningTable
    {
        try {
            if ($request->image) {
                $diningTable->clearMediaCollection('table');
                $diningTable->addMedia($request->image)->toMediaCollection('table');
            }
            return $diningTable;
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), 422);
        }
    }
}

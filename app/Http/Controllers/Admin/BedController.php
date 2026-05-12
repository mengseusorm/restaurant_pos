<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\Bed;
use Illuminate\Http\Request;
use App\Services\BedService;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\BedRequest;
use App\Http\Resources\BedResource;

class BedController extends AdminController
{
    private BedService $bedService;

    public function __construct(BedService $bedService)
    {
        parent::__construct();
        $this->bedService = $bedService;
        $this->middleware(['permission:rooms_create'])->only('store');
        $this->middleware(['permission:rooms_edit'])->only('update', 'changeStatus');
        $this->middleware(['permission:rooms_delete'])->only('destroy');
        $this->middleware(['permission:rooms_show'])->only('show');
    }

    public function index(PaginateRequest $request)
    {
        try {
            return BedResource::collection($this->bedService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function store(BedRequest $request)
    {
        try {
            return new BedResource($this->bedService->store($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function show(Bed $bed)
    {
        try {
            return new BedResource($this->bedService->show($bed));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function update(BedRequest $request, Bed $bed)
    {
        try {
            return new BedResource($this->bedService->update($request, $bed));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function destroy(Bed $bed)
    {
        try {
            $this->bedService->destroy($bed);
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function changeStatus(Request $request, Bed $bed)
    {
        try {
            $request->validate(['status' => 'required|in:available,occupied,cleaning']);
            return new BedResource($this->bedService->changeStatus($bed, $request->status));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}

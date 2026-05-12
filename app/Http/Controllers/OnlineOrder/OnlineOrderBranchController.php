<?php

namespace App\Http\Controllers\OnlineOrder;


use App\Http\Controllers\Controller;
use App\Models\FrontendDiningTable;
use Exception;
use App\Services\DiningTableService;
use App\Http\Requests\PaginateRequest;
use App\Http\Resources\BranchPublicResource;
use App\Http\Resources\BranchResource;
use App\Http\Resources\DiningTableResource;
use App\Models\Branch;
use App\Models\DiningTable;
use App\Services\BranchService;
use Illuminate\Support\Facades\Log;

class OnlineOrderBranchController extends Controller
{
    private BranchService $branchService;

    public function __construct(BranchService $branch)
    {
        $this->branchService = $branch;
    }

    public function index(PaginateRequest $request) : \Illuminate\Http\Response | \Illuminate\Http\Resources\Json\AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return BranchPublicResource::collection($this->branchService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function show(Branch $branch): BranchPublicResource|\Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            Log::info('OnlineOrderBranchController@show', ['branch' => $branch->toArray()]);
            return new BranchPublicResource($branch);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}

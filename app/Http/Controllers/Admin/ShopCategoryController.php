<?php

namespace App\Http\Controllers\Admin;


use Exception;
use App\Models\ShopCategory;
use Illuminate\Http\Request;
use App\Services\ShopCategoryService;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\ShopCategoryRequest;
use App\Http\Resources\ShopCategoryResource;

class ShopCategoryController extends AdminController
{
    private ShopCategoryService $shopCategoryService;

    public function __construct(ShopCategoryService $shopCategory)
    {
        parent::__construct();
        $this->shopCategoryService = $shopCategory;
        $this->middleware(['permission:settings'])->only('store', 'update', 'destroy', 'show');
    }

    public function index(
        PaginateRequest $request
    ): \Illuminate\Http\Response | \Illuminate\Http\Resources\Json\AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return ShopCategoryResource::collection($this->shopCategoryService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }


    public function store(
        ShopCategoryRequest $request
    ): \Illuminate\Http\Response | ShopCategoryResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return new ShopCategoryResource($this->shopCategoryService->store($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function show(
        ShopCategory $shopCategory
    ): \Illuminate\Http\Response | ShopCategoryResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return new ShopCategoryResource($this->shopCategoryService->show($shopCategory));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function update(
        ShopCategoryRequest $request,
        ShopCategory $shopCategory
    ): \Illuminate\Http\Response | ShopCategoryResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return new ShopCategoryResource($this->shopCategoryService->update($request, $shopCategory));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function destroy(
        ShopCategory $shopCategory
    ): \Illuminate\Http\Response | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            $this->shopCategoryService->destroy($shopCategory);
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function sortCategory(
        Request $request
    ) {
        try {
            $this->shopCategoryService->sortCategory($request);
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

}

<?php

namespace App\Http\Controllers\Admin;


use Exception;
use App\Models\ItemCategory;
use Illuminate\Http\Request;
use App\Services\ItemCategoryService;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\ItemCategoryRequest;
use App\Http\Requests\ChangeImageRequest;
use App\Http\Resources\ItemCategoryResource;
use App\Exports\ItemCategoryExport;
use App\Http\Requests\ItemCategoryImportRequest;
use App\Imports\ItemCategoryImport;
use Illuminate\Support\Facades\Log;
use Response;
use Maatwebsite\Excel\Facades\Excel;

class ItemCategoryController extends AdminController
{
    private ItemCategoryService $itemCategoryService;

    public function __construct(ItemCategoryService $itemCategory)
    {
        parent::__construct();
        $this->itemCategoryService = $itemCategory;
        $this->middleware(['permission:settings'])->only('store', 'update', 'destroy', 'show');
    }

    public function index(
        PaginateRequest $request
    ): \Illuminate\Http\Response | \Illuminate\Http\Resources\Json\AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            $categories = $this->itemCategoryService->list($request);

            // Check if last_updated parameter was provided and no categories were found
            if ($request->has('last_updated') && $categories->isEmpty()) {
                return response(['status' => true, 'message' => 'No item categories updated', 'has_updates' => false], 200);
            }

            return ItemCategoryResource::collection($categories);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }


    public function store(
        ItemCategoryRequest $request
    ): \Illuminate\Http\Response | ItemCategoryResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return new ItemCategoryResource($this->itemCategoryService->store($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function show(
        ItemCategory $itemCategory
    ): \Illuminate\Http\Response | ItemCategoryResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return new ItemCategoryResource($this->itemCategoryService->show($itemCategory));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function update(
        ItemCategoryRequest $request,
        ItemCategory $itemCategory
    ): \Illuminate\Http\Response | ItemCategoryResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return new ItemCategoryResource($this->itemCategoryService->update($request, $itemCategory));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function destroy(
        ItemCategory $itemCategory
    ): \Illuminate\Http\Response | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            $this->itemCategoryService->destroy($itemCategory);
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function destroyFromItem(
        ItemCategory $itemCategory
    ): \Illuminate\Http\Response | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            $this->itemCategoryService->destroyFromItem($itemCategory);
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function sortCategory(
        Request $request
    ) {
        try {
            $this->itemCategoryService->sortCategory($request);
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function export(PaginateRequest $request) : \Illuminate\Http\Response | \Symfony\Component\HttpFoundation\BinaryFileResponse | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return Excel::download(new ItemCategoryExport($this->itemCategoryService, $request), 'Item.xlsx');
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function downloadSample()
    {
        try {
            return Response::download(public_path('/file/CategoryImportSampleFile.xlsx'));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function import(ItemCategoryImportRequest $request)
    {
        try {
            Excel::import(new ItemCategoryImport(),$request->file('file'));
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function applyItemCategoryPrinter(Request $request)
    {
        try {
            return $this->itemCategoryService->applyItemCategoryPrinter($request);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }


    public function uploadImages(ChangeImageRequest $request): \Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try { 
            $this->itemCategoryService->uploadImages($request);
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

}
